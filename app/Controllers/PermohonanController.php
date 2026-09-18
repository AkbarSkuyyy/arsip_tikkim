<?php
/** CRUD arsip: validated input, record access and serialized location writes. */
class PermohonanController
{
    public function index(): void
    {
        Auth::requireLogin();
        if (Auth::isReadOnly() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            Session::flashSet('error', 'Akun Anda hanya bisa melihat data (read-only).');
            header('Location: data_permohonan.php');
            return;
        }
        $is_full = Auth::isFullDataAccess();
        $dokumen_list = MasterDokumen::allActive();
        $dok_by_kode = [];
        foreach ($dokumen_list as $d) $dok_by_kode[$d['kode']] = $d['nama'];
        $kode_filter = archive_scope();
        try {
            $jenis = archive_text($_GET['jenis'] ?? '', 'Jenis dokumen', 50);
            if ($jenis !== '' && (!isset($dok_by_kode[$jenis]) || ($kode_filter !== [] && !in_array($jenis, $kode_filter, true)))) {
                throw new InvalidArgumentException('Jenis dokumen tidak dikenal atau akses ditolak.');
            }
            if ($jenis !== '') $kode_filter = [$jenis];
            $jenis_nama = $dok_by_kode[$jenis] ?? '';
            $bulan_pilihan = sprintf('%02d', archive_integer($_GET['bulan'] ?? date('m'), 'Bulan', 1, 12));
            $tahun_pilihan = archive_integer($_GET['tahun'] ?? date('Y'), 'Tahun', 1900, (int)date('Y') + 1);
            $keyword = archive_text($_GET['cari'] ?? '', 'Pencarian', 255);
            $loker_pilihan = archive_integer($_GET['loker'] ?? '0', 'Loker', 0, 2147483647);
            $halaman_aktif = archive_integer($_GET['halaman'] ?? '1', 'Halaman', 1, 1000000);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }
        if (isset($_POST['hapus_id']) || isset($_POST['simpan']) || isset($_POST['update'])) {
            Csrf::verify();
            try {
                $message = $this->mutate($_POST);
                // Logging follows commit; a logging failure must not claim the write failed.
                try { catatLog(Auth::userNama(), $message); } catch (Throwable $e) { error_log('Archive audit log failed.'); }
                Session::flashSet('success', $message);
            } catch (InvalidArgumentException $e) {
                Session::flashSet('error', $e->getMessage());
            } catch (Throwable $e) {
                error_log('Archive mutation failed: ' . get_class($e));
                Session::flashSet('error', 'Penyimpanan gagal. Periksa database dan dukungan transaksi; jangan mengulang sebelum memeriksa hasil.');
            }
            header('Location: data_permohonan.php' . ($jenis !== '' ? '?jenis=' . urlencode($jenis) : ''));
            return;
        }
        $nama_bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $rekap = Permohonan::rekapBulan((int)$bulan_pilihan, (int)$tahun_pilihan, $kode_filter);
        $batas_per_halaman = 15;
        $halaman_awal = ($halaman_aktif - 1) * $batas_per_halaman;
        $total_semua_data = Permohonan::searchCount($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan, $loker_pilihan, $kode_filter);
        $total_halaman = (int)ceil($total_semua_data / $batas_per_halaman);
        $data_rows = Permohonan::search($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan, $batas_per_halaman, $halaman_awal, $loker_pilihan, $kode_filter);
        if (!$is_full) $dokumen_list = array_values(array_filter($dokumen_list, fn($d) => in_array((string)$d['kode'], $kode_filter, true)));
        $ket_list = MasterKeterangan::allActive();
        $loker_list = MasterLoker::allActive();
        $loker_map = Permohonan::lokerMap($kode_filter);
        require __DIR__ . '/../Views/permohonan/index.php';
    }

    private function mutate(array $post): string
    {
        $actions = array_intersect(['hapus_id','simpan','update'], array_keys($post));
        if (count($actions) !== 1) throw new InvalidArgumentException('Pilih tepat satu tindakan.');
        return Permohonan::withArchiveTransaction(function () use ($post): string {
            if (isset($post['hapus_id'])) {
                $id = archive_integer($post['hapus_id'], 'ID arsip', 1, 2147483647);
                $old = Permohonan::find($id, true);
                archive_require_record_access($old);
                if (!Permohonan::delete($id)) throw new RuntimeException('Delete failed.');
                return 'Data permohonan telah dihapus.';
            }
            $id = isset($post['update']) ? archive_integer($post['id_permohonan'] ?? '', 'ID arsip', 1, 2147483647) : 0;
            $old = $id ? Permohonan::find($id, true) : null;
            if ($id) archive_require_record_access($old);
            $data = $this->extractFormData($post);
            archive_require_record_access($data);
            if ($id) {
                $location = archive_location($post, MasterLoker::allActive(), $old);
                $unchanged = $location === ['loker_id'=>(int)$old['loker_id'], 'rak'=>(string)$old['rak'], 'kotak'=>(string)$old['kotak']];
                if (!$unchanged && Permohonan::locationOccupied($location, $id)) throw new InvalidArgumentException('Lokasi arsip sudah terisi.');
                if (!Permohonan::update($id, $data + $location)) throw new RuntimeException('Update failed.');
                return 'Data permohonan telah diperbarui.';
            }
            $location = Permohonan::nextLokasiOtomatis();
            if ($location === null) throw new InvalidArgumentException('Semua loker arsip penuh atau tidak tersedia.');
            if (!Permohonan::create($data + $location)) throw new RuntimeException('Insert failed.');
            return 'Data baru berhasil ditambahkan. Lokasi: L' . $location['loker_id'] . '-R' . $location['rak'] . '-U' . $location['kotak'] . '.';
        });
    }

    private function extractFormData(array $post): array
    {
        return archive_data($post);
    }
}
