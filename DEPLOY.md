# Panduan Deploy / Hosting — SI RAPI

Panduan memindahkan SI RAPI dari XAMPP (lokal) ke hosting produksi (cPanel/shared hosting).

---

## 1. Prasyarat Server

- PHP 8.x (minimal 8.0)
- MySQL / MariaDB 10.x
- Apache dengan mod_rewrite aktif (untuk `.htaccess`)

---

## 2. Langkah Deploy

### 2.1 Siapkan Database di Hosting

1. Di cPanel, buat database baru + user database (via "MySQL Databases").
2. Catat 4 nilai:
   - Host (biasanya `localhost`)
   - Nama database (sering di-prefix username, mis. `user_sirapi`)
   - User database
   - Password
3. Import struktur + data via phpMyAdmin:
   - Buka phpMyAdmin → pilih database baru → tab "Import".
   - Upload file dump `.sql` (hasil Backup DB, atau `arsip_tikkim (2).sql`).

### 2.2 Atur Kredensial

Edit `app/config.php`:

```php
return [
    'db' => [
        'host'     => 'localhost',
        'user'     => 'USER_DATABASE_ANDA',
        'password' => 'PASSWORD_ANDA',
        'name'     => 'NAMA_DATABASE_ANDA',
    ],
];
```

### 2.3 Upload File

Upload seluruh isi project ke web root (mis. `public_html/`), ATAU ke subfolder
jika diinginkan (mis. `public_html/sirapi/`).

**PENTING:** pastikan file `.htaccess` di root dan `app/.htaccess` ikut ter-upload
(keduanya tersembunyi; aktifkan "show hidden files" di file manager cPanel).

### 2.4 Verifikasi

- Buka `https://domain-anda/` → harus muncul halaman login SI RAPI.
- Login dengan akun yang sudah ada di database.

---

## 3. Keamanan yang Sudah Terpasang

- `app/.htaccess` → menolak akses langsung ke `app/` (Controllers/Models/Core/Views/config).
- `.htaccess` root → `Options -Indexes` (tidak ada listing direktori) + rewrite ke front controller.
- CSRF, prepared statements, rate limiting login, session timeout sudah aktif.

---

## 4. Catatan Penting

- **Jangan** commit `app/config.php` (sudah di `.gitignore`) jika berisi kredensial asli.
- **Jangan** commit file `*.sql` (backup database) ke repo publik.
- Backup database berkala lewat menu "Backup Database" di panel superadmin.

---

## 5. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Error "Koneksi gagal" | Cek kredensial di `app/config.php` (nama db/user/password) |
| 404 saat buka halaman | Pastikan `mod_rewrite` aktif + `.htaccess` ter-upload |
| Blank page | Cek PHP error log; pastikan PHP ≥ 8.0 |
| Listing direktori muncul | Pastikan `.htaccess` `Options -Indexes` ada |
