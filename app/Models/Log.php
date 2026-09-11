<?php
/**
 * Model tabel logs (audit trail). Setara query di dashboard_super.php,
 * audit_trail.php, catatLog() lama.
 */
class Log
{
    public static function count(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM logs");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function recent(int $limit = 10): array
    {
        $r = Database::raw("SELECT * FROM logs ORDER BY id DESC LIMIT " . (int)$limit);
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function dailyActivity(int $days = 14): array
    {
        $r = Database::raw("SELECT DATE(waktu) AS tgl, COUNT(*) AS total FROM logs WHERE waktu >= DATE_SUB(CURDATE(), INTERVAL " . ((int)$days - 1) . " DAY) GROUP BY DATE(waktu) ORDER BY tgl ASC");
        $map = [];
        while ($row = mysqli_fetch_assoc($r)) $map[$row['tgl']] = (int)$row['total'];

        $labels = []; $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $labels[] = date('d/m', strtotime($d));
            $data[] = $map[$d] ?? 0;
        }
        return [$labels, $data];
    }

    public static function distinctUsers(): array
    {
        $r = Database::raw("SELECT DISTINCT user FROM logs WHERE user != '' ORDER BY user ASC");
        $users = [];
        while ($row = mysqli_fetch_assoc($r)) $users[] = $row['user'];
        return $users;
    }

    /**
     * Filter + paging untuk halaman Audit Trail.
     */
    public static function search(string $cari, string $user, string $tglMulai, string $tglSelesai, int $limit, int $offset): array
    {
        [$where, $types, $params] = self::buildWhere($cari, $user, $tglMulai, $tglSelesai);
        $sql = "SELECT * FROM logs $where ORDER BY id DESC LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $r = Database::query($sql, $types, $params);
        $rows = [];
        if ($r) while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function searchCount(string $cari, string $user, string $tglMulai, string $tglSelesai): int
    {
        [$where, $types, $params] = self::buildWhere($cari, $user, $tglMulai, $tglSelesai);
        $sql = "SELECT COUNT(*) AS total FROM logs $where";
        if ($types === '') {
            $r = Database::raw($sql);
        } else {
            $r = Database::query($sql, $types, $params);
        }
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    private static function buildWhere(string $cari, string $user, string $tglMulai, string $tglSelesai): array
    {
        $kondisi = []; $types = ''; $params = [];
        if ($cari !== '') { $kondisi[] = "aksi LIKE ?"; $types .= 's'; $params[] = "%$cari%"; }
        if ($user !== '') { $kondisi[] = "user = ?"; $types .= 's'; $params[] = $user; }
        if ($tglMulai !== '') { $kondisi[] = "DATE(waktu) >= ?"; $types .= 's'; $params[] = $tglMulai; }
        if ($tglSelesai !== '') { $kondisi[] = "DATE(waktu) <= ?"; $types .= 's'; $params[] = $tglSelesai; }
        $where = count($kondisi) > 0 ? ('WHERE ' . implode(' AND ', $kondisi)) : '';
        return [$where, $types, $params];
    }

    public static function kategoriAksi(string $aksi): array
    {
        $a = strtolower($aksi);
        if (str_contains($a, 'menghapus')) return ['Hapus', 'badge-hapus'];
        if (str_contains($a, 'menambah')) return ['Tambah', 'badge-tambah'];
        if (str_contains($a, 'mengubah') || str_contains($a, 'update') || str_contains($a, 'memperbarui')) return ['Ubah', 'badge-ubah'];
        if (str_contains($a, 'login')) return ['Login', 'badge-login'];
        if (str_contains($a, 'logout') || str_contains($a, 'keluar')) return ['Logout', 'badge-logout'];
        if (str_contains($a, 'import')) return ['Import', 'badge-import'];
        if (str_contains($a, 'backup')) return ['Backup', 'badge-backup'];
        if (str_contains($a, 'restore')) return ['Restore', 'badge-restore'];
        return ['Lainnya', 'badge-lain'];
    }
}
