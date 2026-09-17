<?php
/**
 * Controller: master data jenis dokumen & keterangan (dropdown form input permohonan).
 */
class MasterDataController
{
    public function index(): void
    {
        Auth::requireSuperadmin();

        // Halaman ini khusus superadmin, jadi selalu admin-level.
        $is_admin = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $aksi = $_POST['aksi'] ?? '';

            if ($aksi === 'dokumen_simpan') {
                $kode = trim($_POST['kode']);
                $nama = trim($_POST['nama']);
                if ($kode !== '' && $nama !== '') {
                    if (MasterDokumen::upsert($kode, $nama)) {
                        Session::flashSet('success', 'Jenis dokumen disimpan.');
                    } else {
                        Session::flashSet('error', 'Gagal menyimpan jenis dokumen.');
                    }
                }
            } elseif ($aksi === 'dokumen_hapus') {
                MasterDokumen::deactivate((int)$_POST['id']);
                Session::flashSet('success', 'Jenis dokumen dinonaktifkan.');
            } elseif ($aksi === 'keterangan_simpan') {
                $nama = trim($_POST['nama']);
                if ($nama !== '') {
                    if (MasterKeterangan::upsert($nama)) {
                        Session::flashSet('success', 'Keterangan disimpan.');
                    } else {
                        Session::flashSet('error', 'Gagal menyimpan keterangan.');
                    }
                }
            } elseif ($aksi === 'keterangan_hapus') {
                MasterKeterangan::deactivate((int)$_POST['id']);
                Session::flashSet('success', 'Keterangan dinonaktifkan.');
            } elseif ($aksi === 'loker_simpan') {
                $nomor = trim($_POST['nomor']);
                $nama = trim($_POST['nama']);
                if ($nomor !== '' && $nama !== '') {
                    if (MasterLoker::upsert($nomor, $nama)) {
                        Session::flashSet('success', 'Loker disimpan.');
                    } else {
                        Session::flashSet('error', 'Gagal menyimpan loker.');
                    }
                }
            } elseif ($aksi === 'loker_hapus') {
                MasterLoker::deactivate((int)$_POST['id']);
                Session::flashSet('success', 'Loker dinonaktifkan.');
            }

            header("Location: master_data.php");
            exit;
        }

        $dokumen_list = MasterDokumen::all();
        $ket_list = MasterKeterangan::all();
        $loker_list = MasterLoker::all();

        require __DIR__ . '/../Views/master_data/index.php';
    }
}
