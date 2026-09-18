<?php
/**
 * Controller: import data permohonan dari file CSV.
 *
 * CSV (Comma Delimited) format:
 *   header : no,nama,tahun,bulan,tanggal,dokumen,jenis_kelamin,keterangan
 *   baris  : nomor urut, nama pemohon, tahun lahir, bulan lahir, tanggal lahir,
 *            kode dokumen, jenis kelamin (L/P), keterangan
 *
 * `bulan` pada CSV adalah KOMPONEN tanggal lahir (1-12); bulan arsip dipilih
 * terpisah lewat form (bulan_arsip) dan dipakai sebagai tanggal_input.
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

            try {
                $bulan_arsip = archive_integer($_POST['bulan_arsip'] ?? '', 'Bulan arsip', 1, 12);
                $bulan_padded = str_pad((string)$bulan_arsip, 2, '0', STR_PAD_LEFT);
                $tahun_arsip = (int)date('Y');
                $tanggal_input = $tahun_arsip . '-' . $bulan_padded . '-01';

                $tmp = archive_uploaded_file($_FILES['file_csv'] ?? null, 'csv', 10 * 1024 * 1024);
                $handle = fopen($tmp, 'r');
                if (!$handle) {
                    throw new RuntimeException('Gagal membuka berkas CSV.');
                }

                try {
                    $rows = $this->parseCsv($handle, $bulan_padded);
                } finally {
                    fclose($handle);
                }

                if (count($rows) === 0) {
                    throw new InvalidArgumentException('Tidak ada baris valid di berkas CSV.');
                }

                $berhasil = 0;
                $gagal = 0;

                Permohonan::withArchiveTransaction(function () use ($rows, $tanggal_input, &$berhasil, &$gagal) {
                    foreach ($rows as $row) {
                        $row['tanggal_input'] = $tanggal_input;
                        if (Permohonan::create($row)) {
                            $berhasil++;
                        } else {
                            $gagal++;
                        }
                    }
                });

                $nama_bulan = date('F', mktime(0, 0, 0, $bulan_arsip, 10));
                $pesan = '<strong>Berhasil!</strong> ' . $berhasil . ' data masuk ke bulan ' . $nama_bulan . '.';
                if ($gagal > 0) {
                    $pesan_error = $gagal . ' data gagal disimpan.';
                }
            } catch (InvalidArgumentException $e) {
                $pesan_error = $e->getMessage();
            } catch (RuntimeException $e) {
                $pesan_error = 'Gagal memproses impor: ' . $e->getMessage();
            }
        }

        require __DIR__ . '/../Views/import/index.php';
    }

    /**
     * Parse & validate a CSV stream into archive rows. Throws InvalidArgumentException
     * on any malformed header, field, or row so a partial import never commits.
     *
     * @param resource $fp   open stream (header already at position 0)
     * @param string   $bulan bulan arsip (01-12) — validated here
     * @return array<int,array<string,mixed>> rows with tgl_lahir + loker_id=0/rak='' (no invented location)
     */
    public function parseCsv($fp, string $bulan): array
    {
        if (!preg_match('/\A(0[1-9]|1[0-2])\z/', $bulan)) {
            throw new InvalidArgumentException('Bulan arsip tidak valid.');
        }

        $header = fgetcsv($fp, 0, ',');
        $expected = ['no', 'nama', 'tahun', 'bulan', 'tanggal', 'dokumen', 'jenis_kelamin', 'keterangan'];
        if (!is_array($header) || array_map('strtolower', array_map('trim', $header)) !== $expected) {
            throw new InvalidArgumentException('Header CSV tidak sesuai format yang diharapkan.');
        }

        $codes = array_map(fn($row) => (string)$row['kode'], MasterDokumen::allActive());
        $rows = [];

        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if (count($data) === 1 && trim($data[0] ?? '') === '') {
                continue; // baris kosong di tengah file
            }
            if (!is_array($data) || count($data) !== 8) {
                throw new InvalidArgumentException('Jumlah kolom pada suatu baris tidak sesuai (harus 8).');
            }
            $nama = archive_text($data[1] ?? '', 'Nama', 255, true);
            $tahun = $data[2] ?? '';
            $bulanLahir = $data[3] ?? '';
            $tanggalLahir = $data[4] ?? '';
            $dokumen = trim((string)($data[5] ?? ''));
            $jk = strtoupper(trim((string)($data[6] ?? '')));
            $keterangan = trim((string)($data[7] ?? ''));

            $tahun = preg_replace('/\.0$/', '', trim((string)$tahun));
            $bulanLahir = preg_replace('/\.0$/', '', trim((string)$bulanLahir));
            $tanggalLahir = preg_replace('/\.0$/', '', trim((string)$tanggalLahir));

            if (!preg_match('/\A[0-9]{4}\z/', $tahun)) {
                throw new InvalidArgumentException('Tahun lahir tidak valid pada baris dengan nama "' . $nama . '".');
            }
            $bulanLahir = str_pad($bulanLahir, 2, '0', STR_PAD_LEFT);
            $tanggalLahir = str_pad($tanggalLahir, 2, '0', STR_PAD_LEFT);
            $tgl_lahir = norm_tgl_lahir($tahun . '-' . $bulanLahir . '-' . $tanggalLahir);
            if ($tgl_lahir === null) {
                throw new InvalidArgumentException('Tanggal lahir tidak valid pada baris dengan nama "' . $nama . '".');
            }
            if (!in_array($jk, ['L', 'P'], true)) {
                throw new InvalidArgumentException('Jenis kelamin harus L atau P pada baris dengan nama "' . $nama . '".');
            }
            if (!in_array($dokumen, $codes, true)) {
                throw new InvalidArgumentException('Jenis dokumen tidak aktif atau tidak dikenal pada baris dengan nama "' . $nama . '".');
            }
            if (!in_array($keterangan, array_column(MasterKeterangan::allActive(), 'nama'), true)) {
                throw new InvalidArgumentException('Keterangan tidak aktif atau tidak dikenal pada baris dengan nama "' . $nama . '".');
            }

            $rows[] = [
                'nama_pemohon' => $nama,
                'no_permohonan' => '',
                'tgl_lahir' => $tgl_lahir,
                'dokumen' => $dokumen,
                'jenis_kelamin' => $jk,
                'keterangan' => $keterangan,
                'loker_id' => 0,
                'rak' => '',
                'kotak' => '',
            ];
        }

        return $rows;
    }
}
