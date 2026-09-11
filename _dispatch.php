<?php
/**
 * Front controller stub: setiap file lama di root memuat file ini dengan
 * $page diisi nama route, lalu bootstrap MVC menjalankan Controller terkait.
 * Menjaga URL lama (login.php, dashboard.php, dst) tetap berfungsi tanpa
 * mengubah link/bookmark yang sudah ada.
 */
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/routes.php';

if (!isset($page)) {
    http_response_code(400);
    die('Route tidak ditentukan.');
}

Router::dispatch($page);
