# PRD — SI RAPI (Sistem Aplikasi Rekam Arsip Permohonan Imigrasi)

**Produk:** SI RAPI
**Instansi:** Kantor Imigrasi Kelas II TPI Kotawaringin Timur — Seksi TIKKIM
**Versi Dokumen:** 2.0
**Status:** Aktif

---

## 1. Ringkasan Produk

Aplikasi web internal untuk mencatat, merekap, mencari, dan mencetak arsip
**SPRI** (Surat Perjalanan Republik Indonesia). Menggantikan pencatatan manual
(buku/Excel) dengan database terpusat yang bisa direkap otomatis per bulan dan
diekspor.

**Fokus tunggal:** SPRI saja. Jenis dokumen lain (Izin Tinggal, Lainnya,
Paspor Biasa) **tidak digunakan** dan dinonaktifkan.

Nama aplikasi: **SI RAPI** — Sistem Aplikasi Rekam Arsip Permohonan Imigrasi.

---

## 2. Latar Belakang & Masalah

Seksi TIKKIM memproses permohonan SPRI harian dengan status **Baru** dan
**Penggantian Paspor**.

**Masalah yang dipecahkan:**

1. Pencatatan manual — rawan salah hitung rekap.
2. Pencarian data lama sulit (arsip fisik).
3. Laporan bulanan dibuat ulang dari nol setiap periode.
4. Tidak ada jejak audit (siapa mengubah apa, kapan).
5. Tidak ada backup data terpusat.

---

## 3. Tujuan & Ukuran Keberhasilan

| Tujuan                   | Ukuran Keberhasilan                                   |
| ------------------------ | ----------------------------------------------------- |
| Rekap otomatis & akurat  | Rekap bulanan dihasilkan < 1 menit, tanpa hitung manual |
| Pencarian cepat          | Cari nama/filter bulan tampil < 2 detik               |
| Impor data massal        | Import 1000 baris CSV < 10 detik, tanpa error baris valid |
| Laporan siap cetak       | Laporan periode terunduh/tercetak sekali klik         |
| Lokasi arsip otomatis    | Slot Loker-Rak-Urutan terisi otomatis tanpa input manual |
| Jejak audit              | Semua aksi kunci tercatat di log                      |

---

## 4. Persona / Pengguna (4 Role)

| Role       | Deskripsi                          | Hak Akses |
| ---------- | ---------------------------------- | --------- |
| Superadmin | Pengelola sistem penuh             | Semua data + kelola akun, master data, pengaturan, backup, audit trail |
| Admin      | Pengumpul data (semua jenis)       | Lihat/kelola semua data, import, laporan — tanpa menu sistem |
| Petugas    | Staf arsip (fokus 1 jenis dokumen) | Input/edit/hapus jenis dokumennya sendiri |
| Viewer     | Kepala kantor (read-only)          | Lihat semua data + export laporan — tanpa hak ubah |

> Karena fokus hanya SPRI, praktis Petugas = Petugas SPRI. Struktur 4 role
> tetap dipertahankan untuk fleksibilitas organisasi.

---

## 5. Lingkup (Scope)

### Di dalam lingkup

- Autentikasi & otorisasi 4 role (superadmin/admin/petugas/viewer).
- CRUD data permohonan SPRI.
- Nomor permohonan.
- Filter bulan/tahun/loker, pencarian nama, pagination.
- Rekap otomatis (total, Baru, Penggantian, L/P), realtime via polling.
- Lokasi fisik arsip (Loker → Rak → Urutan) **auto-assign** + Peta Loker + kode seri.
- Import CSV & export Excel.
- Laporan periode siap cetak.
- Pengaturan identitas instansi (kop laporan).
- Backup & restore database, log aktivitas, audit trail.
- Keamanan: rate limiting login + session timeout idle 30 menit.

### Di luar lingkup

- Jenis dokumen selain SPRI (Izin Tinggal, Lainnya, Paspor Biasa) — **nonaktif**.
- Integrasi sistem paspor nasional (API Imigrasi).
- Pembayaran / PNBP.
- Aplikasi mobile.
- Multi-kantor.

---

## 6. Kebutuhan Fungsional (FR)

### FR-1 Autentikasi & Keamanan

- **FR-1.1** Login email/username + password (`password_verify`).
- **FR-1.2** Rate limiting: 5 gagal → kunci 15 menit (per IP).
- **FR-1.3** Session timeout idle 30 menit → logout otomatis.
- **FR-1.4** Logout + log aktivitas.

### FR-2 Manajemen User (Superadmin)

- **FR-2.1** Kelola Akun: daftar akun + info (nama, username, jabatan, email, role, akses dokumen).
- **FR-2.2** Tambah akun: 4 pilihan role (superadmin/admin/petugas/viewer).
- **FR-2.3** Edit role & akses dokumen.
- **FR-2.4** Reset password, hapus akun.
- **FR-2.5** Safeguard: superadmin terakhir tidak bisa dihapus/diturunkan.

### FR-3 Dashboard

- **FR-3.1** Kartu statistik: total data, Baru, Penggantian, rasio L/P.
- **FR-3.2** Grafik Chart.js: tren bulanan (12 bulan), jenis kelamin, status.
- **FR-3.3** Tabel permohonan terbaru.
- **FR-3.4** Dashboard super: proporsi dokumen, keterangan, komposisi role, log aktivitas.

### FR-4 Data Permohonan SPRI

