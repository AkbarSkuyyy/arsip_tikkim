<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: login.php"); exit; }
require 'koneksi.php';

if (isset($_POST['import'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $handle = fopen($file, "r");
    
    // Skip 4 baris pertama (karena berisi judul laporan yang bukan data)
    for ($i = 0; $i < 4; $i++) { fgetcsv($handle); }

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Cek jika kolom nama (index 1) kosong, maka lewati
        if (empty($data[1])) continue; 

        $no = mysqli_real_escape_string($conn, $data[0]);
        $nama = mysqli_real_escape_string($conn, $data[1]);

        // Query sesuaikan dengan tabel permohonan_spri Anda
        mysqli_query($conn, "INSERT INTO permohonan_spri (no_urut, nama_pemohon) VALUES ('$no', '$nama')");
    }
    fclose($handle);
    $pesan = "Data berhasil disinkronkan ke database!";
}
?>

<!DOCTYPE html>
<html>
<body>
    <?php include 'sidebar.php'; ?>
    <div style="margin-left: 250px; padding: 20px;">
        <h3>Import Arsip Bersih</h3>
        <?php if(isset($pesan)) echo "<p style='color:green;'>$pesan</p>"; ?>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="file_csv" accept=".csv">
            <button type="submit" name="import">Proses Impor</button>
        </form>
    </div>
</body>
</html>