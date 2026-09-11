<?php
/**
 * Front controller — satu-satunya pintu masuk aplikasi.
 *
 * URL bersih (mis. /arsip_tikkim/dashboard) ATAU ?page=dashboard dipetakan
 * ke route di app/routes.php lalu di-dispatch ke Controller terkait.
 *
 * File .php lama di root (login.php, dashboard.php, dst.) sudah DIHAPUS.
 * .htaccess mengalihkan pola <nama>.php ke sini (page=<nama>) supaya
 * link/bookmark lama tetap berfungsi tanpa file stub di root.
 */
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/routes.php';

$page = isset($_GET['page']) ? trim((string)$_GET['page']) : '';

if ($page === '') {
    // Kunjungan ke /arsip_tikkim/ tanpa page: arahkan ke dashboard/login.
    $page = Auth::check() ? 'dashboard' : 'login';
}

// Untuk penyorotan menu aktif di sidebar (membandingkan 'dashboard.php').
$GLOBALS['current_page'] = $page . '.php';
$GLOBALS['current_jenis'] = isset($_GET['jenis']) ? trim((string)$_GET['jenis']) : '';

if (!Router::has($page)) {
    http_response_code(404);
    echo 'Halaman tidak ditemukan: ' . htmlspecialchars($page);
    exit;
}

Router::dispatch($page);
