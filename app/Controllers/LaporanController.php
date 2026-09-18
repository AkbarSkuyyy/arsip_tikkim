<?php
/**
 * Controller: laporan rekapitulasi permohonan per periode tanggal (dengan cetak PDF & export Excel).
 */
class LaporanController
{
    public function index(): void
    {
        Auth::requireLogin();

        try {
            [$cari, $tgl_mulai, $tgl_selesai] = archive_report_filters($_GET);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }

        $kode_filter = archive_scope();

        $data_laporan = Permohonan::byPeriode($cari, $tgl_mulai, $tgl_selesai, $kode_filter);

        $total_data = count($data_laporan);
        $total_L = 0; $total_P = 0; $total_baru = 0; $total_ganti = 0;
        foreach ($data_laporan as $row) {
            if ($row['jenis_kelamin'] == 'L') { $total_L++; } else { $total_P++; }
            if ($row['keterangan'] == 'Baru') { $total_baru++; } else { $total_ganti++; }
        }

        $teks_periode = date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai));

        require __DIR__ . '/../Views/laporan/index.php';
    }
}
