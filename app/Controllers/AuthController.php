<?php
/**
 * Controller: autentikasi (login, logout).
 */
class AuthController
{
    public function login(): void
    {
        if (Auth::check()) {
            header("Location: dashboard.php");
            exit;
        }

        $error = "";

        if (isset($_POST['login'])) {
            Csrf::verify();

            // Rate limiting: cegah brute force (5 gagal -> kunci 15 menit)
            $max_attempts = 5;
            $lock_seconds = 900;

            if (Session::has('lock_until') && time() < Session::get('lock_until')) {
                $menit = ceil((Session::get('lock_until') - time()) / 60);
                $error = "Terlalu banyak percobaan login. Coba lagi dalam $menit menit.";
            } else {
                if (Session::has('lock_until')) {
                    Session::remove('lock_until');
                    Session::remove('login_attempts');
                }

                $user_input = trim($_POST['user_input']);
                $password = $_POST['password'];

                $row = User::findByLogin($user_input);

                if ($row && password_verify($password, $row['password'])) {
                    Session::remove('login_attempts');
                    Session::remove('lock_until');
                    Session::set('login', true);
                    Session::set('user_id', $row['id']);
                    Session::set('user_nama', $row['nama']);
                    Session::set('user_jabatan', $row['jabatan']);
                    Session::set('username', $row['username']);
                    Session::set('user_role', $row['role']);
                    Session::set('user_foto', $row['foto_profile']);

                    catatLog(Session::get('user_nama'), "User berhasil login ke sistem.");

                    header("Location: dashboard.php");
                    exit;
                } elseif ($row) {
                    $error = "Password yang Anda masukkan salah!";
                } else {
                    $error = "Email atau Username tidak terdaftar!";
                }

                if ($error !== "") {
                    $attempts = (int)Session::get('login_attempts', 0) + 1;
                    Session::set('login_attempts', $attempts);
                    if ($attempts >= $max_attempts) {
                        Session::set('lock_until', time() + $lock_seconds);
                        $error = "Terlalu banyak percobaan gagal. Login dikunci selama 15 menit.";
                    }
                }
            }
        }

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function logout(): void
    {
        Session::destroy();
        header("Location: login.php");
        exit;
    }
}
