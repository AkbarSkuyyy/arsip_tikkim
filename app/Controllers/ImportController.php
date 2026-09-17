<?php
/**
 * Controller: import data permohonan dari file CSV.
 */
class ImportController
{
    public function index(): void
    {
        Auth::requireSuperadmin();

        $pesan = null;
        $pesan_error = null;

        if (isset($_POST['import'])) {
            Csrf::verify();

            $file = $_FILES['file_csv']['tmp_name'];
            $bulan_arsip = $_POST['bulan_arsip'];
            $tahun_arsip = date('Y');
            $tanggal_input = $tahun_arsip . '-' . $bulan_arsip . '-01';

            $handle = fopen($file, "r");
            if (!$handle) {
                $pesan_error = "Gagal membaca file CSV!";
            } else {
                fgetcsv($handle, 1000, ","); // lewati header

                $berhasil = 0;
                $gagal = 0;
                $dilewati = 0;

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $nama = isset($data[1]) ? trim($data[1]) : '';

                    if (empty($nama)) {
                        $dilewati++;
                        continue;
                    }

                    $tahun_raw   = isset($data[2]) ? preg_replace('/\.0$/', '', trim($data[2])) : '';
                    $bulan_raw   = isset($data[3]) ? preg_replace('/\.0$/', '', trim($data[3])) : '';
                    $tanggal_raw = isset($data[4]) ? preg_replace('/\.0$/', '', trim($data[4])) : '';
                    $dokumen_raw = isset($data[5]) ? preg_replace('/\.0$/', '', trim($data[5])) : '';
                    $jk          = isset($data[6]) ? trim($data[6]) : '';
                    $keterangan  = isset($data[7]) ? trim($data[7]) : '';

                    $bulan   = str_pad((int)$bulan_raw, 2, "0", STR_PAD_LEFT);
                    $tanggal = str_pad((int)$tanggal_raw, 2, "0", STR_PAD_LEFT);
                    $tgl_lahir = norm_tgl_lahir($tahun_raw . '-' . $bulan . '-' . $tanggal);

                    if ($tgl_lahir === null) {
                        $gagal++;
                        continue;
                    }

                    if (Permohonan::create(['nama_pemohon' => $nama, 'tgl_lahir' => $tgl_lahir, 'dokumen' => $dokumen_raw, 'jenis_kelamin' => $jk, 'keterangan' => $keterangan, 'tanggal_input' => $tanggal_input])) {
                        $berhasil++;
                    } else {
                        $gagal++;
                    }
                }
                fclose($handle);

                $nama_bulan = date("F", mktime(0, 0, 0, (int)$bulan_arsip, 10));
                $pesan = "<strong>Berhasil!</strong> $berhasil data masuk ke bulan $nama_bulan. <br><span style='font-size: 0.85rem; font-weight:normal; margin-top:5px; display:inline-block;'>(Sebanyak $dilewati baris Excel yang kosong/tanpa nama telah dibuang secara otomatis).</span>";
                if ($gagal > 0) {
                    $pesan_error = "$gagal data gagal diproses (tanggal lahir tidak valid / error database).";
                }
            }
        }

        require __DIR__ . '/../Views/import/index.php';
    }
}
