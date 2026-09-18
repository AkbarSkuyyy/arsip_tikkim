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

        try {
            [$cari, $tgl_mulai, $tgl_selesai] = archive_report_filters($_GET);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }

        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Laporan_SPRI_" . date('Ymd') . ".xls");

        $kode_filter = archive_scope();

        $rows = Permohonan::byPeriode($cari, $tgl_mulai, $tgl_selesai, $kode_filter);

        require __DIR__ . '/../Views/laporan/export_excel.php';
    }
}
