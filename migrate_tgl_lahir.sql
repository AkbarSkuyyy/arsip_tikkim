-- Migrasi: pecah 3 kolom tanggal lahir (tahun/bulan/tanggal) -> 1 kolom DATE
-- Jalankan urut. Step 1 & 2 aman (tidak destruktif). Step 3 hapus kolom lama.

-- STEP 1: tambah kolom baru
ALTER TABLE `permohonan` ADD COLUMN `tgl_lahir` DATE NULL AFTER `nama_pemohon`;

-- STEP 2: backfill dari 3 kolom lama. Tanggal tidak valid -> NULL (otomatis via STR_TO_DATE).
UPDATE `permohonan`
SET `tgl_lahir` = STR_TO_DATE(
    CONCAT(`tahun_lahir`, '-', LPAD(`bulan_lahir`, 2, '0'), '-', LPAD(`tanggal_lahir`, 2, '0')),
    '%Y-%m-%d'
);

-- Cek baris yang menjadi NULL (tanggal tidak valid, mis. tanggal '80'/'87'):
-- SELECT id, nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir FROM permohonan WHERE tgl_lahir IS NULL;

-- STEP 3: hapus 3 kolom lama (jalankan SETELAH semua kode PHP memakai tgl_lahir)
ALTER TABLE `permohonan`
    DROP COLUMN `tahun_lahir`,
    DROP COLUMN `bulan_lahir`,
    DROP COLUMN `tanggal_lahir`;