- **FR-4.1** Tambah data (popup): nama, nomor permohonan, tanggal lahir, jenis kelamin, keterangan, tanggal input.
- **FR-4.2** Edit data (popup); field lokasi manual di Edit untuk koreksi data lama.
- **FR-4.3** Hapus data dengan konfirmasi.
- **FR-4.4** Filter bulan + tahun + loker.
- **FR-4.5** Pencarian nama (`LIKE`).
- **FR-4.6** Pagination 15 baris/halaman.
- **FR-4.7** Rekap bulanan (total, Baru, Penggantian, L/P), realtime polling AJAX 10 detik.
- **FR-4.8** Kolom LOKASI + Peta Loker (grid 11 loker, kode seri `L-R-U`).
- **FR-4.9** Auto-assign lokasi (Loker → Rak → Urutan) sekuensial untuk data baru.

### FR-5 Import Arsip (CSV)

- **FR-5.1** Upload CSV, header dilewati, baris kosong dibuang.
- **FR-5.2** Normalisasi tanggal, laporan hasil (berhasil/gagal/dilewati).

### FR-6 Export

- **FR-6.1** Export rekap `.xls`, nama `Laporan_SPRI_YYYYMMDD.xls`.

### FR-7 Laporan

- **FR-7.1** Filter rentang tanggal + nama, rekap total L/P Baru/Penggantian, siap cetak.

### FR-8 Pengaturan (Superadmin)

- **FR-8.1** Identitas instansi (kementerian, instansi, seksi, pejabat).

### FR-9 Backup & Audit

- **FR-9.1** Backup sekali klik + restore `.sql`.
- **FR-9.2** Audit trail (log lengkap + filter + pagination).

---

## 7. Kebutuhan Non-Fungsional (NFR)

| Kategori        | Kebutuhan                                                       |
| --------------- | --------------------------------------------------------------- |
| Keamanan        | Prepared statements semua mutasi; CSRF semua form POST; `password_hash` min 8 char; rate limiting; session timeout |
| Performa        | Halaman daftar render < 2 detik utk 10k baris                   |
| Kompatibilitas  | PHP 8.x + MariaDB 10.4 (XAMPP); browser modern                  |
| Usability       | UI responsif, font Inter/Outfit, tema Imigrasi, tanpa emoji, ikon SVG |
| Maintainability | Struktur MVC (front controller + Router); path relatif cPanel    |
| Deploy          | `.htaccess` `DirectoryIndex index.php` + `Options -Indexes`     |
| Bahasa          | Antarmuka Indonesia                                             |

---

## 8. Model Data (Ringkas)

| Tabel             | Kolom Kunci |
| ----------------- | ----------- |
| `users`           | id, nama, username, jabatan, email, password (hash), role (superadmin/admin/user/viewer), foto_profile, akses_dokumen |
| `permohonan`      | id, nama_pemohon, no_permohonan, tgl_lahir (DATE), dokumen, jenis_kelamin, keterangan, tanggal_input, loker_id, rak, kotak |
| `master_loker`    | id, nomor, nama, aktif (11 loker fisik) |
| `master_dokumen`  | id, kode, nama, aktif (hanya SPRI aktif) |
| `master_keterangan` | id, nama, aktif (Baru / Penggantian Paspor / dll) |
| `pengaturan`      | id, kementerian, instansi, seksi, pejabat_nama/jabatan/nip |
| `logs`            | id, user, aksi, waktu |
| `login_throttle`  | id, ip, attempts, last_attempt, locked_until (rate limiting) |

**Kapasitas loker fisik (auto-assign):**
- Loker 1-8 (unit besar): 5 rak (A-E) × 200 urutan.
- Loker 9-11 (unit "3 setir"/roll o'pack): 4 rak (A-D) × 100 urutan.

---

## 9. Prioritas (MoSCoW)

**Must have:** FR-1, FR-4 (CRUD + filter + cari + pagination + auto-assign), FR-5 (import), FR-7 (laporan).

**Should have:** FR-3 (dashboard + grafik), FR-6 (export), FR-2 (kelola user), FR-8 (pengaturan).

**Could have:** FR-9 (backup & audit), toast notification.

**Won't have (versi ini):** jenis dokumen non-SPRI, scan dokumen, REST API, PDF resmi, multi-kantor.

---

## 10. Asumsi & Kendala

- Server dev XAMPP Windows; deploy target cPanel (subfolder).
- Tidak ada koneksi ke sistem Imigrasi pusat (offline internal).
- Data SPRI internal, akses dibatasi user terdaftar.
- Auto-assign lokasi hanya untuk data baru; data lama (loker_id=0) dikoreksi manual via Edit.

---

## 11. Milestone Rilis

| Milestone | Isi |
| --------- | --- |
| M1        | Auth + CRUD + filter/cari + rekap (inti) |
| M2        | Import CSV + export + laporan cetak |
| M3        | Dashboard + kelola user + pengaturan |
| M4        | Hardening: prepared, CSRF, validasi, toast, SVG |
| M5        | Refactor MVC + deploy cPanel |
| M6        | Lokasi fisik arsip (Loker→Rak→Urutan) + peta loker |
| M7        | Role 4 tingkat + rate limiting + session timeout + auto-assign lokasi |
| M8        | Fokus SPRI-only (nonaktifkan jenis dokumen lain) + branding SI RAPI |

---

## 12. Kriteria Rilis (DoD)

- [ ] Semua FR "Must have" lolos acceptance.
- [ ] Tidak ada query mutasi tanpa prepared statement.
- [ ] CSRF aktif di semua form POST.
- [ ] Role 4 tingkat berfungsi (superadmin/admin/petugas/viewer).
- [ ] Rate limiting + session timeout aktif.
- [ ] Auto-assign lokasi SPRI berjalan (Loker→Rak→Urutan).
- [ ] Import CSV 1000 baris valid tanpa error.
- [ ] Laporan bulanan akurat (cocok hitung manual sampel).
- [ ] Backup & restore teruji.
