<?php
session_start();
require 'koneksi.php';

// Keamanan: Hanya admin yang bisa akses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Hapus hanya lewat POST (bukan GET) + CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard_super.php");
    exit;
}

csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // Ambil username untuk log
    $query_cek = mysqli_query($conn, "SELECT username FROM users WHERE id = '$id'");
    $user_data = mysqli_fetch_assoc($query_cek);

    if ($user_data) {
        $username = $user_data['username'];
        db_exec("DELETE FROM users WHERE id = ?", 'i', [$id]);
        catatLog($conn, $_SESSION['user_nama'], "Menghapus akun user: " . $username);
    }
}

header("Location: dashboard_super.php");
exit;
