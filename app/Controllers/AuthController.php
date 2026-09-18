<?php
/**
 * Controller: autentikasi (login, logout).
 * Rate limiting berbasis IP (DB) + session timeout idle.
 */
class AuthController
{
    /** Default: peer langsung. TRUSTED_PROXIES hanya daftar IP exact milik operator. */
    private function clientIp(): string
    {
        $peer = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!is_string($peer) || !filter_var($peer, FILTER_VALIDATE_IP)) return '0.0.0.0';

        $trusted = array_filter(array_map('trim', explode(',', getenv('TRUSTED_PROXIES') ?: '')),
            static fn($ip) => filter_var($ip, FILTER_VALIDATE_IP) !== false);
        $header = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (!in_array($peer, $trusted, true) || !is_string($header) || $header === '') return $peer;

        $chain = array_map('trim', explode(',', $header));
        foreach ($chain as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) return $peer;
        }
        // Traverse from the trusted peer; never trust a spoofable leftmost entry.
        $client = $peer;
        for ($i = count($chain) - 1; $i >= 0 && in_array($client, $trusted, true); $i--) {
            $client = $chain[$i];
        }
        return $client;
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

    /** HTTP credentials must be strings, never arrays/coerced values. */
    private function loginInput(): ?array
    {
        $identifier = $_POST['user_input'] ?? null;
        $password = $_POST['password'] ?? null;
        if (!is_string($identifier) || !is_string($password)
            || trim($identifier) === '' || $password === '') return null;
        return [trim($identifier), $password];
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
                $credentials = $this->loginInput();
                [$user_input, $password] = $credentials ?? ['', ''];
                $row = $credentials === null ? null : User::findByLogin($user_input);

                if ($row && password_verify($password, $row['password'])) {
                    $this->throttleClear($ip);
                    Auth::establishSession($row);

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
