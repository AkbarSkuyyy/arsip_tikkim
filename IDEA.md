# IDEA — Sistem Arsip TIKKIM (Arsip SPRI)

**Kantor Imigrasi Kelas II TPI Sampit · Seksi TIKKIM**

Dokumen ini berisi konsep, kondisi saat ini, dan arah pengembangan aplikasi arsip.

---

## 1. Ringkasan

Aplikasi web untuk mendigitalisasi arsip **SPRI** (Surat Permohonan penerbitan
dokumen perjalanan / paspor) yang sebelumnya dicatat manual. Data permohonan
paspor (nama, tanggal lahir, jenis kelamin, jenis dokumen, status Baru /
Penggantian HB) direkam, direkap, dan dicetak menjadi laporan bulanan.

**Tujuan:**

- Ganti pencatatan manual (buku/Excel) dengan database terpusat.
- Rekap otomatis: total, Baru vs Penggantian, Laki vs Perempuan per bulan.
- Cari & filter data cepat.
- Import massal dari CSV, export ke Excel.
- Laporan siap cetak untuk kebutuhan seksi.

---

## 2. Latar Belakang

Seksi TIKKIM memproses permohonan paspor setiap hari. Arsip SPRI berisi daftar
pemohon beserta jenis permohonan (paspor **Baru** atau **Penggantian HB** —
habis berlaku). Sebelumnya rekap dilakukan manual sehingga rawan salah hitung,
sulit dicari, dan lambat saat dibutuhkan laporan.

Aplikasi ini dibangun sebagai solusi internal berbiaya rendah: PHP native +
MySQL (XAMPP), tanpa framework berat, mudah di-deploy ke server lokal maupun
cPanel.

---

## 3. Kondisi Saat Ini (Existing)

### 3.1 Stack

| Komponen   | Teknologi                                    |
| ---------- | -------------------------------------------- |
| Backend    | PHP native (procedural, mysqli)              |
| Database   | MariaDB 10.4 (MySQL)                         |
| Frontend   | HTML/CSS/JS vanilla + Chart.js (CDN)         |
| Font       | Inter + Outfit (Google Fonts)                |
| Server dev | XAMPP (Apache)                               |
| Deploy     | cPanel (subfolder, relative path)            |

### 3.2 Basis data

| Tabel            | Isi                                                        |
| ---------------- | ---------------------------------------------------------- |
| `users`          | akun + role (`admin` / `user`), foto profile               |
| `permohonan`     | data pemohon SPRI (utama)                                  |
| `permohonan_spri`| template 12 baris bulan utk cetak rekap SPRI               |
| `pengaturan`     | identitas instansi + pejabat utk kop laporan               |
| `logs`           | jejak aktivitas (login, tambah, hapus, ubah role)          |

### 3.3 Fitur

- **Auth** — login (email/username + `password_verify`), register,
  reset password, kelola user (tambah/edit/hapus/ubah role).
- **Dashboard** — kartu statistik (total, Baru, Penggantian), grafik
  jenis kelamin (Chart.js), 5 permohonan terbaru.
- **Data Permohonan** — CRUD (tambah popup, edit popup, hapus), filter
  bulan + tahun, pencarian nama, pagination (15/halaman), rekap bulanan.
- **Tambah Permohonan** — formulir input manual.
- **Import Arsip** — upload CSV, skip header, buang baris kosong,
  normalisasi bulan/tanggal 2 digit.
- **Export Excel** — unduh `.xls` rekap.
- **Laporan** — rekap filter rentang tanggal + nama, siap cetak.
- **Pengaturan** — identitas kementerian/instansi/seksi/pejabat.
- **Backup** — backup database.
- **Log Aktivitas** — `logs` via `catatLog()`.

---

## 4. Masalah yang Sudah Teridentifikasi (Technical Debt)

