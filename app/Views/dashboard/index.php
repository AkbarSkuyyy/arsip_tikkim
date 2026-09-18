<?php
/**
 * View: dashboard analitik staf. Variabel dari DashboardController::index():
 * $total_data, $total_baru, $total_ganti, $label_jk, $data_jk, $recent, $tren_labels, $tren_data
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SI RAPI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .stat-value, .brand-main { font-family: 'Outfit', sans-serif; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; background: var(--bg-main);}
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); position: relative; overflow: hidden; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--imigrasi-blue); }
        .stat-card:nth-child(2)::before { background: #10B981; }
        .stat-card:nth-child(3)::before { background: var(--imigrasi-gold); }
        .stat-title { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--imigrasi-dark); line-height: 1; }
        .stat-sub { font-size: 0.8rem; color: var(--text-blue-grey); font-weight: 500; margin-top: 8px; }
        .charts-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; margin-bottom: 32px; }
        .card-container { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
        .canvas-container { position: relative; height: 260px; width: 100%; display: flex; justify-content: center; }
        .btn-view-all { background: var(--imigrasi-blue); color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 500; font-size: 0.9rem; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); }
        .btn-view-all:hover { background: #08305A; transform: translateY(-1px); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 16px 20px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 16px 20px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; font-weight: 500; color: var(--text-dark); }
        tbody tr:hover { background-color: rgba(244, 247, 250, 0.5); }
        .badge { background-color: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .badge-baru { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
        .badge-ganti { background-color: rgba(245, 166, 35, 0.1); color: #D48806; }
        .live-badge { font-size: 0.75rem; font-weight: 700; color: #059669; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); padding: 4px 12px; border-radius: 20px; margin-left: 12px; display: inline-flex; align-items: center; gap: 6px; }
        .live-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #10B981; animation: rt-pulse 2s infinite; }
        @keyframes rt-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

        /* Responsive mobile */
        @media (max-width: 900px) {
            .main-content { padding: 0; min-width: 0; width: 100%; }
            .top-header { padding: 12px 16px 12px 76px; height: auto; min-height: 76px; flex-wrap: wrap; }
            .page-title { font-size: 1.15rem; flex-wrap: wrap; min-width: 0; overflow-wrap: anywhere; }
            .content-wrapper { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
            .stat-card { padding: 20px; }
            .stat-value { font-size: 2rem; }
            .charts-grid { grid-template-columns: 1fr; gap: 12px; }
            .card-container { padding: 16px; }
            .canvas-container { height: 220px; }
        }
        @media (max-width: 600px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .content-wrapper { padding: 12px; }
            .page-title { font-size: 1.05rem; }
            .stat-value { font-size: 1.6rem; }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include __DIR__ . '/../layouts/nav.php'; ?>
        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Dashboard Analitik SPRI <span class="live-badge">Live · <span data-rt-time>--:--:--</span></span></h1>
            </header>
            <div class="content-wrapper">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Keseluruhan</div>
                        <div class="stat-value" data-rt="total"><?= (int)$total_data ?></div>
                        <div class="stat-sub">Arsip SPRI</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Permohonan Baru</div>
                        <div class="stat-value" data-rt="baru"><?= (int)$total_baru ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Penggantian Paspor</div>
                        <div class="stat-value" data-rt="ganti"><?= (int)$total_ganti ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="card-container">
                        <h2 class="card-title">Jenis Kelamin</h2>
                        <div class="canvas-container"><canvas id="chartGender"></canvas></div>
                    </div>
                    <div class="card-container">
                        <h2 class="card-title">Status Permohonan SPRI</h2>
                        <div class="canvas-container"><canvas id="chartStatus"></canvas></div>
                    </div>
                </div>

                <div class="card-container" style="margin-bottom: 32px;">
                    <h2 class="card-title">Tren Permohonan Bulanan</h2>
                    <div class="canvas-container" style="height: 280px;"><canvas id="chartTren"></canvas></div>
                </div>

                <div class="card-container" style="padding: 0;">
                    <div class="card-title" style="padding: 24px 28px 0 28px;">
                        Daftar Permohonan Terbaru
                        <a href="data_permohonan.php" class="btn-view-all">Kelola Data &rarr;</a>
                    </div>
                    <div class="table-scroll" role="region" aria-label="Daftar permohonan terbaru, geser untuk melihat kolom lainnya" tabindex="0">
                    <table style="margin-top: 16px;">
                        <thead>
                            <tr>
                                <th style="padding-left: 28px;">NAMA PEMOHON</th>
                                <th style="text-align: center;">DOKUMEN</th>
                                <th style="text-align: center;">JENIS KELAMIN</th>
                                <th style="padding-right: 28px;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent) > 0): foreach ($recent as $row): $badge_class = (stripos($row['keterangan'], 'baru') !== false) ? 'badge badge-baru' : 'badge badge-ganti'; ?>
                            <tr>
                                <td style="padding-left: 28px; font-weight:600; color:var(--imigrasi-dark);"><?= htmlspecialchars($row['nama_pemohon']) ?></td>
                                <td style="text-align: center; color: var(--text-blue-grey);"><?= htmlspecialchars($row['dokumen']) ?></td>
                                <td style="text-align: center; color: var(--text-blue-grey);"><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
                                <td style="padding-right: 28px;"><span class="<?= $badge_class ?>"><?= htmlspecialchars($row['keterangan']) ?></span></td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--text-blue-grey);">Belum ada data terbaru.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const labelsGender = <?= json_encode($label_jk) ?>;
        const dataGender = <?= json_encode($data_jk) ?>;
        const dataBaru = <?= (int)$total_baru ?>;
        const dataGanti = <?= (int)$total_ganti ?>;
        const colors = ['#0B427B', '#F5A623'];
        const barColors = ['#10B981', '#F5A623'];

        new Chart(document.getElementById('chartGender').getContext('2d'), {
            type: 'doughnut',
            data: { labels: labelsGender, datasets: [{ data: dataGender, backgroundColor: colors, borderWidth: 0, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { font: { family: 'Inter' }, padding: 20 } } } }
        });

        new Chart(document.getElementById('chartStatus').getContext('2d'), {
            type: 'bar',
            data: { labels: ['Permohonan Baru', 'Penggantian Paspor'], datasets: [{ label: 'Jumlah SPRI', data: [dataBaru, dataGanti], backgroundColor: barColors, borderRadius: 8, borderWidth: 0, barThickness: 60 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 10, font: { family: 'Inter' } }, border: { display: false } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: 600 } } }
                }
            }
        });

        const trenLabels = <?= json_encode($tren_labels) ?>;
        const trenData = <?= json_encode($tren_data) ?>;
        new Chart(document.getElementById('chartTren').getContext('2d'), {
            type: 'line',
            data: { labels: trenLabels, datasets: [{ label: 'Jumlah Permohonan', data: trenData, borderColor: '#0B427B', backgroundColor: 'rgba(11,66,123,0.1)', fill: true, tension: 0.3, borderWidth: 2, pointBackgroundColor: '#F5A623', pointRadius: 4 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, border: { display: false }, ticks: { font: { family: 'Inter' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Inter' } } }
                }
            }
        });
    </script>
    <script src="asset/js/realtime.js"></script>
</body>
</html>
