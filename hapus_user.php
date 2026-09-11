<?php
session_start();
require 'koneksi.php';

// Keamanan: Hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil nama user untuk log
    $query_cek = mysqli_query($conn, "SELECT username FROM users WHERE id = '$id'");
    $user_data = mysqli_fetch_assoc($query_cek);
    
    if ($user_data) {
        $username = $user_data['username'];
        
        // Hapus user
        mysqli_query($conn, "DELETE FROM users WHERE id = '$id'");
        
        // Catat log
        catatLog($conn, $_SESSION['user_nama'], "Menghapus akun user: " . $username);
    }
}

header("Location: dashboard_super.php");
exit;
?>