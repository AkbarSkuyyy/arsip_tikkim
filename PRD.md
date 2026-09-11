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
dan **Penggantian Paspor**.

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
| ------ | --------------------------------------- | ----------------------------------------- |
| Admin  | Kepala/penanggung jawab seksi          | Kelola user, lihat semua data & rekap, backup |
| User   | Staff arsip / operator TIKKIM          | Input, edit, cari, cetak laporan — dibatasi 1 jenis dokumen (diatur superadmin) |
| Super Admin | Pengelola sistem                 | Kelola user & role, akses dokumen per user, pengaturan instansi |

---

## 5. Lingkup (Scope)

### Di dalam lingkup

- Autentikasi & otorisasi (role admin/user; user dibatasi 1 jenis dokumen).
- CRUD data permohonan (SPRI, Paspor Biasa, Izin Tinggal, Lainnya) dengan field dinamis per jenis.
- Filter bulan/tahun/loker/jenis dokumen, pencarian nama, pagination.
- Rekap otomatis (total, Baru, Penggantian, L/P).
- Lokasi fisik arsip (loker → rak → kotak) otomatis (auto-assign) + peta loker + seri lokasi.
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

### FR-2 Manajemen User (Superadmin)

- **FR-2.1** Kelola Akun: daftar akun + info (nama, username, jabatan, email, role, akses dokumen) — halaman tersendiri (`kelola_akun.php`), terpisah dari form tambah akun.
- **FR-2.2** Tambah akun (`register.php`): nama, username, jabatan, email, password, role, foto, **akses dokumen (radio, tepat 1 jenis untuk role user)**.
- **FR-2.3** Edit user (radio akses dokumen tunggal, konsisten dengan tambah).
- **FR-2.4** Hapus user.
- **FR-2.5** Ubah role (`admin` ↔ `user`); admin/superadmin akses semua jenis dokumen, user dibatasi 1 jenis yang diatur superadmin.

### FR-3 Dashboard

- **FR-3.1** Kartu statistik: total data, total Baru, total Penggantian.
- **FR-3.2** Grafik jenis kelamin (L vs P) — Chart.js.
- **FR-3.3** Tabel 5 permohonan terbaru.

### FR-4 Data Permohonan

- **FR-4.1** Tambah data (popup): nama, **nomor permohonan**, tanggal lahir, jenis dokumen, jenis kelamin, keterangan, tanggal input. Lokasi (loker/rak/kotak) **tidak diisi manual** — otomatis (lihat FR-4.9).
- **FR-4.1a** Field dinamis per jenis dokumen (toggle JS sesuai jenis dipilih):
  - SPRI / Paspor Biasa: field standar saja (nama, tgl lahir, JK).
  - Izin Tinggal (WNA): + kewarganegaraan, no. paspor kebangsaan, no. izin tinggal (KITAS/KITAP), masa berlaku, nama penjamin/sponsor.
  - Lainnya: + catatan/keterangan bebas.
- **FR-4.2** Edit data (popup), hanya 1 ID valid. Field Loker/Rak/Kotak **tetap manual di form Edit** (untuk koreksi data lama/data lama sebelum auto-assign).
- **FR-4.3** Hapus data dengan konfirmasi.
- **FR-4.4** Filter bulan + tahun + loker + jenis dokumen (dibatasi otomatis kalau akun user hanya punya akses 1 jenis).
- **FR-4.5** Pencarian nama (`LIKE`).
- **FR-4.6** Pagination 15 baris/halaman.
- **FR-4.7** Rekap bulanan ringkas (total, baru, penggantian, L/P), realtime via polling AJAX (10 detik).
- **FR-4.8** Kolom LOKASI di tabel + Peta Loker (grid 11 loker, jumlah arsip per loker, badge seri kode `Loker-Rak-Kotak`).
- **FR-4.9** Auto-assign lokasi arsip (loker → rak → kotak) untuk data BARU: sistem cari slot kosong berikutnya secara sekuensial (isi kotak dulu, baru pindah rak, baru pindah loker), sesuai kapasitas fisik tiap unit (lihat §8 Model Data). Data lama (`loker_id=0`) tidak dimigrasi otomatis.

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
| `users`          | id, nama, username, jabatan, email, password (hash), role, foto_profile, **akses_dokumen** (kode jenis dokumen tunggal; `*` = semua, hanya admin/superadmin) |
| `permohonan`     | id, nama_pemohon, **no_permohonan**, tgl_lahir (DATE), dokumen, jenis_kelamin, **kewarganegaraan, no_paspor_kebangsaan, no_izin_tinggal, masa_berlaku, nama_penjamin, catatan_lain** (field dinamis Izin Tinggal/Lainnya), keterangan, tanggal_input, loker_id, rak, kotak |
| `master_loker`   | id, nomor, nama, aktif (11 loker fisik: 1-8 unit besar 5 rak A-E ×200 kotak; 9-11 unit "3 setir"/roll o'pack, 1 loker = 1 baris, 4 rak A-D ×100 kotak) |
| `master_dokumen` | id, kode, nama, aktif (SPRI, Paspor Biasa, Izin Tinggal, Lainnya) |
| `master_keterangan` | id, nama, aktif (Baru / Penggantian Paspor / dll)         |
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
| M6        | Lokasi fisik arsip (loker → rak → kotak) + peta loker  |
| M7        | Akses dokumen per akun (single-doc), Kelola Akun terpisah, timezone Asia/Jakarta, field dinamis per jenis dokumen, nomor permohonan, auto-assign lokasi |

---

## 12. Kriteria Rilis (DoD)

- [ ] Semua FR "Must have" lolos acceptance.
- [ ] Tidak ada query mutasi tanpa prepared statement.
- [ ] CSRF aktif di semua form POST.
- [ ] Akun default `admin` & `user` login normal (password ter-hash).
- [ ] Import CSV 1000 baris valid tanpa error.
- [ ] Laporan bulanan akurat (cocok hitung manual sampel).
- [ ] Backup & restore teruji.
