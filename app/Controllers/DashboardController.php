<?php
/**
 * Controller: dashboard analitik staf (halaman utama setelah login).
 */
class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $total_data = Permohonan::count();
        $total_baru = Permohonan::countByKeteranganLike('Baru');
        $total_ganti = Permohonan::countByKeteranganLike('Penggantian');
        $gender = Permohonan::genderBreakdown();
        $label_jk = ['Laki-laki', 'Perempuan'];
        $data_jk = [$gender['L'], $gender['P']];
        $recent = Permohonan::recent(5);
        [$tren_labels, $tren_data] = Permohonan::monthlyTrend(12);

        $nama_bulan_pendek = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
        foreach ($tren_labels as $i => $b) {
            $p = explode('-', $b);
            $tren_labels[$i] = ($nama_bulan_pendek[$p[1]] ?? $p[1]) . ' ' . $p[0];
        }

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
