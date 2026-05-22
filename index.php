<?php
// Memulai sesi untuk memeriksa status login
session_start();

// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
} else {
    // Jika belum login, arahkan ke halaman login
    header("Location: login.php");
    exit;
}
?>