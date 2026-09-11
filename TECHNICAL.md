# Spesifikasi Teknis — Sistem Arsip TIKKIM (Arsip SPRI)

**Dokumen:** Technical Design Document
**Versi:** 1.0
**Stack:** PHP 8.x (procedural, mysqli) · MariaDB 10.4 · Vanilla JS + Chart.js · XAMPP (Apache)

---

## 1. Arsitektur

### 1.1 Pola Saat Ini (Monolitik Prosedural)

Setiap halaman = satu file PHP yang menggabungkan logika + HTML inline.
CSS diulang per file (duplikasi tinggi). Auth diperiksa manual di awal tiap file.

```
Browser → Apache (XAMPP) → *.php (logika + view) → MySQL (mysqli)
```

### 1.2 Pola Target (MVC Ringan)

```
Browser → index.php (front controller)
        → .htaccess (rewrite, basename routing)
        → Router → Controller → Model (PDO) → View (layout terpisah)
```

Target folder:

```
arsip_tikkim/
├── index.php                 # front controller
├── .htaccess                 # rewrite + DirectoryIndex + Options -Indexes
├── app/
│   ├── bootstrap.php         # autoload + global helpers + $conn
│   ├── Core/
│   │   ├── Database.php      # PDO singleton
│   │   ├── Session.php
│   │   ├── Auth.php
│   │   ├── Helpers.php
│   │   └── Router.php
│   ├── Controllers/          # 12 controller (1 per modul)
│   ├── Models/
│   │   └── PermohonanModel.php
│   └── Views/
│       └── layouts/          # head, sidebar, footer, main
├── asset/                    # css, js, img (satu sumber, tanpa duplikasi)
└── ...
```

---

## 2. Peta Modul (File → Tanggung Jawab)

| File                     | Peran                                      | Akses        |
| ------------------------ | ------------------------------------------ | ------------ |
| `index.php`              | Gate sesi → redirect dashboard/login       | publik       |
| `login.php`              | Autentikasi (email/username + verify)      | publik       |
| `register.php`           | Tambah akun (dari panel admin)             | admin        |
| `reset_password.php`     | Reset password                             | login/admin  |
| `logout.php`             | Hapus sesi + catat log                     | login        |
| `koneksi.php`            | Koneksi mysqli + `catatLog()`              | (include)    |
| `dashboard.php`          | Statistik + grafik + 5 terbaru             | login        |
| `dashboard_super.php`    | Dashboard super admin                      | admin        |
| `panel_admin.php`        | Panel kelola user (include)                | admin        |
| `sidebar.php`            | Navigasi user                              | login        |
| `sidebar_super.php`      | Navigasi admin                             | admin        |
| `data_permohonan.php`    | CRUD + filter + cari + pagination + rekap  | login        |
| `tambah_permohonan.php`  | Form input manual                          | login        |
| `tambah.php`             | Form input dokumen SPRI (varian)           | login        |
| `edit_user.php`          | Edit user                                  | admin        |
| `hapus_user.php`         | Hapus user                                 | admin        |
| `hapus.php`              | Hapus permohonan (GET `hapus_id`)          | login        |
| `import_arsip.php`       | Import CSV                                 | login        |
| `export_excel.php`       | Export `.xls` rekap                        | login        |
| `laporan.php`            | Laporan + rekap rentang tanggal            | login        |
| `pengaturan.php`         | Identitas instansi & pejabat               | login/admin  |
| `backup.php`             | Dump SQL (download)                        | admin        |

---

## 3. Skema Database

### `users`
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,        -- hash bcrypt (password_hash)
  `role` varchar(20) DEFAULT 'user',       -- 'admin' | 'user'
  `foto_profile` varchar(255) DEFAULT 'default.png',
  PRIMARY KEY (`id`)
);
```

### `permohonan`
```sql
CREATE TABLE `permohonan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pemohon` varchar(100) NOT NULL,
  `tahun_lahir` varchar(4) NOT NULL,
  `bulan_lahir` varchar(2) NOT NULL,
  `tanggal_lahir` varchar(2) NOT NULL,
  `dokumen` varchar(10) NOT NULL,          -- kode jenis dokumen (mis. '1')
  `jenis_kelamin` varchar(1) NOT NULL,     -- 'L' | 'P'
  `keterangan` varchar(50) NOT NULL,       -- 'Baru' | 'Penggantian Paspor'
  `tanggal_input` date DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

> Catatan: tanggal lahir dipecah 3 kolom varchar → validasi lemah (ada data
> `tanggal_lahir = '80'` / `'87'`). Target: satu kolom `tgl_lahir DATE`.

