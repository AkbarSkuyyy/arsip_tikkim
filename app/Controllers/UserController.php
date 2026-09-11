<?php
/**
 * Controller: manajemen akun pengguna (register, edit role, reset password, hapus).
 */
class UserController
{
    public function register(): void
    {
        Auth::requireAdmin();

        $pesan_sukses = "";
        $pesan_error = "";

        if (isset($_POST['register'])) {
            Csrf::verify();

            $nama     = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $jabatan  = trim($_POST['jabatan']);
            $email    = trim($_POST['email']);
            $role     = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            $password = $_POST['password'];

            if (strlen($password) < 8) {
                $pesan_error = "Kata sandi minimal 8 karakter!";
            } elseif (User::existsByEmailOrUsername($email, $username)) {
                $pesan_error = "Email atau Username tersebut sudah terdaftar!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                if (User::create(['nama' => $nama, 'username' => $username, 'jabatan' => $jabatan, 'email' => $email, 'password' => $hashed, 'role' => $role])) {
                    catatLog(Auth::userNama(), "Menambah akun baru: $username (role: $role)");
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
        if (!$user) { header("Location: dashboard_super.php"); exit; }

        if (isset($_POST['update_role'])) {
            Csrf::verify();
            $role = ($_POST['role'] === 'admin') ? 'admin' : 'user';
            Database::exec("UPDATE users SET role = ? WHERE id = ?", 'si', [$role, $id]);
            catatLog(Auth::userNama(), "Mengubah role " . $user['username'] . " menjadi " . $role);
            header("Location: dashboard_super.php");
            exit;
        }

        require __DIR__ . '/../Views/users/edit.php';
    }

    public function resetPassword(): void
    {
        Auth::requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) { header("Location: dashboard_super.php"); exit; }

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
            header("Location: dashboard_super.php");
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

        header("Location: dashboard_super.php");
        exit;
    }
}
