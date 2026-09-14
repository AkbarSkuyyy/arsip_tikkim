<?php
/**
 * Controller: autentikasi (login, logout).
 * Rate limiting berbasis IP (DB) + session timeout idle.
 */
class AuthController
{
    /** Ambil IP client (dengan dukungan proxy bila ada). */
    private function clientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return substr((string)$ip, 0, 45);
    }

    /** Bersihkan catatan throttle untuk IP (setelah sukses). */
    private function throttleClear(string $ip): void
    {
        Database::exec("DELETE FROM login_throttle WHERE ip = ?", 's', [$ip]);
    }

    /** Catat kegagalan login; return true jika IP sekarang terkunci. */
    private function throttleFailed(string $ip, int $maxAttempts = 5, int $lockSeconds = 900): bool
    {
        $now = time();
        $r = Database::query("SELECT attempts, locked_until FROM login_throttle WHERE ip = ?", 's', [$ip]);
        $row = $r ? mysqli_fetch_assoc($r) : null;

        $attempts = $row ? (int)$row['attempts'] + 1 : 1;
        if ($attempts >= $maxAttempts) {
            Database::exec(
                "INSERT INTO login_throttle (ip, attempts, last_attempt, locked_until) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE attempts=VALUES(attempts), last_attempt=VALUES(last_attempt), locked_until=VALUES(locked_until)",
                'siii',
                [$ip, $attempts, $now, $now + $lockSeconds]
            );
            return true;
        }

        Database::exec(
            "INSERT INTO login_throttle (ip, attempts, last_attempt, locked_until) VALUES (?, ?, ?, 0)
             ON DUPLICATE KEY UPDATE attempts=VALUES(attempts), last_attempt=VALUES(last_attempt), locked_until=0",
            'sii',
            [$ip, $attempts, $now]
        );
        return false;
    }

    public function login(): void
    {
        if (Auth::check()) {
            header("Location: " . (Auth::isSuperadmin() ? 'dashboard_super.php' : 'dashboard.php'));
            exit;
        }

        $error = "";

        if (isset($_GET['timeout'])) {
            $error = "Sesi berakhir karena tidak ada aktivitas. Silakan login kembali.";
        }

        if (isset($_POST['login'])) {
            Csrf::verify();

            $ip = $this->clientIp();

            // Cek kunci (lock) dari percobaan gagal sebelumnya.
            $r = Database::query("SELECT locked_until FROM login_throttle WHERE ip = ?", 's', [$ip]);
            $locked = $r ? (int)(mysqli_fetch_assoc($r)['locked_until'] ?? 0) : 0;
            if ($locked > time()) {
                $menit = ceil(($locked - time()) / 60);
                $error = "Terlalu banyak percobaan login. Coba lagi dalam $menit menit.";
            } else {
                $user_input = trim($_POST['user_input']);
                $password = $_POST['password'];

                $row = User::findByLogin($user_input);

                if ($row && password_verify($password, $row['password'])) {
                    $this->throttleClear($ip);
                    Session::set('login', true);
                    Session::set('user_id', $row['id']);
                    Session::set('user_nama', $row['nama']);
                    Session::set('user_jabatan', $row['jabatan']);
                    Session::set('username', $row['username']);
                    Session::set('user_role', $row['role']);
                    Session::set('user_akses', $row['akses_dokumen'] ?? '*');
                    Session::set('user_foto', $row['foto_profile']);
                    Session::set('last_activity', time());

                    catatLog(Session::get('user_nama'), "User berhasil login ke sistem.");

                    $target = ($row['role'] === 'superadmin') ? 'dashboard_super.php' : 'dashboard.php';
                    header("Location: " . $target);
                    exit;
                } elseif ($row) {
                    $error = "Password yang Anda masukkan salah!";
                } else {
                    $error = "Email atau Username tidak terdaftar!";
                }

                if ($error !== "") {
                    if ($this->throttleFailed($ip)) {
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
