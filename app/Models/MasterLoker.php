<?php
/**
 * Model tabel master_loker (lokasi fisik arsip: 11 loker). Setara pola
 * MasterDokumen / MasterKeterangan. Lokasi arsip = Loker -> Rak -> Urutan.
 */
class MasterLoker
{
    public static function allActive(): array
    {
        $r = Database::raw("SELECT * FROM master_loker WHERE aktif=1 ORDER BY CAST(nomor AS UNSIGNED) ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function all(): array
    {
        $r = Database::raw("SELECT * FROM master_loker ORDER BY aktif DESC, CAST(nomor AS UNSIGNED) ASC");
        $rows = [];
        while ($row = mysqli_fetch_assoc($r)) $rows[] = $row;
        return $rows;
    }

    public static function upsert(string $nomor, string $nama): bool
    {
        return Database::exec(
            "INSERT INTO master_loker (nomor, nama) VALUES (?, ?) ON DUPLICATE KEY UPDATE nama=VALUES(nama), aktif=1",
            'ss', [$nomor, $nama]
        );
    }

    public static function deactivate(int $id): bool
    {
        return Database::exec("UPDATE master_loker SET aktif=0 WHERE id=?", 'i', [$id]);
    }
}
