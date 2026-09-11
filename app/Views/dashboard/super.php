<?php
/**
 * View: dashboard super admin. Variabel dari DashboardSuperController::index():
 * $total_data, $total_hari_ini, $total_bulan_ini, $total_users, $total_admin,
 * $dok_labels, $dok_data, $ket_labels, $ket_data, $tren_labels, $tren_data,
 * $log_labels, $log_data, $users, $logs, $db_size_mb, $total_logs
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); display: flex; align-items: center; gap: 10px;}
        .content-wrapper { padding: 32px 40px; max-width: 1500px; margin: 0 auto; width: 100%; }
        .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; margin-bottom: 28px; }
        @media (max-width: 1300px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
        .stat-card { background: var(--surface); padding: 22px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--imigrasi-blue); }
        .stat-card.c-green::before { background: #10B981; }
        .stat-card.c-gold::before { background: var(--imigrasi-gold); }
        .stat-card.c-purple::before { background: #6366F1; }
        .stat-card.c-red::before { background: #EF4444; }
        .stat-title { font-size: 0.78rem; color: var(--text-blue-grey); font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.4px; }
        .stat-value { font-size: 2rem; font-weight: 700; color: var(--imigrasi-dark); line-height: 1; }
        .charts-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .charts-row-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px; }
        @media (max-width: 1200px) { .charts-row-3, .charts-row-2 { grid-template-columns: 1fr; } }
        .card-container { background: var(--surface); padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-title { font-size: 1.05rem; font-weight: 700; color: var(--imigrasi-dark); margin: 0; }
        .canvas-container { position: relative; height: 240px; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.78rem; padding: 14px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; text-align: left; }
        td { padding: 14px; border-bottom: 1px solid var(--border-light); font-size: 0.92rem; font-weight: 500; }
        .btn-add { background: var(--imigrasi-gold); color: var(--imigrasi-dark); padding: 9px 18px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.85rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;}
        .btn-add:hover { opacity: 0.9; transform: translateY(-2px); }
        .btn-backup, .btn-restore { color: white; padding: 9px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 8px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem;}
        .btn-backup { background: #10B981; }
        .btn-restore { background: #6366F1; }
        .log-item { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-light); }
        .log-item:last-child { border-bottom: none; margin-bottom: 0; }
        .log-aksi { font-weight: 600; font-size: 0.87rem; color: var(--imigrasi-blue); }
        .log-meta { font-size: 0.78rem; color: var(--text-blue-grey); margin-top: 3px; }
        .btn-edit, .btn-delete, .btn-reset { padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: 0.2s; display: inline-block; border: none; cursor: pointer; }
        .btn-edit { background: #3B82F6; color: #fff; margin-right: 5px; }
        .btn-reset { background: #6366F1; color: white; margin-right: 5px; }
        .btn-delete { background: #EF4444; color: #fff; }
        .btn-edit:hover, .btn-delete:hover, .btn-reset:hover { opacity: 0.8; }
        .role-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; }
        .role-admin { background: rgba(245,166,35,0.15); color: #D48806; }
        .role-user { background: rgba(11,66,123,0.1); color: var(--imigrasi-blue); }
        .sys-info-list { display: flex; flex-direction: column; gap: 14px; }
        .sys-info-item { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-light); }
        .sys-info-item:last-child { border-bottom: none; padding-bottom: 0; }
        .sys-info-label { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 500; }
        .sys-info-value { font-size: 0.9rem; font-weight: 700; color: var(--imigrasi-dark); }
    </style>
</head>
<body>
    <?php include 'sidebar_super.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?= icon('crown') ?> Dashboard Super Admin</h1>
        </header>
        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">Total Permohonan</div>
                    <div class="stat-value"><?= (int)$total_data ?></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-title">Input Hari Ini</div>
                    <div class="stat-value"><?= (int)$total_hari_ini ?></div>
                </div>
                <div class="stat-card c-gold">
                    <div class="stat-title">Input Bulan Ini</div>
                    <div class="stat-value"><?= (int)$total_bulan_ini ?></div>
                </div>
                <div class="stat-card c-purple">
                    <div class="stat-title">Total Pengguna</div>
                    <div class="stat-value"><?= (int)$total_users ?></div>
                </div>
                <div class="stat-card c-red">
                    <div class="stat-title">Akun Admin</div>
                    <div class="stat-value"><?= (int)$total_admin ?></div>
                </div>
            </div>

            <div class="charts-row-3">
                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Proporsi Jenis Dokumen</h2>
                    <div class="canvas-container"><canvas id="chartDokumen"></canvas></div>
                </div>
                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Proporsi Keterangan</h2>
                    <div class="canvas-container"><canvas id="chartKeterangan"></canvas></div>
                </div>
                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Komposisi Role Pengguna</h2>
                    <div class="canvas-container"><canvas id="chartRole"></canvas></div>
                </div>
            </div>

            <div class="charts-row-2">
                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Tren Permohonan Bulanan (12 Bulan)</h2>
                    <div class="canvas-container" style="height: 260px;"><canvas id="chartTren"></canvas></div>
                </div>
                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Info Sistem</h2>
                    <div class="sys-info-list">
                        <div class="sys-info-item"><span class="sys-info-label">Ukuran Database</span><span class="sys-info-value"><?= $db_size_mb ?> MB</span></div>
                        <div class="sys-info-item"><span class="sys-info-label">Total Log Aktivitas</span><span class="sys-info-value"><?= (int)$total_logs ?></span></div>
                        <div class="sys-info-item"><span class="sys-info-label">PHP Version</span><span class="sys-info-value"><?= PHP_VERSION ?></span></div>
                        <div class="sys-info-item"><span class="sys-info-label">Server Time</span><span class="sys-info-value"><?= date('d/m/Y H:i') ?></span></div>
                    </div>
                </div>
            </div>

            <div class="card-container" style="margin-bottom: 24px;">
                <h2 class="card-title" style="margin-bottom: 20px;">Aktivitas Log (14 Hari Terakhir)</h2>
                <div class="canvas-container" style="height: 220px;"><canvas id="chartLogAktivitas"></canvas></div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <div class="card-container">
                    <div class="card-header">
                        <h2 class="card-title">Daftar Pengguna Sistem</h2>
                        <div>
                            <a href="backup.php" class="btn-backup"><?= icon('database') ?> Backup DB</a>
                            <a href="backup_restore.php" class="btn-restore"><?= icon('upload') ?> Restore DB</a>
                            <a href="register.php" class="btn-add"><?= icon('users') ?> Tambah Akun</a>
                        </div>
                    </div>
                    <table>
                        <thead><tr><th>NAMA</th><th>USERNAME</th><th>JABATAN</th><th>ROLE</th><th>AKSI</th></tr></thead>
                        <tbody>
                            <?php foreach ($users as $usr): ?>
                            <tr>
                                <td><?= htmlspecialchars($usr['nama']) ?></td>
                                <td><?= htmlspecialchars($usr['username']) ?></td>
                                <td><?= htmlspecialchars($usr['jabatan']) ?></td>
                                <td><span class="role-badge <?= $usr['role'] === 'admin' ? 'role-admin' : 'role-user' ?>"><?= htmlspecialchars($usr['role']) ?></span></td>
                                <td>
                                    <a href="edit_user.php?id=<?= (int)$usr['id'] ?>" class="btn-edit">Edit</a>
                                    <a href="reset_password.php?id=<?= (int)$usr['id'] ?>" class="btn-reset">Reset Pass</a>
                                    <form method="POST" action="hapus_user.php" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        <input type="hidden" name="id" value="<?= (int)$usr['id'] ?>">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-container">
                    <h2 class="card-title" style="margin-bottom: 20px;">Log Aktivitas Terbaru</h2>
                    <div>
                        <?php foreach ($logs as $log): ?>
                        <div class="log-item">
                            <div class="log-aksi"><?= htmlspecialchars($log['aksi']) ?></div>
                            <div class="log-meta"><?= htmlspecialchars($log['waktu']) ?> | <?= htmlspecialchars($log['user']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const chartFont = { family: 'Inter' };

        new Chart(document.getElementById('chartDokumen').getContext('2d'), {
            type: 'doughnut',
            data: { labels: <?= json_encode($dok_labels) ?>, datasets: [{ data: <?= json_encode($dok_data) ?>, backgroundColor: ['#0B427B', '#F5A623', '#10B981', '#6366F1', '#EF4444', '#8B5CF6'], borderWidth: 0, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: chartFont, boxWidth: 12, padding: 12 } } } }
        });

        new Chart(document.getElementById('chartKeterangan').getContext('2d'), {
            type: 'pie',
            data: { labels: <?= json_encode($ket_labels) ?>, datasets: [{ data: <?= json_encode($ket_data) ?>, backgroundColor: ['#10B981', '#F5A623', '#0B427B', '#EF4444'], borderWidth: 0, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: chartFont, boxWidth: 12, padding: 12 } } } }
        });

        new Chart(document.getElementById('chartRole').getContext('2d'), {
            type: 'doughnut',
            data: { labels: ['Admin', 'User'], datasets: [{ data: [<?= (int)$total_admin ?>, <?= (int)($total_users - $total_admin) ?>], backgroundColor: ['#F5A623', '#0B427B'], borderWidth: 0, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: chartFont, boxWidth: 12, padding: 12 } } } }
        });

        new Chart(document.getElementById('chartTren').getContext('2d'), {
            type: 'line',
            data: { labels: <?= json_encode($tren_labels) ?>, datasets: [{ label: 'Jumlah Permohonan', data: <?= json_encode($tren_data) ?>, borderColor: '#0B427B', backgroundColor: 'rgba(11,66,123,0.1)', fill: true, tension: 0.3, borderWidth: 2, pointBackgroundColor: '#F5A623', pointRadius: 4 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, border: { display: false }, ticks: { font: chartFont } }, x: { grid: { display: false }, ticks: { font: chartFont } } }
            }
        });

        new Chart(document.getElementById('chartLogAktivitas').getContext('2d'), {
            type: 'bar',
            data: { labels: <?= json_encode($log_labels) ?>, datasets: [{ label: 'Aktivitas', data: <?= json_encode($log_data) ?>, backgroundColor: '#6366F1', borderRadius: 6, borderWidth: 0 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, border: { display: false } }, x: { grid: { display: false }, ticks: { font: chartFont } } }
            }
        });
    </script>
</body>
</html>
