<?php
/**
 * Model tabel master_keterangan. Setara query di master_data.php, dropdown
 * status keterangan di tambah_permohonan.php.
 */
class MasterKeterangan
{
    public static function allActive(): array
    {
        $r = Database::raw("SELECT * FROM master_keterangan WHERE aktif=1 ORDER BY nama ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function all(): array
    {
        $r = Database::raw("SELECT * FROM master_keterangan ORDER BY aktif DESC, nama ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function upsert(string $nama): bool
    {
        return Database::exec(
            "INSERT INTO master_keterangan (nama) VALUES (?) ON DUPLICATE KEY UPDATE nama=VALUES(nama), aktif=1",
            's', [$nama]
        );
    }

    public static function deactivate(int $id): bool
    {
        return Database::exec("UPDATE master_keterangan SET aktif=0 WHERE id=?", 'i', [$id]);
    }
}
