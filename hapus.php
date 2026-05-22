<?php
require 'koneksi.php';

// Memeriksa apakah ada parameter 'id' yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // (Opsional) Ambil nama untuk log agar lebih detail
    $query_get = mysqli_query($conn, "SELECT nama_pemohon FROM permohonan WHERE id = '$id'");
    $data = mysqli_fetch_assoc($query_get);
    $nama_hapus = $data['nama_pemohon'] ?? 'ID: '.$id;

    // Menjalankan query hapus
    $query = "DELETE FROM permohonan WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        // TAMBAHKAN BARIS INI:
        catatLog($conn, $_SESSION['user_nama'], "Menghapus data permohonan: " . $nama_hapus);
        
        header("Location: data_permohonan.php");
        exit;
    }
}
?>