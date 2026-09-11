<?php
/**
 * Controller: CRUD data permohonan (arsip SPRI) dengan filter bulan/tahun/keyword + pagination.
 */
class PermohonanController
{
    public function index(): void
    {
        Auth::requireLogin();

        // 1. HAPUS
        if (isset($_POST['hapus_id'])) {
            Csrf::verify();
            $id_hapus = (int)$_POST['hapus_id'];
            if ($id_hapus > 0) {
                $d = Permohonan::find($id_hapus);
                $nama = $d['nama_pemohon'] ?? ('ID ' . $id_hapus);

                if (Permohonan::delete($id_hapus)) {
                    catatLog(Auth::userNama(), "Menghapus data permohonan: " . $nama);
                    Session::flashSet('success', 'Data permohonan telah dihapus.');
                } else {
                    Session::flashSet('error', 'Gagal menghapus data!');
                }
            }
            header("Location: data_permohonan.php");
            exit;
        }

        // 2. SIMPAN BARU
        if (isset($_POST['simpan'])) {
            Csrf::verify();
            $data = $this->extractFormData($_POST);

            if ($data['tgl_lahir'] === null) {
                Session::flashSet('error', 'Tanggal lahir tidak valid!');
            } elseif (Permohonan::create($data)) {
                catatLog(Auth::userNama(), "Menambahkan permohonan baru atas nama: " . $data['nama_pemohon']);
                Session::flashSet('success', 'Data baru berhasil ditambahkan.');
            } else {
                Session::flashSet('error', 'Gagal menambahkan data!');
            }
            header("Location: data_permohonan.php");
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
                } elseif (Permohonan::update($id_edit, $data)) {
                    catatLog(Auth::userNama(), "Mengubah data permohonan: " . $data['nama_pemohon']);
                    Session::flashSet('success', 'Data permohonan telah diperbarui.');
                } else {
                    Session::flashSet('error', 'Gagal memperbarui data!');
                }
            }
            header("Location: data_permohonan.php");
            exit;
        }

        // 4. Filter & pencarian
        $bulan_pilihan = $_GET['bulan'] ?? date('m');
        $tahun_pilihan = $_GET['tahun'] ?? date('Y');
        $keyword       = isset($_GET['cari']) ? trim($_GET['cari']) : '';

        $nama_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $rekap = Permohonan::rekapBulan((int)$bulan_pilihan, (int)$tahun_pilihan);

        $batas_per_halaman = 15;
        $halaman_aktif = max(1, (int)($_GET['halaman'] ?? 1));
        $halaman_awal = ($halaman_aktif - 1) * $batas_per_halaman;
        $total_semua_data = (int)($rekap['total_baru_atau_ganti'] ?? Permohonan::searchCount($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan));
        $total_semua_data = Permohonan::searchCount($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan);
        $total_halaman = (int)ceil($total_semua_data / $batas_per_halaman);

        $data_rows = Permohonan::search($keyword, (int)$bulan_pilihan, (int)$tahun_pilihan, $batas_per_halaman, $halaman_awal);

        require __DIR__ . '/../Views/permohonan/index.php';
    }

    private function extractFormData(array $post): array
    {
        return [
            'nama_pemohon' => trim($post['nama_pemohon']),
            'tgl_lahir' => norm_tgl_lahir($post['tgl_lahir'] ?? null),
            'dokumen' => trim($post['dokumen']),
            'jenis_kelamin' => ($post['jenis_kelamin'] === 'P') ? 'P' : 'L',
            'keterangan' => trim($post['keterangan']),
            'tanggal_input' => $post['tanggal_input'] ?? date('Y-m-d'),
        ];
    }
}
