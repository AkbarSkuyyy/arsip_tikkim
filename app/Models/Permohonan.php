<?php
/**
 * Model tabel permohonan (data SPRI). Setara query mutasi/select yang
 * sebelumnya tersebar di data_permohonan.php, tambah_permohonan.php, laporan.php.
 */
class Permohonan
{
    public static function find(int $id): ?array
    {
        $r = Database::query("SELECT * FROM permohonan WHERE id = ?", 'i', [$id]);
        $row = $r ? mysqli_fetch_assoc($r) : null;
        return $row ?: null;
    }

    public static function create(array $data): bool
    {
        return Database::exec(
            "INSERT INTO permohonan (nama_pemohon, tgl_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input, loker_id, rak, kotak) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'ssssssiss',
            [$data['nama_pemohon'], $data['tgl_lahir'], $data['dokumen'], $data['jenis_kelamin'], $data['keterangan'], $data['tanggal_input'], (int)$data['loker_id'], $data['rak'], $data['kotak']]
        );
    }

    public static function update(int $id, array $data): bool
    {
        return Database::exec(
            "UPDATE permohonan SET nama_pemohon = ?, tgl_lahir = ?, dokumen = ?, jenis_kelamin = ?, keterangan = ?, tanggal_input = ?, loker_id = ?, rak = ?, kotak = ? WHERE id = ?",
            'ssssssissi',
            [$data['nama_pemohon'], $data['tgl_lahir'], $data['dokumen'], $data['jenis_kelamin'], $data['keterangan'], $data['tanggal_input'], (int)$data['loker_id'], $data['rak'], $data['kotak'], $id]
        );
    }

    public static function delete(int $id): bool
    {
        return Database::exec("DELETE FROM permohonan WHERE id = ?", 'i', [$id]);
    }

