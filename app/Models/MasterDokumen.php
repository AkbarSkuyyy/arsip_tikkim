<?php
/**
 * Model tabel master_dokumen. Setara query di master_data.php, dropdown
 * jenis dokumen di tambah_permohonan.php.
 */
class MasterDokumen
{
    public static function allActive(): array
    {
        $r = Database::raw("SELECT * FROM master_dokumen WHERE aktif=1 ORDER BY kode ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function all(): array
    {
        $r = Database::raw("SELECT * FROM master_dokumen ORDER BY aktif DESC, kode ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function upsert(string $kode, string $nama): bool
    {
        return Database::exec(
            "INSERT INTO master_dokumen (kode, nama) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama=VALUES(nama), aktif=1",
            'ss', [$kode, $nama]
        );
    }

    public static function deactivate(int $id): bool
    {
        return Database::exec("UPDATE master_dokumen SET aktif=0 WHERE id=?", 'i', [$id]);
    }
}