| Isu                                            | Dampak                          |
| ---------------------------------------------- | ------------------------------- |
| Query string interpolasi (`mysqli_real_escape_string`) | rawan SQL injection pada pola tertentu |
| Belum ada CSRF token di form POST              | rawan CSRF                      |
| File flat PHP campur logika + HTML             | sulit maintain, duplikasi CSS   |
| Kolom tanggal lahir dipecah 3 kolom varchar    | validasi lemah (ada nilai `80`, `87`) |
| `alert()` dipakai untuk feedback               | UX kurang rapi                  |
| Emoji di judul modal/form                      | tidak konsisten dgn identitas institusi |

---

## 5. Roadmap / Ide Pengembangan

### Fase 1 — Hardening (prioritas)

1. **Migrasi ke PDO + prepared statements** untuk semua query mutasi.
2. **CSRF token** di setiap form POST.
3. **Validasi input ketat** — tanggal lahir 1–31, bulan 1–12, jenis
   kelamin enum, `dokumen` terbatas ke kode valid.
4. Ganti `alert()` dengan notifikasi inline (toast).
5. Bersihkan emoji, ganti ikon SVG (konsisten tema Imigrasi).

### Fase 2 — Refactor Arsitektur

6. **MVC ringan**: `app/Core` (Router, Database, Auth, Session, Helpers),
   Controller per modul, View pakai layout terpisah (`head`, `sidebar`,
   `footer`), front controller `index.php` + `.htaccess` rewrite.
   *(Sebagian sudah digarap di branch `arsip`.)*
7. Pisahkan CSS/JS per halaman ke file `asset/` (hapus duplikasi).

### Fase 3 — Fitur Arsip Digital

8. **Scan dokumen** — checklist kelengkapan 12 dokumen per permohonan +
   galeri hasil scan per dokumen.
9. **Integrasi scanner** — Epson TWAIN via NAPS2 (`NAPS2.Console.exe`),
   endpoint `scan_naps2.php`, eksekusi server-side.
   *Catatan: Apache harus jalan via XAMPP Control Panel (bukan Windows
   service Session 0) agar dialog scanner tampil.*
10. **Penyimpanan file scan** — struktur folder per permohonan, thumbnail,
    preview inline.

### Fase 4 — Laporan & Analitik

11. Template cetak SPRI resmi (kop, nomor urut bulanan, tanda tangan pejabat).
12. Dashboard diperluas: tren bulanan (bar chart), proporsi jenis dokumen,
    top bulan tersibuk.
13. Export PDF resmi (selain `.xls`).

### Fase 5 — Operasional

14. **Role & permission** lebih granular (admin seksi vs operator).
15. **REST API** ringan untuk integrasi sistem lain.
16. Deploy cPanel + konfigurasi `.htaccess` (`DirectoryIndex index.php`,
    `Options -Indexes`, disable directory listing).

---

## 6. Konvensi Proyek

- Path **relatif** agar aman deploy di subfolder cPanel.
- `.htaccess`: `DirectoryIndex index.php` + `Options -Indexes`.
- `password_hash()` / `password_verify()`, minimal 8 karakter.
- Prepared statements untuk semua mutasi database.
- CSRF di semua form POST.
- UI: dark/light theme, glassmorphism, **zero emoji**, ikon SVG, font Inter,
  grid 8pt, tanpa layout bertumpuk.
- Bahasa antarmuka: Indonesia.

---

## 7. Definisi Selesai (Siap Pakai)

Aplikasi dianggap siap produksi bila:

- [ ] Seluruh query mutasi pakai prepared statements.
- [ ] CSRF aktif di semua form.
- [ ] Validasi tanggal lahir & jenis kelamin ketat.
- [ ] CRUD + import CSV + export Excel + laporan berjalan tanpa error.
- [ ] Backup & restore teruji.
- [ ] Akun default `admin` dan `user` bisa login (password ter-hash).
- [ ] Deploy cPanel berjalan di subfolder (relative path OK).
