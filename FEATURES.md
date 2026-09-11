# Rekomendasi & Saran Fitur Selanjutnya — Arsip TIKKIM

Prioritas berdasar dampak vs usaha, dan risiko teknis yang sudah ditemukan di kode.

> **Status terbaru:** mayoritas rekomendasi prioritas sudah direalisasikan —
> prepared statements, CSRF, migrasi `tgl_lahir DATE`, konsolidasi MVC,
> toast, SVG, ekstrak CSS, master dokumen/keterangan/loker, rate limiting,
> backup+restore, dan **lokasi fisik arsip (loker → rak → kotak)**.

---

## 1. Rekomendasi Prioritas (Urut Dikerjakan)

| # | Fitur / Perbaikan | Kategori | Dampak | Usaha | Alasan |
| - | ----------------- | -------- | ------ | ----- | ------ |
| 1 | Prepared statements (PDO) utk semua query mutasi | Keamanan | KRITIS | Sedang | Interpolasi `mysqli_real_escape_string` masih rawan SQL injection di pola tertentu |
| 2 | CSRF token di semua form POST | Keamanan | KRITIS | Kecil | Form tambah/edit/hapus/import tanpa token → bisa ditembak request silang |
| 3 | Validasi tanggal lahir (1–31, bulan 1–12) + migrasi ke `tgl_lahir DATE` | Data | Tinggi | Sedang | Ada data invalid (`tanggal_lahir='80'`, `'87'`, `'80'` di bulan) → rekap bisa salah |
| 4 | Konsolidasi form input (`tambah.php` vs `tambah_permohonan.php`) | Maintain | Tinggi | Kecil | Dua form duplikat fungsi → rawan beda perilaku |
| 5 | Ganti `alert()` dengan toast notification | UX | Sedang | Kecil | Feedback kasar, tidak konsisten tema |
| 6 | Hapus emoji, ganti ikon SVG | UX | Sedang | Kecil | Tidak konsisten identitas institusi |
| 7 | Ekstrak CSS inline ke `asset/css/*.css` | Maintain | Sedang | Sedang | Duplikasi CSS di tiap file, susah ubah tema |
| 8 | Fix log hapus user kosong | Audit | Sedang | Kecil | `logs` id 20–27 user kosong → ambil dari `$_SESSION`, bukan param eksternal |
| 9 | Restore backup (upload `.sql`) | Operasional | Tinggi | Sedang | Backup hanya bisa didownload, tidak bisa dipulihkan → bencana recovery belum ada |

---

## 2. Saran Jangka Menengah (Fitur Baru)

### 2.1 Arsip Digital (Scan Dokumen)
- Checklist 12 dokumen kelengkapan per permohonan.
- Galeri hasil scan per dokumen.
- Integrasi scanner Epson TWAIN via **NAPS2** (`NAPS2.Console.exe`), endpoint `scan_naps2.php` server-side.
  - *Prasyarat:* Apache jalan via XAMPP Control Panel (bukan Windows service Session 0) agar dialog scanner tampil.

### 2.2 Laporan & Cetak
- Template cetak SPRI resmi (kop, nomor urut bulanan, tanda tangan pejabat).
- Export PDF resmi (saat ini cuma `.xls`).
- Rekap per jenis dokumen (saat ini `dokumen` cuma kode `1`; belum ada master jenis dokumen).

### 2.3 Dashboard & Analitik
- Tren bulanan (bar chart) — lihat bulan tersibuk.
- Proporsi jenis dokumen (pie).
- Ringkasan tahunan (total per tahun).

### 2.4 Master Data
- Tabel master **jenis dokumen** — ✅ sudah (`master_dokumen`).
- Tabel master **keterangan** (Baru / Penggantian Paspor / Perubahan Nama / Penambahan Nama) — ✅ sudah (`master_keterangan`).
- Tabel master **loker** (11 loker fisik) — ✅ sudah (`master_loker`).

### 2.5 Keamanan & Akun
- Rate limiting login (cegah brute force).
- Role granular (admin seksi vs operator vs viewer).
- Enforce password min 8 char + `password_hash` (sudah ada, perlu enforce di form).

### 2.6 Lokasi Fisik Arsip (Loker → Rak → Kotak) — ✅ sudah
- `master_loker` (11 loker) + kolom `loker_id`, `rak`, `kotak` di `permohonan`.
- Form tambah/edit: pilih Loker, isi Rak & Kotak.
- Tabel list: kolom LOKASI (`Loker 7 · Rak A · Kotak 3`).
- Filter Loker + Peta Loker (grid 11 kartu, jumlah arsip per loker).
- Fase berikutnya (opsional): label QR/barcode per kotak → scan buka record.

---

## 3. Saran Jangka Panjang

| Fitur | Nilai | Catatan |
| ----- | ----- | ------- |
| MVC refactor (front controller + Router) | Maintain | Sebagian sudah di branch `arsip`, gabungkan |
| REST API ringan | Integrasi | Untuk sistem lain di Kanim (ImigTrack, M-Clearance) |
| Audit trail lengkap (siapa ubah data apa, nilai sebelum/sesudah) | Governansi | Perluas `logs` → simpan field + before/after |
| Multi-kantor | Skala | Arsitektur multi-tenant |
| PWA / mobile-friendly | Akses | Untuk input dari loket |
| Integrasi API Imigrasi pusat | Otomasi | Butuh izin & dokumentasi resmi |

---

## 4. Urutan Kerja Disarankan (Quick Wins Dulu)

```
Fase A (sekarang, kecil & kritis):
  1 → 2 → 3 → 8   (keamanan + data invalid + audit)

Fase B (UX & maintain):
  4 → 5 → 6 → 7   (konsolidasi, toast, SVG, ekstrak CSS)

Fase C (operasional):
  9 → 2.1 → 2.2   (restore, scan dokumen, cetak PDF)

Fase D (skala):
  2.3 → 2.4 → REST API → multi-kantor
```

---

## 5. Catatan Risiko yang Harus Diperhatikan

1. **SQL injection** — semua query mutasi harus pindah ke prepared statements sebelum expose ke jaringan kantor.
2. **Data tanggal lahir invalid** — wajib dibersihkan sebelum rekap dipakai laporan resmi.
3. **Backup tanpa restore** — bencana recovery belum ada; tambahkan restore segera.
4. **Emoji + `alert()`** — minor tapi menurunkan kesan profesional aplikasi institusi.
5. **Scanner (NAPS2)** — hanya jalan kalau Apache dijalankan manual via XAMPP Control Panel, bukan service Windows.
