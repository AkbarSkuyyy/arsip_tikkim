# PRD — Sistem Arsip TIKKIM (Arsip SPRI)

**Produk:** Sistem Arsip TIKKIM
**Instansi:** Kantor Imigrasi Kelas II TPI Sampit — Seksi TIKKIM
**Versi Dokumen:** 1.0
**Status:** Draft

---

## 1. Ringkasan Produk

Aplikasi web internal untuk mencatat, merekap, mencari, dan mencetak arsip
**SPRI** (Surat Permohonan penerbitan dokumen perjalanan/paspor). Menggantikan
pencatatan manual (buku/Excel) dengan database terpusat yang bisa direkap
otomatis per bulan dan diekspor.

---

## 2. Latar Belakang & Masalah

Seksi TIKKIM memproses permohonan paspor harian dengan dua status: **Baru**
dan **Penggantian HB** (habis berlaku).

**Masalah saat ini:**

1. Pencatatan manual — rawan salah hitung rekap.
2. Pencarian data lama & sulit (arsip fisik).
3. Laporan bulanan dibuat ulang dari nol setiap periode.
4. Tidak ada jejak audit (siapa mengubah apa, kapan).
5. Tidak ada backup data terpusat.

---

## 3. Tujuan & Ukuran Keberhasilan

| Tujuan                                   | Ukuran Keberhasilan                                 |
| ---------------------------------------- | --------------------------------------------------- |
| Rekap otomatis & akurat                  | Rekap bulanan dihasilkan < 1 menit, tanpa hitung manual |
| Pencarian cepat                          | Cari nama/filter bulan tampil < 2 detik             |
| Impor data massal                        | Import 1000 baris CSV < 10 detik, tanpa error baris valid |
| Laporan siap cetak                       | Laporan periode terunduh/tercetak sekali klik       |
| Jejak audit                              | Semua aksi kunci tercatat di log                    |

---

## 4. Persona / Pengguna

| Role   | Deskripsi                              | Kebutuhan Utama                          |
| ------ | -------------------------------------- | ---------------------------------------- |
| Admin  | Kepala/penanggung jawab seksi          | Kelola user, lihat semua data & rekap, backup |
| User   | Staff arsip / operator TIKKIM          | Input, edit, cari, import, cetak laporan |
| Super Admin | Pengelola sistem                 | Kelola user & role, pengaturan instansi |

---

## 5. Lingkup (Scope)

### Di dalam lingkup

- Autentikasi & otorisasi (role admin/user).
- CRUD data permohonan SPRI.
- Filter bulan/tahun, pencarian nama, pagination.
- Rekap otomatis (total, Baru, Penggantian, L/P).
- Import CSV & export Excel.
- Laporan periode (rentang tanggal) siap cetak.
- Pengaturan identitas instansi (kop laporan).
- Backup database & log aktivitas.

### Di luar lingkup (versi ini)

- Integrasi sistem paspor nasional (API Imigrasi).
- Pembayaran / PNBP.
- Aplikasi mobile.
- Multi-kantor (hanya Sampit).

---

## 6. Kebutuhan Fungsional (FR)

### FR-1 Autentikasi

- **FR-1.1** Login dengan email atau username + password.
  - *Acceptance:* password diverifikasi `password_verify`; gagal → pesan salah; sukses → redirect dashboard; log tercatat.
- **FR-1.2** Registrasi user (oleh admin).
- **FR-1.3** Reset password.
- **FR-1.4** Logout menghapus sesi & mencatat log.

### FR-2 Manajemen User (Admin)

- **FR-2.1** Tambah user (nama, username, jabatan, email, password, role, foto).
- **FR-2.2** Edit user.
- **FR-2.3** Hapus user.
- **FR-2.4** Ubah role (`admin` ↔ `user`).

### FR-3 Dashboard

- **FR-3.1** Kartu statistik: total data, total Baru, total Penggantian.
- **FR-3.2** Grafik jenis kelamin (L vs P) — Chart.js.
- **FR-3.3** Tabel 5 permohonan terbaru.

### FR-4 Data Permohonan

- **FR-4.1** Tambah data (popup): nama, tanggal lahir, dokumen, jenis kelamin, keterangan, tanggal input.
- **FR-4.2** Edit data (popup), hanya 1 ID valid.
- **FR-4.3** Hapus data dengan konfirmasi.
- **FR-4.4** Filter bulan + tahun.
- **FR-4.5** Pencarian nama (`LIKE`).
- **FR-4.6** Pagination 15 baris/halaman.
- **FR-4.7** Rekap bulanan ringkas (total, baru, penggantian, L/P).

