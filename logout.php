<?php
// Mulai sesi untuk mendapatkan akses ke data sesi saat ini
session_start();

// Kosongkan semua variabel sesi
$_SESSION = [];

// Hancurkan sesi secara keseluruhan
session_destroy();

// Arahkan kembali pengguna ke halaman login
header("Location: login.php");
exit;
?>