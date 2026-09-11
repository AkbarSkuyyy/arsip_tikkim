<?php
/**
 * Guard login & role. Setara blok "KUNCI KEAMANAN" yang diulang di setiap file lama.
 */
class Auth
{
    public static function check(): bool
    {
        return Session::has('login');
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header("Location: " . self::url('login.php'));
            exit;
        }
    }

    public static function isAdmin(): bool
    {
        return Session::get('user_role') === 'admin';
    }

    public static function requireAdmin(string $redirect = 'dashboard.php'): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: " . self::url($redirect));
            exit;
        }
    }

    public static function userId(): int
    {
        return (int)Session::get('user_id', 0);
    }

    public static function userNama(): string
    {
        return (string)Session::get('user_nama', '');
    }

    /**
     * URL halaman lama (file stub langsung, misal login.php, dashboard.php).
     * Dipertahankan agar semua link/bookmark existing tetap berfungsi.
     */
    public static function url(string $page): string
    {
        return $page;
    }
}