    public static function count(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM permohonan");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function countByKeteranganLike(string $like): int
    {
        $r = Database::query("SELECT COUNT(*) as total FROM permohonan WHERE keterangan LIKE ?", 's', ["%$like%"]);
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function countToday(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM permohonan WHERE tanggal_input = CURDATE()");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function countThisMonth(): int
    {
        $r = Database::raw("SELECT COUNT(*) as total FROM permohonan WHERE DATE_FORMAT(tanggal_input, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function genderBreakdown(): array
    {
        $r = Database::raw("SELECT jenis_kelamin, COUNT(*) as jumlah FROM permohonan WHERE jenis_kelamin IN ('L','P') GROUP BY jenis_kelamin");
        $laki = 0; $perempuan = 0;
        while ($row = mysqli_fetch_assoc($r)) {
            if (strtoupper($row['jenis_kelamin']) === 'L') $laki = (int)$row['jumlah'];
            if (strtoupper($row['jenis_kelamin']) === 'P') $perempuan = (int)$row['jumlah'];
        }
        return ['L' => $laki, 'P' => $perempuan];
    }

    public static function recent(int $limit = 5): array
    {
        $r = Database::raw("SELECT * FROM permohonan ORDER BY id DESC LIMIT " . (int)$limit);
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function monthlyTrend(int $months = 12): array
    {
        $r = Database::raw("SELECT DATE_FORMAT(tanggal_input, '%Y-%m') AS bulan, COUNT(*) AS total FROM permohonan WHERE tanggal_input IS NOT NULL GROUP BY DATE_FORMAT(tanggal_input, '%Y-%m') ORDER BY bulan DESC LIMIT " . (int)$months);
        $labels = []; $data = [];
        while ($row = mysqli_fetch_assoc($r)) { $labels[] = $row['bulan']; $data[] = (int)$row['total']; }
        return [array_reverse($labels), array_reverse($data)];
    }

    public static function dokumenBreakdown(): array
    {
        $r = Database::raw("
            SELECT COALESCE(md.nama, CONCAT('Kode ', p.dokumen)) AS nama, COUNT(*) AS jumlah
            FROM permohonan p
            LEFT JOIN master_dokumen md ON md.kode = p.dokumen
            GROUP BY p.dokumen, md.nama
            ORDER BY jumlah DESC
        ");
        $labels = []; $data = [];
        while ($row = mysqli_fetch_assoc($r)) { $labels[] = $row['nama']; $data[] = (int)$row['jumlah']; }
        return [$labels, $data];
    }

    public static function keteranganBreakdown(): array
    {
        $r = Database::raw("SELECT keterangan, COUNT(*) AS jumlah FROM permohonan GROUP BY keterangan ORDER BY jumlah DESC");
        $labels = []; $data = [];
        while ($row = mysqli_fetch_assoc($r)) { $labels[] = $row['keterangan']; $data[] = (int)$row['jumlah']; }
        return [$labels, $data];
    }

    /**
     * Proporsi lengkap tiap keterangan (dari master_keterangan, termasuk
     * yang belum punya data). Return: [ ['nama','jumlah','persen'], ... ]
     */
    public static function keteranganProportion(): array
    {
        $r = Database::raw("
            SELECT mk.nama, COUNT(p.id) AS jumlah
            FROM master_keterangan mk
            LEFT JOIN permohonan p ON p.keterangan = mk.nama
            WHERE mk.aktif = 1
            GROUP BY mk.id, mk.nama
            ORDER BY jumlah DESC, mk.nama ASC
        ");
        $rows = [];
        $total = 0;
        while ($row = mysqli_fetch_assoc($r)) {
            $rows[] = ['nama' => $row['nama'], 'jumlah' => (int)$row['jumlah']];
            $total += (int)$row['jumlah'];
        }
        foreach ($rows as &$row) {
            $row['persen'] = $total > 0 ? round($row['jumlah'] / $total * 100, 2) : 0.0;
        }
        unset($row);
        return $rows;
    }

    /**
     * Peta loker: total arsip per loker aktif (semua periode) untuk grid
     * "Peta Loker" di data_permohonan.php. $kodeFilter = daftar kode dokumen
     * yang boleh diakses (kosong = semua).
     */
    public static function lokerMap(array $kodeFilter = []): array
    {
        $sql = "SELECT ml.id, ml.nomor, ml.nama, COUNT(p.id) AS jumlah
                FROM master_loker ml
                LEFT JOIN permohonan p ON p.loker_id = ml.id";
        $types = '';
        $params = [];
        if (!empty($kodeFilter)) {
            $sql .= " AND p.dokumen IN (" . implode(',', array_fill(0, count($kodeFilter), '?')) . ")";
            $types = str_repeat('s', count($kodeFilter));
            $params = $kodeFilter;
        }
        $sql .= " WHERE ml.aktif = 1
                GROUP BY ml.id, ml.nomor, ml.nama
                ORDER BY CAST(ml.nomor AS UNSIGNED) ASC";
        $r = Database::query($sql, $types, $params);
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    /**
     * Query filter+paging untuk data_permohonan.php (kelola arsip per bulan).
     */
    public static function search(string $keyword, int $bulan, int $tahun, int $limit, int $offset, int $lokerId = 0, array $kodeFilter = []): array
    {
        $sql = "SELECT p.*, ml.nomor AS loker_nomor FROM permohonan p LEFT JOIN master_loker ml ON ml.id = p.loker_id WHERE MONTH(p.tanggal_input) = ? AND YEAR(p.tanggal_input) = ?";
        $types = 'ii';
        $params = [$bulan, $tahun];
        if ($lokerId > 0) {
            $sql .= " AND p.loker_id = ?";
            $types .= 'i';
            $params[] = $lokerId;
        }
        if (!empty($kodeFilter)) {
            $sql .= " AND p.dokumen IN (" . implode(',', array_fill(0, count($kodeFilter), '?')) . ")";
            $types .= str_repeat('s', count($kodeFilter));
            foreach ($kodeFilter as $k) $params[] = $k;
        }
        if ($keyword !== '') {
            $sql .= " AND p.nama_pemohon LIKE ?";
            $types .= 's';
            $params[] = "%$keyword%";
        }
        $sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
        $r = Database::query($sql, $types, $params);
        $rows = [];
        if ($r) while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function searchCount(string $keyword, int $bulan, int $tahun, int $lokerId = 0, array $kodeFilter = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM permohonan WHERE MONTH(tanggal_input) = ? AND YEAR(tanggal_input) = ?";
        $types = 'ii';
        $params = [$bulan, $tahun];
        if ($lokerId > 0) {
            $sql .= " AND loker_id = ?";
            $types .= 'i';
            $params[] = $lokerId;
        }
        if (!empty($kodeFilter)) {
            $sql .= " AND dokumen IN (" . implode(',', array_fill(0, count($kodeFilter), '?')) . ")";
            $types .= str_repeat('s', count($kodeFilter));
            foreach ($kodeFilter as $k) $params[] = $k;
        }
        if ($keyword !== '') {
            $sql .= " AND nama_pemohon LIKE ?";
            $types .= 's';
            $params[] = "%$keyword%";
        }
        $r = Database::query($sql, $types, $params);
        return (int)(mysqli_fetch_assoc($r)['total'] ?? 0);
    }

    public static function rekapBulan(int $bulan, int $tahun, array $kodeFilter = []): array
    {
        $sql = "SELECT
                COUNT(*) AS total_baru_atau_ganti,
                SUM(CASE WHEN keterangan = 'Baru' THEN 1 ELSE 0 END) AS total_baru,
                SUM(CASE WHEN keterangan LIKE '%Penggantian%' THEN 1 ELSE 0 END) AS total_penggantian,
                SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) AS total_laki,
                SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) AS total_perempuan
             FROM permohonan WHERE MONTH(tanggal_input) = ? AND YEAR(tanggal_input) = ?";
        $types = 'ii';
        $params = [$bulan, $tahun];
        if (!empty($kodeFilter)) {
            $sql .= " AND dokumen IN (" . implode(',', array_fill(0, count($kodeFilter), '?')) . ")";
            $types .= str_repeat('s', count($kodeFilter));
            foreach ($kodeFilter as $k) $params[] = $k;
        }
        $r = Database::query($sql, $types, $params);
        return mysqli_fetch_assoc($r) ?: ['total_baru' => 0, 'total_penggantian' => 0, 'total_laki' => 0, 'total_perempuan' => 0];
    }

    /** Laporan periode tanggal (laporan.php / export_excel.php). */
    public static function byPeriode(string $keyword, string $tglMulai, string $tglSelesai): array
    {
        $sql = "SELECT * FROM permohonan WHERE tanggal_input BETWEEN ? AND ?";
        $types = 'ss';
        $params = [$tglMulai, $tglSelesai];
        if ($keyword !== '') {
            $sql .= " AND nama_pemohon LIKE ?";
            $types .= 's';
            $params[] = "%$keyword%";
        }
        $sql .= " ORDER BY tanggal_input ASC, id ASC";
        $r = Database::query($sql, $types, $params);
        $rows = [];
        if ($r) while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }
}
