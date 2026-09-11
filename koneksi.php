<?php
$host     = "localhost";
$user     = "root"; // Default user XAMPP
$password = "";     // Default password XAMPP kosong
$db       = "arsip_tikkim";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Tambahkan ini ke koneksi.php
function catatLog($conn, $user, $aksi) {
    // Membersihkan input untuk keamanan
    $user = mysqli_real_escape_string($conn, $user);
    $aksi = mysqli_real_escape_string($conn, $aksi);
    
    $query = "INSERT INTO logs (user, aksi) VALUES ('$user', '$aksi')";
    mysqli_query($conn, $query);
}
?>