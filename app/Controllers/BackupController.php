<?php
/**
 * Controller: backup (download dump) & restore database.
 */
class BackupController
{
    public function download(): void
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            die("Akses ditolak!");
        }

        $conn = Database::conn();
        $filename = 'backup_arsip_spri_' . date('Y-m-d_H-i-s') . '.sql';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $this->dumpAllTables($conn);
    }

    private function dumpAllTables(mysqli $conn): void
    {
        $tables = [];
        $result = mysqli_query($conn, 'SHOW TABLES');
        while ($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }

        $return = "-- Backup Database Arsip SPRI\n-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $result = mysqli_query($conn, 'SELECT * FROM ' . $table);
            $num_fields = mysqli_num_fields($result);

            $return .= 'DROP TABLE IF EXISTS ' . $table . ';';
            $row2 = mysqli_fetch_row(mysqli_query($conn, 'SHOW CREATE TABLE ' . $table));
            $return .= "\n\n" . $row2[1] . ";\n\n";

            while ($row = mysqli_fetch_row($result)) {
                $return .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $val = $row[$j] !== null ? addslashes($row[$j]) : '';
                    $return .= '"' . $val . '"';
                    if ($j < ($num_fields - 1)) { $return .= ','; }
                }
                $return .= ");\n";
            }
            $return .= "\n\n\n";
        }
        echo $return;
    }

    public function restoreForm(): void
    {
        Auth::requireAdmin();

        if (isset($_POST['restore'])) {
            Csrf::verify();

            $file = $_FILES['file_sql']['tmp_name'];
            $sql = file_get_contents($file);
            $conn = Database::conn();

            if ($sql === false || trim($sql) === '') {
                Session::flashSet('error', 'File SQL kosong atau tidak bisa dibaca.');
            } else {
                $ok = true;
                if (mysqli_multi_query($conn, $sql)) {
                    do {
                        if ($result = mysqli_store_result($conn)) {
                            mysqli_free_result($result);
                        }
                    } while (mysqli_more_results($conn) && mysqli_next_result($conn));

                    if (mysqli_errno($conn)) $ok = false;
                } else {
                    $ok = false;
                }

                if ($ok) {
                    catatLog(Auth::userNama(), "Melakukan restore database dari file SQL");
                    Session::flashSet('success', 'Database berhasil dipulihkan (restore).');
                } else {
                    Session::flashSet('error', 'Gagal restore: ' . mysqli_error($conn));
                }
            }
            header("Location: backup_restore.php");
            exit;
        }

        require __DIR__ . '/../Views/backup/restore.php';
    }
}
