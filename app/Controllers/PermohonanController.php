<?php
/**
 * Controller: CRUD data permohonan (arsip) dengan filter bulan/tahun/keyword/jenis + pagination.
 * Akses tiap jenis dokumen dibatasi role/akses user (admin melihat semua). Modul per jenis
 * dokumen diakses lewat ?jenis=<kode>.
 */
class PermohonanController
{
    public function index(): void
    {
        Auth::requireLogin();

        // Viewer (kepala kantor) read-only: tolak semua mutasi POST.
        if (Auth::isReadOnly() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            Session::flashSet('error', 'Akun Anda hanya bisa melihat data (read-only).');
            header("Location: data_permohonan.php");
            exit;
        }

        $is_full = Auth::isFullDataAccess();

        $dokumen_list = MasterDokumen::allActive();
        $dok_by_kode = [];
        foreach ($dokumen_list as $d) $dok_by_kode[$d['kode']] = $d['nama'];

        // Resolusi jenis dokumen (?jenis=) + filter akses untuk staf.
        $jenis = isset($_GET['jenis']) ? trim((string)$_GET['jenis']) : '';
        $jenis_nama = '';
        $kode_filter = [];

        if ($jenis !== '') {
            if (!isset($dok_by_kode[$jenis])) {
                header("Location: data_permohonan.php");
                exit;
            }
            Auth::requireAksesDokumen($jenis);
            $jenis_nama = $dok_by_kode[$jenis];
            $kode_filter = [$jenis];
        } elseif (!$is_full) {
            $kode_tunggal = Auth::aksesDokumenTunggal();
            $kode_filter = $kode_tunggal !== '' ? [$kode_tunggal] : ['__none__'];
        }

        // 1. HAPUS
        if (isset($_POST['hapus_id'])) {
            Csrf::verify();
            $id_hapus = (int)$_POST['hapus_id'];
            if ($id_hapus > 0) {
                $d = Permohonan::find($id_hapus);
                if ($d && !Auth::canAksesDokumen($d['dokumen'])) {
                    Session::flashSet('error', 'Anda tidak memiliki akses untuk jenis dokumen tersebut!');
                } else {
                    $nama = $d['nama_pemohon'] ?? ('ID ' . $id_hapus);
                    if (Permohonan::delete($id_hapus)) {
                        catatLog(Auth::userNama(), "Menghapus data permohonan: " . $nama);
                        Session::flashSet('success', 'Data permohonan telah dihapus.');
                    } else {
                        Session::flashSet('error', 'Gagal menghapus data!');
                    }
                }
            }
            header("Location: data_permohonan.php" . ($jenis !== '' ? "?jenis=" . urlencode($jenis) : ""));
            exit;
        }

        // 2. SIMPAN BARU
        if (isset($_POST['simpan'])) {
            Csrf::verify();
            $data = $this->extractFormData($_POST);

            // Lokasi fisik (Loker -> Rak -> Urutan) diisi OTOMATIS oleh sistem.
            $lokasi = Permohonan::nextLokasiOtomatis();
            $data['loker_id'] = $lokasi['loker_id'] ?? 0;
            $data['rak'] = $lokasi['rak'] ?? '';
            $data['kotak'] = $lokasi['kotak'] ?? '';

            if ($data['tgl_lahir'] === null) {
                Session::flashSet('error', 'Tanggal lahir tidak valid!');
            } elseif (!Auth::canAksesDokumen($data['dokumen'])) {
                Session::flashSet('error', 'Anda tidak memiliki akses untuk jenis dokumen tersebut!');
            } elseif ($lokasi === null) {
                Session::flashSet('error', 'Semua loker arsip sudah penuh! Hubungi admin untuk menambah loker baru.');
            } elseif (Permohonan::create($data)) {
                catatLog(Auth::userNama(), "Menambahkan permohonan baru atas nama: " . $data['nama_pemohon'] . " (lokasi otomatis: L{$data['loker_id']}-R{$data['rak']}-U{$data['kotak']})");
                Session::flashSet('success', 'Data baru berhasil ditambahkan. Lokasi arsip otomatis: Loker ' . $data['loker_id'] . ' · Rak ' . $data['rak'] . ' · Urutan ' . $data['kotak'] . '.');
            } else {
                Session::flashSet('error', 'Gagal menambahkan data!');
            }
            header("Location: data_permohonan.php" . ($jenis !== '' ? "?jenis=" . urlencode($jenis) : ""));
            exit;
        }

        // 3. UPDATE
        if (isset($_POST['update'])) {
            Csrf::verify();
            $id_edit = (int)$_POST['id_permohonan'];
            if ($id_edit > 0) {
                $data = $this->extractFormData($_POST);
                if ($data['tgl_lahir'] === null) {
                    Session::flashSet('error', 'Tanggal lahir tidak valid!');
                } elseif (!Auth::canAksesDokumen($data['dokumen'])) {
                    Session::flashSet('error', 'Anda tidak memiliki akses untuk jenis dokumen tersebut!');
                } elseif (Permohonan::update($id_edit, $data)) {
                    catatLog(Auth::userNama(), "Mengubah data permohonan: " . $data['nama_pemohon']);
                    Session::flashSet('success', 'Data permohonan telah diperbarui.');
                } else {
                    Session::flashSet('error', 'Gagal memperbarui data!');
                }
            }
            header("Location: data_permohonan.php" . ($jenis !== '' ? "?jenis=" . urlencode($jenis) : ""));
            exit;
        }

        // 4. Filter & pencarian
        $bulan_pilihan = $_GET['bulan'] ?? date('m');
        $tahun_pilihan = $_GET['tahun'] ?? date('Y');
        $keyword       = isset($_GET['cari']) ? trim($_GET['cari']) : '';
        $loker_pilihan = (int)($_GET['loker'] ?? 0);

        $nama_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $rekap = Permohonan::rekapBulan((int)$bulan_pilihan, (int)$tahun_pilihan, $kode_filter);

        $batas_per_halaman = 15;
        $halaman_aktif = max(1, (int)($_GET['halaman'] ?? 1));
        $halaman_awal = ($halaman_aktif - 1) * $batas_per_halaman;
        $total_semua_data = Permohonan::searchCount($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan, $loker_pilihan, $kode_filter);
        $total_halaman = (int)ceil($total_semua_data / $batas_per_halaman);

        $data_rows = Permohonan::search($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan, $batas_per_halaman, $halaman_awal, $loker_pilihan, $kode_filter);

        // Dropdown jenis dokumen pada form: penuh untuk admin, terbatas untuk staf.
        if (!$is_full && $jenis === '' && !empty($kode_filter) && $kode_filter !== ['__none__']) {
            $dokumen_list = array_values(array_filter($dokumen_list, function ($d) use ($kode_filter) {
                return in_array($d['kode'], $kode_filter, true);
            }));
        }

        $ket_list = MasterKeterangan::allActive();
        $loker_list = MasterLoker::allActive();
        $loker_map = Permohonan::lokerMap($kode_filter);

        require __DIR__ . '/../Views/permohonan/index.php';
    }

    private function extractFormData(array $post): array
    {
        return [
            'nama_pemohon' => trim((string)($post['nama_pemohon'] ?? '')),
            'no_permohonan' => trim((string)($post['no_permohonan'] ?? '')),
            'tgl_lahir' => norm_tgl_lahir($post['tgl_lahir'] ?? null),
            'dokumen' => trim((string)($post['dokumen'] ?? '')),
            'jenis_kelamin' => (($post['jenis_kelamin'] ?? '') === 'P') ? 'P' : 'L',
            'keterangan' => trim((string)($post['keterangan'] ?? '')),
            'tanggal_input' => $post['tanggal_input'] ?? date('Y-m-d'),
            'loker_id' => (int)($post['loker_id'] ?? 0),
            'rak' => strtoupper(trim((string)($post['rak'] ?? ''))),
            'kotak' => trim((string)($post['kotak'] ?? '')),
        ];
    }
}
