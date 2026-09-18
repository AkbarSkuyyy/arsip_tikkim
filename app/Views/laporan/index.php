<?php
/**
 * View: laporan rekapitulasi periode tanggal (cetak PDF + export Excel).
 * Variabel dari LaporanController::index(): $cari, $tgl_mulai, $tgl_selesai,
 * $data_laporan, $total_data, $total_L, $total_P, $total_baru, $total_ganti, $teks_periode
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi - SI RAPI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        :root {
            --imigrasi-dark: #0A2540;
            --imigrasi-blue: #0B427B;
            --imigrasi-gold: #F5A623;
            --bg-main: #F4F7FA;
            --surface: #FFFFFF;
            --text-dark: #0F172A;
            --text-blue-grey: #64748B;
            --border-light: #E2E8F0;
            --excel-green: #107C41;
            --pdf-red: #E3242B;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .brand-main { font-family: 'Outfit', sans-serif; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; align-items: center; }
        .panel-kontrol { width: 100%; max-width: 1000px; background: var(--surface); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px; overflow: hidden; }
        .filter-form { display: flex; flex-wrap: wrap; gap: 16px; padding: 20px 24px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-blue-grey); }
        .form-control { padding: 10px 14px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; }
        .form-control:focus { border-color: var(--imigrasi-blue); }
        .btn { padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s; color: white; text-decoration: none; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .btn-primary { background-color: var(--imigrasi-blue); }
        .btn-pdf { background-color: var(--pdf-red); }
        .btn-excel { background-color: var(--excel-green); }
        .quick-filters { background: #F8FAFC; padding: 12px 24px; border-top: 1px solid var(--border-light); display: flex; gap: 12px; align-items: center; }
        .quick-btn { background: none; border: 1px solid var(--border-light); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; color: var(--text-dark); font-weight: 500; background: white; }
        .quick-btn:hover { background: var(--border-light); }
        .report-document { background: var(--surface); width: 100%; max-width: 1000px; padding: 60px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 40px; }
        .report-heading { text-align: center; margin-bottom: 30px; }
        .report-heading h3 { font-size: 1.4rem; margin-bottom: 8px; color: var(--imigrasi-dark); }
        .report-heading p { font-size: 0.95rem; color: var(--text-dark); font-weight: 600; }
        .rekap-box { display: flex; justify-content: center; gap: 24px; margin-bottom: 20px; padding: 12px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; }
        .rekap-item { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); }
        .rekap-item span { color: var(--imigrasi-blue); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem; }
        th, td { border: 1px solid #CBD5E1; padding: 10px 14px; }
        th { background-color: var(--imigrasi-dark); color: white; text-align: center; font-weight: 600; letter-spacing: 0.5px; }
        td { color: var(--text-dark); font-weight: 500; }
        tbody tr:nth-child(even) { background-color: #F8FAFC; }
        @media print {
            body { background: white; }
            .sidebar, .top-header, .panel-kontrol { display: none !important; }
            .main-content { overflow: visible; }
            .content-wrapper { padding: 0; }
            .report-document { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
            th { background-color: #0A2540 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .rekap-box, tbody tr:nth-child(even) { background-color: #F8FAFC !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/nav.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Laporan & Rekapitulasi SPRI</h1>
        </header>
        <div class="content-wrapper">
            <div class="panel-kontrol">
                <form method="GET" action="laporan.php" class="filter-form" id="formFilter">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Cari Nama Pemohon</label>
                        <input type="text" name="cari" class="form-control" placeholder="Ketik nama..." value="<?= htmlspecialchars($cari) ?>">
                    </div>
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= icon('search') ?> Terapkan</button>
                    <div class="filter-divider" aria-hidden="true" style="width: 1px; height: 38px; background: var(--border-light); margin: 0 8px;"></div>
                    <button type="button" class="btn btn-pdf" onclick="window.print()"><?= icon('printer') ?> Cetak PDF</button>
                    <a href="export_excel.php?cari=<?= urlencode($cari) ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" class="btn btn-excel" style="background-color: var(--excel-green);">
                        <span><?= icon('download') ?></span> Unduh Excel
                    </a>
                </form>
                <div class="quick-filters">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-blue-grey);">Filter Cepat:</span>
                    <button class="quick-btn" onclick="setFilterCepat('hari_ini')">Hari Ini</button>
                    <button class="quick-btn" onclick="setFilterCepat('bulan_ini')">Bulan Ini</button>
                    <button class="quick-btn" onclick="setFilterCepat('tahun_ini')">Tahun Ini</button>
                </div>
            </div>

            <div class="report-document" id="printableArea">
                <div class="report-heading">
                    <h3>REKAPITULASI DOKUMEN PERMOHONAN SPRI</h3>
                    <p>PERIODE: <?= $teks_periode ?></p>
                </div>
                <div class="rekap-box">
                    <div class="rekap-item">Total: <span><?= $total_data ?></span></div>
                    <div class="rekap-item">Laki-laki: <span><?= $total_L ?></span></div>
                    <div class="rekap-item">Perempuan: <span><?= $total_P ?></span></div>
                    <div class="rekap-item">Baru: <span><?= $total_baru ?></span></div>
                    <div class="rekap-item">Penggantian Paspor: <span><?= $total_ganti ?></span></div>
                </div>
                <div class="table-scroll" role="region" aria-label="Tabel laporan, geser untuk melihat kolom lainnya" tabindex="0">
                <table id="tabelLaporan">
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>NAMA PEMOHON</th>
                            <th>TGL LAHIR</th>
                            <th>TGL INPUT</th>
                            <th style="width: 80px;">DOK</th>
                            <th style="width: 80px;">L/P</th>
                            <th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($data_laporan) > 0): $no = 1; foreach ($data_laporan as $row): $tgl_lahir_format = !empty($row['tgl_lahir']) ? date('d/m/Y', strtotime($row['tgl_lahir'])) : '-'; $tgl_input_format = isset($row['tanggal_input']) ? date('d/m/Y', strtotime($row['tanggal_input'])) : '-'; ?>
                        <tr>
                            <td style="text-align: center;"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_pemohon']) ?></td>
                            <td style="text-align: center;"><?= $tgl_lahir_format ?></td>
                            <td style="text-align: center; color: var(--text-blue-grey);"><?= $tgl_input_format ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($row['dokumen']) ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
                            <td style="text-align: center;"><?= htmlspecialchars($row['keterangan']) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 30px;">Tidak ada data pada periode atau pencarian ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function setFilterCepat(jenis) {
            let today = new Date();
            let startDate, endDate;
            const formatDate = (date) => {
                let d = new Date(date), month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;
                return [year, month, day].join('-');
            }
            if (jenis === 'hari_ini') { startDate = today; endDate = today; }
            else if (jenis === 'bulan_ini') { startDate = new Date(today.getFullYear(), today.getMonth(), 1); endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0); }
            else if (jenis === 'tahun_ini') { startDate = new Date(today.getFullYear(), 0, 1); endDate = new Date(today.getFullYear(), 11, 31); }
            document.getElementById('tgl_mulai').value = formatDate(startDate);
            document.getElementById('tgl_selesai').value = formatDate(endDate);
            document.getElementById('formFilter').submit();
        }
    </script>
</body>
</html>
