<?php
/**
 * Konfigurasi database & aplikasi.
 *
 * SAAT DEPLOY KE HOSTING (cPanel/shared hosting):
 *  - Ubah DB_HOST, DB_USER, DB_PASS, DB_NAME sesuai kredensial database
 *    di panel hosting (biasanya DB_NAME di-prefix username cPanel,
 *    mis. `user_sirapi`).
 *  - Jangan commit file ini ke repo publik jika berisi kredensial asli.
 *
 * Lokal (XAMPP) default: root / tanpa password / db arsip_tikkim.
 */
return [
    'db' => [
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => '',
        'name'     => 'arsip_tikkim',
    ],
];
