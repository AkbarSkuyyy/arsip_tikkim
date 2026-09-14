<?php
/**
 * Guard login, role, dan akses dokumen.
 *
 * Role (4 tingkat):
 *  - superadmin : pengelola sistem penuh (kelola akun, master data, pengaturan, backup, audit) + semua data
 *  - admin      : pengumpul data — lihat/kelola SEMUA jenis dokumen, laporan, import; TANPA menu sistem
 *  - user       : petugas arsip — fokus 1 jenis dokumen (input/edit/hapus jenisnya sendiri)
 *  - viewer     : kepala kantor — lihat semua jenis (read-only) + export laporan, tanpa hak ubah
 */
class Auth
{
    public static function check(): bool
    {
        return Session::has('login');
    }

    /** Timeout idle (menit). 0 = nonaktif. */
    public static function sessionTimeoutMinutes(): int
    {
        return 30;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header("Location: " . self::url('login.php'));
            exit;
        }

        // Session timeout: logout otomatis jika idle melebihi batas.
        $timeout = self::sessionTimeoutMinutes() * 60;
        if ($timeout > 0) {
            $last = (int)Session::get('last_activity', 0);
            if ($last === 0) {
                Session::set('last_activity', time());
            } elseif (time() - $last > $timeout) {
                Session::destroy();
                header("Location: " . self::url('login.php') . '?timeout=1');
                exit;
            } else {
                Session::set('last_activity', time());
            }
        }
    }

    // ---- Pemeriksaan role ----

    public static function role(): string
    {
        return (string)Session::get('user_role', '');
    }

    public static function isSuperadmin(): bool
    {
        return self::role() === 'superadmin';
    }

    /**
     * "Admin-level" (akses data penuh): superadmin ATAU admin pengumpul.
     * Keduanya boleh lihat/kelola SEMUA jenis dokumen. Bedanya, superadmin
     * juga pegang menu sistem (akun, master, pengaturan, backup, audit).
     */
    public static function isAdmin(): bool
    {
        return self::role() === 'admin' || self::role() === 'superadmin';
    }

    public static function isPetugas(): bool
    {
        return self::role() === 'user';
    }

    public static function isViewer(): bool
    {
        return self::role() === 'viewer';
    }

    /** Read-only (hanya lihat/export, tidak bisa input/edit/hapus). */
    public static function isReadOnly(): bool
    {
        return self::isViewer();
    }

    /** Akses data penuh (lihat semua jenis dokumen): superadmin, admin, atau viewer. */
    public static function isFullDataAccess(): bool
    {
        return self::isAdmin() || self::isViewer();
    }

    /** Boleh input/edit data (superadmin, admin, petugas). Viewer TIDAK boleh. */
    public static function canMutate(): bool
    {
        return !self::isViewer();
    }

    // ---- Guard ----

    /** Halaman sistem (akun, master data, pengaturan, backup, audit) khusus superadmin. */
    public static function requireSuperadmin(string $redirect = 'dashboard.php'): void
    {
        self::requireLogin();
        if (!self::isSuperadmin()) {
            header("Location: " . self::url($redirect));
            exit;
        }
    }

    /** Halaman data penuh (superadmin atau admin pengumpul). */
    public static function requireAdmin(string $redirect = 'dashboard.php'): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: " . self::url($redirect));
            exit;
        }
    }

    // ---- Akses per jenis dokumen ----

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
     * '*' (superadmin/admin/viewer — akses penuh) -> semua kode aktif.
     * Petugas dibatasi tepat SATU jenis dokumen.
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

    /** Kode jenis dokumen tunggal milik petugas, atau '' jika bukan petugas. */
    public static function aksesDokumenTunggal(): string
    {
        $akses = (string)Session::get('user_akses', '*');
        if ($akses === '' || $akses === '*') return '';
        $kode = array_filter(array_map('trim', explode(',', $akses)), 'strlen');
        return $kode ? (string)reset($kode) : '';
    }

    /** Apakah user boleh mengakses jenis dokumen $kode? Admin-level / '*' selalu boleh. */
    public static function canAksesDokumen(string $kode): bool
    {
        if (self::isAdmin()) return true;
        $akses = (string)Session::get('user_akses', '*');
        if ($akses === '*') return true;
        return in_array($kode, self::aksesDokumen(), true);
    }

    /** Wajib punya akses ke jenis dokumen $kode, selain itu redirect. */
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
