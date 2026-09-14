<?php
/**
 * Controller: dashboard analitik super admin (manajemen user, log, backup, chart).
 */
class DashboardSuperController
{
    public function index(): void
    {
        Auth::requireSuperadmin();
        Session::set('ui_mode', 'super');

        $total_data  = Permohonan::count();
        $total_hari_ini  = Permohonan::countToday();
        $total_bulan_ini = Permohonan::countThisMonth();
        $total_users = User::count();
        $total_admin = User::countAdmin();
        $role_breakdown = User::roleBreakdown();

        [$dok_labels, $dok_data] = Permohonan::dokumenBreakdown();
        [$ket_labels, $ket_data] = Permohonan::keteranganBreakdown();
        $ket_proporsi = Permohonan::keteranganProportion();
        $ket_total = array_sum(array_column($ket_proporsi, 'jumlah'));
        [$tren_labels, $tren_data] = Permohonan::monthlyTrend(12);

        $nama_bulan_pendek = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
        foreach ($tren_labels as $i => $b) {
            $p = explode('-', $b);
            $tren_labels[$i] = ($nama_bulan_pendek[$p[1]] ?? $p[1]) . ' ' . $p[0];
        }

        [$log_labels, $log_data] = Log::dailyActivity(14);

        $users = User::all();
        $dokumen_list = MasterDokumen::allActive();
        $logs = Log::recent(10);

        $conn = Database::conn();
        $db_size_row = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.TABLES WHERE table_schema = DATABASE()
        "));
        $db_size_mb = $db_size_row['size_mb'] ?? 0;
        $total_logs = Log::count();

        require __DIR__ . '/../Views/dashboard/super.php';
    }
}
