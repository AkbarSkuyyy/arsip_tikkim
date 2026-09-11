<?php
/**
 * Model tabel pengaturan (identitas instansi + pejabat penandatangan).
 * Setara query di pengaturan.php, export_excel.php.
 */
class Pengaturan
{
    public static function get(): ?array
    {
        $r = Database::raw("SELECT * FROM pengaturan WHERE id = 1");
        $row = $r ? mysqli_fetch_assoc($r) : null;
        return $row ?: null;
    }

    public static function update(array $data): bool
    {
        return Database::exec(
            "UPDATE pengaturan SET kementerian=?, instansi=?, seksi=?, pejabat_nama=?, pejabat_jabatan=?, pejabat_nip=? WHERE id=1",
            'ssssss',
            [$data['kementerian'], $data['instansi'], $data['seksi'], $data['pejabat_nama'], $data['pejabat_jabatan'], $data['pejabat_nip']]
        );
    }
}
