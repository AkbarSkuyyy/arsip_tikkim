# IDEA & Roadmap — SI RAPI

Daftar ide & saran fitur selanjutnya. Fokus produk: **arsip SPRI saja**,
dengan 4 role (superadmin / admin / petugas / viewer).

---

## Status Terkini (Sudah Terimplementasi)

| Fitur | Status |
| ----- | ------ |
| Auth + 4 role (superadmin/admin/petugas/viewer) | ✅ |
| Rate limiting login (5 gagal → kunci 15 menit, per IP) | ✅ |
| Session timeout idle 30 menit | ✅ |
| CRUD data permohonan SPRI + nomor permohonan | ✅ |
| Auto-assign lokasi Loker → Rak → Urutan (khusus SPRI) | ✅ |
| Peta Loker (11 kartu + kode seri `L-R-U`) | ✅ |
| Realtime polling AJAX (10 detik) | ✅ |
| Grafik dashboard Chart.js (tren, gender, status, role, dokumen, log) | ✅ |
| Import CSV + export Excel (.xls) | ✅ |
| Laporan periode siap cetak | ✅ |
| Backup + restore database | ✅ |
| Audit trail (log lengkap + filter + pagination) | ✅ |
| Branding SI RAPI + instansi Kotawaringin Timur | ✅ |
| Prepared statements + CSRF + password_hash | ✅ |
| Fokus SPRI-only (jenis dokumen lain nonaktif) | ✅ |

---

## Ide Fitur Masa Depan (Prioritas)

### Prioritas Tinggi

| # | Ide | Nilai |
| - | --- | ----- |
| 1 | **Nomor permohonan otomatis + unik** (`SPRI-2026-0001` per bulan) | Cegah duplikat, gampang dilacak |
| 2 | **QR code per lokasi fisik** (Loker-Rak-Urutan) | Scan langsung buka record, cepat cari arsip fisik |
| 3 | **Validasi rak/urutan sesuai kapasitas fisik** | Cegah input "Z"/"999" di luar rentang (A-E / A-D, 1-200 / 1-100) |
| 4 | **Export PDF resmi** (kop + tanda tangan pejabat) | Siap cetak resmi (sekarang cuma .xls) |

### Prioritas Sedang

| # | Ide | Nilai |
| - | --- | ----- |
| 5 | **Riwayat perubahan per record** (nilai sebelum/sesudah) | Audit siapa edit apa, bukan cuma "mengubah data" |
| 6 | **Migrasi data lama (loker_id=0) ke auto-assign** | Arsip lama bisa dicari fisiknya juga |
| 7 | **Pindah kapasitas loker & jenis "punya loker" ke master data** | Superadmin bisa atur tanpa ubah kode |
| 8 | **Scan dokumen terintegrasi (NAPS2/Epson)** | Arsip digital per permohonan |

### Prioritas Rendah

| # | Ide | Nilai |
| - | --- | ----- |
| 9 | **Arsip inaktif / retensi** | Pisah data lama ke gudang, data aktif tetap cepat |
| 10 | **Ringkasan otomatis ke kepala kantor** (harian/bulanan) | Laporan terkirim otomatis |
| 11 | **PWA / mobile-friendly** | Input dari loket |

---

## Roadmap (Fase Disarankan)

```
Fase A (stabilitas & data):   #1 nomor otomatis → #3 validasi rak/urutan
Fase B (pencarian fisik):     #2 QR code → #6 migrasi data lama
Fase C (operasional):         #4 PDF resmi → #7 konfigurasi master data
Fase D (digital & skala):     #8 scan dokumen → #9 retensi → #11 PWA
```

---

## Catatan Risiko

1. **Data lama (loker_id=0)** — belum dicari fisik; migrasi perlu konfirmasi.
2. **Kapasitas loker hardcode** di kode (5 rak×200 / 4 rak×100) — pindahkan ke master data saat butuh fleksibilitas.
3. **Nomor permohonan belum unique** — rawan duplikat sampai #1 dikerjakan.
4. **Scanner (NAPS2)** — hanya jalan kalau Apache via XAMPP Control Panel (bukan service Windows).