### `permohonan_spri`
```sql
CREATE TABLE `permohonan_spri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_urut` int(11) DEFAULT NULL,
  `nama_pemohon` varchar(100) DEFAULT NULL,  -- template 12 nama bulan
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `jenis_dokumen` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### `pengaturan`
```sql
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kementerian` varchar(150) NOT NULL,
  `instansi` varchar(150) NOT NULL,
  `seksi` varchar(100) NOT NULL,
  `pejabat_nama` varchar(100) NOT NULL,
  `pejabat_jabatan` varchar(100) NOT NULL,
  `pejabat_nip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);
```

### `logs`
```sql
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `aksi` varchar(255) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
);
```

---

## 4. Keamanan

| Aspek           | Implementasi Saat Ini                          | Target |
| --------------- | ---------------------------------------------- | ------ |
| Password        | `password_hash()` / `password_verify()` (bcrypt) | tetap |
| Input query     | `mysqli_real_escape_string()` interpolasi      | PDO prepared statements |
| CSRF            | belum ada                                      | token di semua form POST |
| Akses admin     | cek `$_SESSION['user_role'] === 'admin'`       | terpusat di `Auth` |
| Sesi            | `$_SESSION` PHP, kunci login di tiap file      | middleware tunggal |
| Directory list  | `.htaccess Options -Indexes`                   | tetap |

---

## 5. Alur Utama

### 5.1 Login
```
POST login → cari user by email|username → password_verify()
  → sukses: set $_SESSION (login,user_id,user_nama,user_jabatan,username,user_role,user_foto)
           + catatLog(login) → redirect dashboard.php
  → gagal : tampilkan pesan error
```

### 5.2 CRUD Permohonan
```
data_permohonan.php?hapus_id=N   → DELETE ... WHERE id=N (id>0)
POST simpan                       → INSERT (pecah tgl_lahir → 3 kolom)
POST update                       → UPDATE ... WHERE id=N (id>0)
GET  bulan/tahun/cari             → filter + rekap + pagination (15/hal)
```

### 5.3 Import CSV
```
upload CSV → lewati header → tiap baris:
  nama kosong → skip (dilewati++)
  normalisasi bulan/tanggal → str_pad 2 digit
  INSERT permohonan → berhasil++ / gagal++
→ hasil: berhasil, gagal, dilewati
```

### 5.4 Export Excel
```
export_excel.php?tgl_mulai=..&tgl_selesai=..
  → header vnd.ms-excel + attachment Laporan_SPRI_YYYYMMDD.xls
  → kop instansi (pengaturan) → tabel rekap → tanda tangan pejabat
```

### 5.5 Laporan
```
laporan.php?cari=..&tgl_mulai=..&tgl_selesai=..
  → WHERE tanggal_input BETWEEN ... → rekap total/L/P/Baru/Penggantian
```

### 5.6 Backup
```
backup.php (role admin) → SHOW TABLES → SHOW CREATE TABLE + SELECT *
  → dump SQL → download backup_arsip_spri_YYYY-MM-DD_HH-II-SS.sql
```

---

## 6. Deploy

### 6.1 `.htaccess` (root)
```apache
DirectoryIndex index.php
Options -Indexes
# target MVC: rewrite semua request ke index.php (basename routing)
```

### 6.2 Konfigurasi Koneksi (`koneksi.php`)
```php
$host="localhost"; $user="root"; $password="";
$db="arsip_tikkim";
$conn = mysqli_connect($host,$user,$password,$db);
```

### 6.3 Akun Default (SQL dump)
| Username | Role   | Email                     | Password (hash bcrypt) |
| -------- | ------ | ------------------------- | ---------------------- |
| admin    | admin  | adminsampit@imigrasi.go.id | `$2y$10$at7ku...`     |
| user     | user   | adminbiasa@imigrasi.go.id | `$2y$10$MqXFm...`     |
| wira     | user   | sampitadmin@imigrasi.go.id | `$2y$10$7KuEA...`     |

---

## 7. Isu Teknis Terbuka & Mitigasi

| Isu                                                        | Mitigasi                                   |
| ---------------------------------------------------------- | ------------------------------------------ |
| `tambahan` — dua form input (`tambah.php`, `tambah_permohonan.php`) duplikasi | konsolidasi ke satu form + controller |
| CSS inline diulang tiap file                               | pindah ke `asset/css/*.css`                |
| `alert()` untuk feedback                                   | ganti toast notification                   |
| Emoji di judul modal/form (ikon emoji 📝 👑)             | hapus, ganti ikon SVG                      |
| Tanggal lahir varchar 3 kolom, data invalid (`80`,`87`)    | migrasi ke `tgl_lahir DATE` + validasi     |
| Log user kosong saat hapus (lihat dump `logs` id 20-27)    | ambil `$_SESSION['user_nama']`, bukan param eksternal |
| Query export/laporan pakai interpolasi GET                 | prepared statements + binding             |
