<?php
/**
 * Controller: audit trail (log aktivitas lengkap dengan filter + pagination).
 */
class AuditTrailController
{
    public function index(): void
    {
        Auth::requireAdmin();

        $cari      = isset($_GET['cari']) ? trim($_GET['cari']) : '';
        $f_user    = isset($_GET['user']) ? trim($_GET['user']) : '';
        $tgl_mulai = isset($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
        $tgl_selesai = isset($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
        $halaman   = max(1, (int)($_GET['halaman'] ?? 1));
        $per_halaman = 25;

        $total_rows = Log::searchCount($cari, $f_user, $tgl_mulai, $tgl_selesai);
        $total_halaman = max(1, (int)ceil($total_rows / $per_halaman));
        $halaman = min($halaman, $total_halaman);
        $offset = ($halaman - 1) * $per_halaman;

        $rows = Log::search($cari, $f_user, $tgl_mulai, $tgl_selesai, $per_halaman, $offset);
        $daftar_user = Log::distinctUsers();

        require __DIR__ . '/../Views/audit_trail/index.php';
    }
}
