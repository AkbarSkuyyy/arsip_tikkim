<?php
/**
 * Controller: dashboard analitik staf (halaman utama setelah login).
 */
class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();
        Session::set('ui_mode', 'staff');

        // Scope data: petugas hanya lihat jenis dokumennya sendiri;
        // admin, superadmin, dan viewer melihat semua.
        $kode_filter = [];
        if (Auth::isPetugas()) {
            $kode_tunggal = Auth::aksesDokumenTunggal();
            if ($kode_tunggal !== '') $kode_filter = [$kode_tunggal];
        }

        $total_data = Permohonan::count($kode_filter);
        $total_baru = Permohonan::countByKeteranganLike('Baru', $kode_filter);
        $total_ganti = Permohonan::countByKeteranganLike('Penggantian', $kode_filter);
        $gender = Permohonan::genderBreakdown($kode_filter);
        $label_jk = ['Laki-laki', 'Perempuan'];
        $data_jk = [$gender['L'], $gender['P']];
        $recent = Permohonan::recent(5, $kode_filter);
        [$tren_labels, $tren_data] = Permohonan::monthlyTrend(12, $kode_filter);

        $nama_bulan_pendek = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
        foreach ($tren_labels as $i => $b) {
            $p = explode('-', $b);
            $tren_labels[$i] = ($nama_bulan_pendek[$p[1]] ?? $p[1]) . ' ' . $p[0];
        }

        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
