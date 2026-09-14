<?php
/**
 * Controller: laporan rekapitulasi permohonan per periode tanggal (dengan cetak PDF & export Excel).
 */
class LaporanController
{
    public function index(): void
    {
        Auth::requireLogin();

        $cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
        $tgl_mulai  = $_GET['tgl_mulai']  ?? date('Y-m-01');
        $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-t');

        $kode_filter = [];
        if (Auth::isPetugas()) {
            $kode_tunggal = Auth::aksesDokumenTunggal();
            if ($kode_tunggal !== '') $kode_filter = [$kode_tunggal];
        }

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
