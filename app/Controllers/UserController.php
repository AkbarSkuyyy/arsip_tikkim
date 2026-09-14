<?php
/**
 * Controller: manajemen akun pengguna (daftar, register, edit role+akses,
 * reset password, hapus). Halaman ini khusus SUPERADMIN.
 *
 * Role (4 tingkat):
 *  - superadmin : kelola sistem penuh + semua data
 *  - admin      : pengumpul data (semua jenis), tanpa menu sistem
 *  - user       : petugas arsip, fokus 1 jenis dokumen
 *  - viewer     : kepala kantor, lihat semua (read-only)
 */
class UserController
{
    /** Daftar role yang valid. */
    private const ROLES = ['superadmin', 'admin', 'user', 'viewer'];

    /** Label role untuk tampilan. */
    public static function roleLabel(string $role): string
    {
        return [
            'superadmin' => 'Super Admin',
            'admin'      => 'Admin',
            'user'       => 'Petugas',
            'viewer'     => 'Viewer',
        ][$role] ?? $role;
    }

    /** Normalisasi role dari input POST. */
    private function normalizeRole(string $role): string
    {
        return in_array($role, self::ROLES, true) ? $role : 'user';
    }

    /** Akses dokumen berdasar role: non-petugas = '*', petugas = 1 kode. */
    private function aksesForRole(string $role, string $postedAkses): string
    {
        return in_array($role, ['superadmin', 'admin', 'viewer'], true)
            ? '*'
            : trim($postedAkses);
    }

    public function index(): void
    {
        Auth::requireSuperadmin();

        $users = User::all();
        $dokumen_list = MasterDokumen::allActive();

        require __DIR__ . '/../Views/users/index.php';
    }

    public function register(): void
    {
        Auth::requireSuperadmin();

        $pesan_sukses = "";
        $pesan_error = "";
        $dokumen_list = MasterDokumen::allActive();

        if (isset($_POST['register'])) {
            Csrf::verify();

            $nama     = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $jabatan  = trim($_POST['jabatan']);
            $email    = trim($_POST['email']);
            $role     = $this->normalizeRole($_POST['role'] ?? 'user');
            $password = $_POST['password'];
            $akses_dokumen = $this->aksesForRole($role, (string)($_POST['akses_dokumen'] ?? ''));

            if (strlen($password) < 8) {
                $pesan_error = "Kata sandi minimal 8 karakter!";
            } elseif ($role === 'user' && $akses_dokumen === '') {
                $pesan_error = "Pilih satu jenis dokumen untuk akun petugas!";
            } elseif (User::existsByEmailOrUsername($email, $username)) {
                $pesan_error = "Email atau Username tersebut sudah terdaftar!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                if (User::create(['nama' => $nama, 'username' => $username, 'jabatan' => $jabatan, 'email' => $email, 'password' => $hashed, 'role' => $role, 'akses_dokumen' => $akses_dokumen])) {
                    catatLog(Auth::userNama(), "Menambah akun baru: $username (role: $role, akses: $akses_dokumen)");
                    $pesan_sukses = "Akun berhasil dibuat dengan role: " . self::roleLabel($role) . ".";
                } else {
                    $pesan_error = "Terjadi kesalahan saat menyimpan akun.";
                }
            }
        }

        require __DIR__ . '/../Views/users/register.php';
    }

    public function edit(): void
    {
        Auth::requireSuperadmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) { header("Location: kelola_akun.php"); exit; }

        $dokumen_list = MasterDokumen::allActive();
        $akses_sekarang = ($user['akses_dokumen'] ?? '*') === '*'
            ? ''
            : trim(explode(',', (string)($user['akses_dokumen'] ?? ''))[0] ?? '');

        $error = "";

        if (isset($_POST['update_role'])) {
            Csrf::verify();
            $role = $this->normalizeRole($_POST['role'] ?? 'user');
            $akses_dokumen = $this->aksesForRole($role, (string)($_POST['akses_dokumen'] ?? ''));

            if ($role === 'user' && $akses_dokumen === '') {
                $error = "Pilih satu jenis dokumen untuk akun petugas!";
                $akses_sekarang = $akses_dokumen;
            } elseif ($role === 'superadmin' && $this->isLastSuperadmin($id)) {
                $error = "Tidak bisa menurunkan role superadmin terakhir!";
            } else {
                Database::exec("UPDATE users SET role = ?, akses_dokumen = ? WHERE id = ?", 'ssi', [$role, $akses_dokumen, $id]);
                catatLog(Auth::userNama(), "Mengubah role " . $user['username'] . " menjadi " . $role . " (akses: " . $akses_dokumen . ")");
                header("Location: kelola_akun.php");
                exit;
            }
        }

        require __DIR__ . '/../Views/users/edit.php';
    }

    public function resetPassword(): void
    {
        Auth::requireSuperadmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) { header("Location: kelola_akun.php"); exit; }

        $error = "";
        $sukses = "";

        if (isset($_POST['reset_pw'])) {
            Csrf::verify();
            $new_pw = $_POST['new_password'];
            $confirm_pw = $_POST['confirm_password'];

            if (strlen($new_pw) < 8) {
                $error = "Password minimal 8 karakter!";
            } elseif ($new_pw !== $confirm_pw) {
                $error = "Password baru dan konfirmasi tidak cocok!";
            } else {
                $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
                User::updatePassword($id, $hashed);
                catatLog(Auth::userNama(), "Mereset password untuk user: " . $user['username']);
                $sukses = "Password berhasil diubah!";
            }
        }

        require __DIR__ . '/../Views/users/reset_password.php';
    }

    public function destroy(): void
    {
        Auth::requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: kelola_akun.php");
            exit;
        }

        Csrf::verify();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $user = User::find($id);
            if ($user) {
                if ((int)$user['id'] === Auth::userId()) {
                    Session::flashSet('error', 'Anda tidak bisa menghapus akun sendiri!');
                } elseif ($user['role'] === 'superadmin' && $this->isLastSuperadmin((int)$user['id'])) {
                    Session::flashSet('error', 'Tidak bisa menghapus superadmin terakhir!');
                } else {
                    User::delete($id);
                    catatLog(Auth::userNama(), "Menghapus akun user: " . $user['username']);
                    Session::flashSet('success', 'Akun telah dihapus.');
                }
            }
        }

        header("Location: kelola_akun.php");
        exit;
    }

    /** Apakah $id adalah superadmin terakhir yang tersisa? */
    private function isLastSuperadmin(int $id): bool
    {
        $r = Database::query("SELECT COUNT(*) AS t FROM users WHERE role = 'superadmin' AND id != ?", 'i', [$id]);
        $count = (int)(mysqli_fetch_assoc($r)['t'] ?? 0);
        return $count === 0;
    }
}
