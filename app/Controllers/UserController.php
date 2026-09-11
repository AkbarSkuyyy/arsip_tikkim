<?php
/**
 * Controller: manajemen akun pengguna (register, edit role+akses, reset password, hapus).
 */
class UserController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $users = User::all();
        $dokumen_list = MasterDokumen::allActive();

        require __DIR__ . '/../Views/users/index.php';
    }

    public function register(): void
    {
        Auth::requireAdmin();

        $pesan_sukses = "";
        $pesan_error = "";
        $dokumen_list = MasterDokumen::allActive();

        if (isset($_POST['register'])) {
            Csrf::verify();

            $nama     = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $jabatan  = trim($_POST['jabatan']);
            $email    = trim($_POST['email']);
            $role     = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            $password = $_POST['password'];

            // Akses jenis dokumen: admin = semua ('*'); user = pilihan checkbox.
            if ($role === 'admin') {
                $akses_dokumen = '*';
            } else {
                $sel = $_POST['akses_dokumen'] ?? [];
                $akses_dokumen = is_array($sel) ? implode(',', array_filter(array_map('trim', $sel), 'strlen')) : '';
            }

            if (strlen($password) < 8) {
                $pesan_error = "Kata sandi minimal 8 karakter!";
            } elseif (User::existsByEmailOrUsername($email, $username)) {
                $pesan_error = "Email atau Username tersebut sudah terdaftar!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                if (User::create(['nama' => $nama, 'username' => $username, 'jabatan' => $jabatan, 'email' => $email, 'password' => $hashed, 'role' => $role, 'akses_dokumen' => $akses_dokumen])) {
                    catatLog(Auth::userNama(), "Menambah akun baru: $username (role: $role, akses: $akses_dokumen)");
                    $pesan_sukses = "Akun berhasil dibuat dengan role: $role.";
                } else {
                    $pesan_error = "Terjadi kesalahan saat menyimpan akun.";
                }
            }
        }

        require __DIR__ . '/../Views/users/register.php';
    }

    public function edit(): void
    {
        Auth::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) { header("Location: kelola_akun.php"); exit; }

        $dokumen_list = MasterDokumen::allActive();
        $akses_sekarang = ($user['akses_dokumen'] ?? '*') === '*'
            ? []
            : array_filter(array_map('trim', explode(',', (string)($user['akses_dokumen'] ?? ''))), 'strlen');

        if (isset($_POST['update_role'])) {
            Csrf::verify();
            $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            if ($role === 'admin') {
                $akses_dokumen = '*';
            } else {
                $sel = $_POST['akses_dokumen'] ?? [];
                $akses_dokumen = is_array($sel) ? implode(',', array_filter(array_map('trim', $sel), 'strlen')) : '';
            }
            Database::exec("UPDATE users SET role = ?, akses_dokumen = ? WHERE id = ?", 'ssi', [$role, $akses_dokumen, $id]);
            catatLog(Auth::userNama(), "Mengubah role " . $user['username'] . " menjadi " . $role . " (akses: " . $akses_dokumen . ")");
            header("Location: kelola_akun.php");
            exit;
        }

        require __DIR__ . '/../Views/users/edit.php';
    }

    public function resetPassword(): void
    {
        Auth::requireAdmin();

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
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: kelola_akun.php");
            exit;
        }

        Csrf::verify();

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $user = User::find($id);
            if ($user) {
                User::delete($id);
                catatLog(Auth::userNama(), "Menghapus akun user: " . $user['username']);
            }
        }

        header("Location: kelola_akun.php");
        exit;
    }
}
