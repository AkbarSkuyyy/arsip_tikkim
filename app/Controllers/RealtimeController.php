<?php
/**
 * Endpoint JSON realtime untuk dashboard & data permohonan (polling AJAX).
 * Semua angka dibatasi pada jenis dokumen yang boleh diakses user saat ini
 * (admin / akses '*' melihat semua).
 *
 * Query param opsional (diteruskan dari halaman data_permohonan):
 *   bulan, tahun, loker, cari, jenis
 */
class RealtimeController
{
    public function index(): void
    {
        Auth::requireLogin();

        $is_full = Auth::isAdmin();
        $kode_filter = [];
        if (!$is_full) {
            $kode_tunggal = Auth::aksesDokumenTunggal();
            $kode_filter = $kode_tunggal !== '' ? [$kode_tunggal] : ['__none__'];
        }

        // Modul jenis dokumen tertentu (?jenis=) — wajib punya akses.
        $jenis = trim((string)($_GET['jenis'] ?? ''));
        if ($jenis !== '') {
            if (!Auth::canAksesDokumen($jenis)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'forbidden']);
                exit;
            }
            $kode_filter = [$jenis];
        }

        $cond = '1=1';
        $types = '';
        $params = [];
        if (!empty($kode_filter)) {
            $ph = implode(',', array_fill(0, count($kode_filter), '?'));
            $cond = "p.dokumen IN ($ph)";
            $types = str_repeat('s', count($kode_filter));
            $params = $kode_filter;
        }

        // Total: global, atau terfilter bulan/tahun/loker/cari (halaman data_permohonan).
        $bulan = (int)($_GET['bulan'] ?? 0);
        $tahun = (int)($_GET['tahun'] ?? 0);
        $loker = (int)($_GET['loker'] ?? 0);
        $cari  = trim((string)($_GET['cari'] ?? ''));

        if ($bulan >= 1 && $bulan <= 12 && $tahun > 0) {
            $sql = "SELECT COUNT(*) AS t FROM permohonan p WHERE $cond AND MONTH(p.tanggal_input) = ? AND YEAR(p.tanggal_input) = ?";
            $t2 = $types . 'ii';
            $p2 = array_merge($params, [$bulan, $tahun]);
        } else {
            $sql = "SELECT COUNT(*) AS t FROM permohonan p WHERE $cond";
            $t2 = $types;
            $p2 = $params;
        }
        if ($loker > 0) { $sql .= " AND p.loker_id = ?"; $t2 .= 'i'; $p2[] = $loker; }
        if ($cari !== '') { $sql .= " AND p.nama_pemohon LIKE ?"; $t2 .= 's'; $p2[] = "%$cari%"; }
        $total = (int)(mysqli_fetch_assoc(Database::query($sql, $t2, $p2))['t'] ?? 0);

        $today     = (int)(mysqli_fetch_assoc(Database::query("SELECT COUNT(*) AS t FROM permohonan p WHERE $cond AND p.tanggal_input = CURDATE()", $types, $params))['t'] ?? 0);
        $bulan_ini = (int)(mysqli_fetch_assoc(Database::query("SELECT COUNT(*) AS t FROM permohonan p WHERE $cond AND DATE_FORMAT(p.tanggal_input,'%Y-%m') = DATE_FORMAT(CURDATE(),'%Y-%m')", $types, $params))['t'] ?? 0);
        $baru      = (int)(mysqli_fetch_assoc(Database::query("SELECT COUNT(*) AS t FROM permohonan p WHERE $cond AND p.keterangan LIKE '%Baru%'", $types, $params))['t'] ?? 0);
        $ganti     = (int)(mysqli_fetch_assoc(Database::query("SELECT COUNT(*) AS t FROM permohonan p WHERE $cond AND p.keterangan LIKE '%Penggantian%'", $types, $params))['t'] ?? 0);

        // Per jenis dokumen (hanya kode aktif).
        $counts = [];
        $r = Database::query("SELECT p.dokumen AS kode, COUNT(*) AS jumlah FROM permohonan p WHERE $cond GROUP BY p.dokumen", $types, $params);
        while ($row = mysqli_fetch_assoc($r)) $counts[$row['kode']] = (int)$row['jumlah'];
        $dokumen = [];
        foreach (MasterDokumen::allActive() as $d) {
            $dokumen[] = ['kode' => $d['kode'], 'nama' => $d['nama'], 'jumlah' => (int)($counts[$d['kode']] ?? 0)];
        }

        $loker_map = Permohonan::lokerMap($kode_filter);

        $users = User::count();
        $admin = User::countAdmin();

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode([
            'total'     => $total,
            'today'     => $today,
            'bulan_ini' => $bulan_ini,
            'baru'      => $baru,
            'ganti'     => $ganti,
            'users'     => $users,
            'admin'     => $admin,
            'dokumen'   => $dokumen,
            'loker'     => $loker_map,
            'waktu'     => date('H:i:s'),
        ]);
        exit;
    }
}
