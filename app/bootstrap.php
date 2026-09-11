<?php
/**
 * Bootstrap aplikasi: autoload class, mulai session, sediakan helper global.
 * Di-require oleh index.php (front controller) dan semua entry-point lama
 * (dipertahankan sementara untuk kompatibilitas selama migrasi).
 */

date_default_timezone_set('Asia/Jakarta');

spl_autoload_register(function (string $class) {
    $dirs = [
        __DIR__ . '/Core/',
        __DIR__ . '/Models/',
        __DIR__ . '/Controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once __DIR__ . '/Core/Helpers.php'; // fungsi global (bukan class, tidak lewat autoload)

Session::start();

// Kompatibilitas: variabel $conn masih dipakai di beberapa Views yang belum
// dipindah sepenuhnya ke Model. Baru dihapus setelah semua View diverifikasi.
$conn = Database::conn();
