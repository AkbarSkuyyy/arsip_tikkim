<?php
/**
 * Controller: export laporan permohonan ke format Excel (.xls via HTML table).
 */
class ExportController
{
    public function excel(): void
    {
        Auth::requireLogin();

        $instansi = Pengaturan::get() ?? ['kementerian' => '', 'instansi' => '', 'seksi' => '', 'pejabat_nama' => '', 'pejabat_jabatan' => '', 'pejabat_nip' => ''];

        $tgl_mulai  = $_GET['tgl_mulai']  ?? date('Y-m-01');
        $tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_SPRI_" . date('Ymd') . ".xls");

        $rows = Permohonan::byPeriode('', $tgl_mulai, $tgl_selesai);

        require __DIR__ . '/../Views/laporan/export_excel.php';
    }
}