### FR-5 Import Arsip (CSV)

- **FR-5.1** Upload CSV; baris header dilewati.
- **FR-5.2** Baris nama kosong dibuang (dihitung sebagai dilewati).
- **FR-5.3** Normalisasi bulan/tanggal jadi 2 digit.
- **FR-5.4** Tentukan bulan arsip saat import.
- **FR-5.5** Laporan hasil: berhasil / gagal / dilewati.

### FR-6 Export

- **FR-6.1** Export rekap ke `.xls`.
- **FR-6.2** Nama file `Laporan_SPRI_YYYYMMDD.xls`.

### FR-7 Laporan

- **FR-7.1** Filter rentang tanggal + nama.
- **FR-7.2** Tampilkan rekap total, L/P, Baru/Penggantian.
- **FR-7.3** Tampilan siap cetak (kop instansi).

### FR-8 Pengaturan

- **FR-8.1** Ubah kementerian, instansi, seksi, pejabat (nama/jabatan/NIP).

### FR-9 Backup & Log

- **FR-9.1** Backup database sekali klik.
- **FR-9.2** Catat log aksi kunci (login, tambah, hapus, ubah role).

---

## 7. Kebutuhan Non-Fungsional (NFR)

| Kategori       | Kebutuhan                                                       |
| -------------- | --------------------------------------------------------------- |
| Keamanan       | Prepared statements utk semua mutasi; CSRF di semua form POST; `password_hash` min 8 char |
| Performa       | Halaman daftar render < 2 detik utk 10k baris                   |
| Kompatibilitas | PHP 8.x + MariaDB 10.4 (XAMPP); browser modern (Chrome/Edge/Firefox) |
| Usability      | UI responsif, font Inter/Outfit, tema Imigrasi, tanpa emoji, ikon SVG |
| Maintainability| Struktur modular (arah MVC); path relatif utk deploy subfolder cPanel |
| Deploy         | `.htaccess` `DirectoryIndex index.php` + `Options -Indexes`     |
| Bahasa         | Antarmuka Indonesia                                             |

---

## 8. Model Data (Ringkas)

| Tabel            | Kolom Kunci                                                  |
| ---------------- | ------------------------------------------------------------ |
| `users`          | id, nama, username, jabatan, email, password (hash), role, foto_profile |
| `permohonan`     | id, nama_pemohon, tahun/bulan/tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input |
| `permohonan_spri`| id, no_urut, nama_pemohon (template 12 bulan)                |
| `pengaturan`     | id, kementerian, instansi, seksi, pejabat_nama/jabatan/nip   |
| `logs`           | id, user, aksi, waktu                                        |

---

## 9. Prioritas (MoSCoW)

**Must have:**
- FR-1, FR-4 (CRUD + filter + cari + pagination), FR-5 (import), FR-7 (laporan).

**Should have:**
- FR-3 (dashboard statistik + grafik), FR-6 (export), FR-2 (kelola user), FR-8 (pengaturan).

**Could have:**
- FR-9 (backup & log), notifikasi toast (ganti `alert()`).

**Won't have (versi ini):**
- Scan dokumen, REST API, PDF resmi, role granular — masuk roadmap berikutnya.

---

## 10. Asumsi & Kendala

- Server dev XAMPP Windows; deploy target cPanel (subfolder).
- Tidak ada koneksi ke sistem Imigrasi pusat (offline internal).
- Data SPRI bersifat internal, akses dibatasi user terdaftar.

---

## 11. Milestone Rilis

| Milestone | Isi                                                    |
| --------- | ------------------------------------------------------ |
| M1        | Auth + CRUD + filter/cari + rekap (inti)               |
| M2        | Import CSV + export + laporan cetak                    |
| M3        | Dashboard + kelola user + pengaturan                   |
| M4        | Hardening: PDO/prepared, CSRF, validasi, toast, SVG    |
| M5        | Refactor MVC + deploy cPanel                           |

---

## 12. Kriteria Rilis (DoD)

- [ ] Semua FR "Must have" lolos acceptance.
- [ ] Tidak ada query mutasi tanpa prepared statement.
- [ ] CSRF aktif di semua form POST.
- [ ] Akun default `admin` & `user` login normal (password ter-hash).
- [ ] Import CSV 1000 baris valid tanpa error.
- [ ] Laporan bulanan akurat (cocok hitung manual sampel).
- [ ] Backup & restore teruji.
