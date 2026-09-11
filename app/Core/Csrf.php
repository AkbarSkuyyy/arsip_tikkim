<?php
/**
 * CSRF token: generate, render field, verifikasi. Setara csrf_token/csrf_field/csrf_verify lama.
 */
class Csrf
{
    public static function token(): string
    {
        Session::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    public static function verify(): void
    {
        Session::start();
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('Aksi ditolak: token keamanan (CSRF) tidak valid. Muat ulang halaman lalu coba lagi.');
        }
    }
}
