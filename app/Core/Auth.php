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
     * Daftar kode jenis dokumen yang boleh diakses user saat ini.
     * '*' (admin / akses penuh) -> semua kode aktif dari master_dokumen.
     * User biasa dibatasi tepat SATU jenis dokumen (diatur oleh superadmin).
     */
    public static function aksesDokumen(): array
    {
        $akses = (string)Session::get('user_akses', '*');
        if ($akses === '' || $akses === '*') {
            return array_column(MasterDokumen::allActive(), 'kode');
        }
        $kode = array_filter(array_map('trim', explode(',', $akses)), 'strlen');
        return array_values(array_unique($kode));
    }

    /**
     * Kode jenis dokumen tunggal milik user biasa, atau '' jika admin/belum diset.
     */
    public static function aksesDokumenTunggal(): string
    {
        $akses = (string)Session::get('user_akses', '*');
        if ($akses === '' || $akses === '*') return '';
        $kode = array_filter(array_map('trim', explode(',', $akses)), 'strlen');
        return $kode ? (string)reset($kode) : '';
    }

    /**
     * Apakah user boleh mengakses jenis dokumen $kode? Admin / '*' selalu boleh.
     */
    public static function canAksesDokumen(string $kode): bool
    {
        if (self::isAdmin()) return true;
        $akses = (string)Session::get('user_akses', '*');
        if ($akses === '*') return true;
        return in_array($kode, self::aksesDokumen(), true);
    }

    /**
     * Wajib punya akses ke jenis dokumen $kode, selain itu redirect.
     */
    public static function requireAksesDokumen(string $kode, string $redirect = 'dashboard.php'): void
    {
        self::requireLogin();
        if (!self::canAksesDokumen($kode)) {
            header("Location: " . self::url($redirect));
            exit;
        }
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
