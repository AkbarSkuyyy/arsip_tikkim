<?php
session_start();
require 'koneksi.php';

// KUNCI KEAMANAN: Hanya Admin yang boleh akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Akses ditolak!");
}

// Nama file backup
$filename = 'backup_arsip_spri_' . date('Y-m-d_H-i-s') . '.sql';

// Header agar browser men-download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Fungsi untuk membuat dump database
function backup_tables($conn, $tables = '*') {
    if($tables == '*') {
        $tables = array();
        $result = mysqli_query($conn, 'SHOW TABLES');
        while($row = mysqli_fetch_row($result)) { $tables[] = $row[0]; }
    } else {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    $return = "-- Backup Database Arsip SPRI\n-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

    foreach($tables as $table) {
        $result = mysqli_query($conn, 'SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);

        $return .= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($conn, 'SHOW CREATE TABLE '.$table));
        $return .= "\n\n".$row2[1].";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while($row = mysqli_fetch_row($result)) {
                $return .= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    if (isset($row[$j])) { $return .= '"'.$row[$j].'"' ; } else { $return .= '""'; }
                    if ($j<($num_fields-1)) { $return .= ','; }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }
    echo $return;
}

// Jalankan fungsi
backup_tables($conn);
?>