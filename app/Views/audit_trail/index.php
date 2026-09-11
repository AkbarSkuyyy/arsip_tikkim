<?php
/**
 * View: audit trail lengkap dengan filter + pagination. Variabel dari
 * AuditTrailController::index(): $cari, $f_user, $tgl_mulai, $tgl_selesai,
 * $halaman, $total_halaman, $total_rows, $rows, $daftar_user
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244,247,250,0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); display: flex; align-items: center; gap: 10px; }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .panel-filter { background: var(--surface); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px; padding: 20px 24px; }
        .filter-form { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.8rem; font-weight: 600; color: var(--text-blue-grey); }
        .form-control { padding: 10px 14px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #F8FAFC; outline: none; transition: 0.2s; min-width: 160px; }
        .form-control:focus { border-color: var(--imigrasi-blue); }
        .btn { padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: var(--imigrasi-blue); color: white; }
        .btn-reset { background: var(--surface); border: 1px solid var(--border-light); color: var(--text-blue-grey); }
        .info-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .info-text { font-size: 0.9rem; color: var(--text-blue-grey); }
        .info-text strong { color: var(--imigrasi-dark); }
        .card-container { background: var(--surface); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); padding: 0; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { background: #F8FAFC; color: var(--text-blue-grey); font-weight: 600; padding: 14px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1px solid var(--border-light); }
        td { padding: 14px 20px; border-bottom: 1px solid var(--border-light); color: var(--text-dark); }
        tbody tr:hover { background: #F8FAFC; }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .badge-hapus { background: #FEE8E8; color: #C62828; }
        .badge-tambah { background: #E6F4EA; color: #107C41; }
        .badge-ubah { background: #FFF4E0; color: #D48806; }
        .badge-login { background: #E8F0FE; color: #1967D2; }
        .badge-logout { background: #F1F0FE; color: #6B4FBB; }
        .badge-import { background: #E0F7FA; color: #00838F; }
        .badge-backup { background: #E6F4EA; color: #10B981; }
        .badge-restore { background: #EEE8F9; color: #6366F1; }
        .badge-lain { background: #F1F5F9; color: #64748B; }
        .waktu-cell { color: var(--text-blue-grey); white-space: nowrap; }
        .user-cell { font-weight: 600; }
        .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px; }
        .page-link { padding: 8px 14px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; color: var(--text-blue-grey); background: #F8FAFC; border: 1px solid var(--border-light); }
        .page-link:hover { background: var(--border-light); }
        .page-link.active { background: var(--imigrasi-blue); color: white; border-color: var(--imigrasi-blue); }
        .page-link.disabled { opacity: 0.4; pointer-events: none; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-blue-grey); }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/sidebar_super.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?= icon('report') ?> Audit Trail</h1>
        </header>
        <div class="content-wrapper">
            <div class="panel-filter">
                <form method="GET" action="audit_trail.php" class="filter-form">
                    <div class="form-group" style="flex: 1; min-width: 220px;">
                        <label>Cari Aksi</label>
                        <input type="text" name="cari" class="form-control" placeholder="Ketik kata kunci aksi..." value="<?= htmlspecialchars($cari) ?>">
                    </div>
                    <div class="form-group">
                        <label>Pengguna</label>
                        <select name="user" class="form-control">
                            <option value="">Semua Pengguna</option>
                            <?php foreach ($daftar_user as $u): ?>
                            <option value="<?= htmlspecialchars($u) ?>" <?= $f_user === $u ? 'selected' : '' ?>><?= htmlspecialchars($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary"><?= icon('search') ?> Terapkan</button>
                    <a href="audit_trail.php" class="btn btn-reset"><?= icon('refresh') ?> Reset</a>
                </form>
            </div>

            <div class="info-bar">
                <div class="info-text">Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= (int)$total_rows ?></strong> log aktivitas</div>
                <div class="info-text">Halaman <strong><?= $halaman ?></strong> dari <strong><?= $total_halaman ?></strong></div>
            </div>

            <div class="card-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 180px;">WAKTU</th>
                            <th style="width: 180px;">PENGGUNA</th>
                            <th style="width: 110px;">KATEGORI</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rows) > 0): foreach ($rows as $row): [$label, $class] = Log::kategoriAksi($row['aksi']); ?>
                        <tr>
                            <td>#<?= (int)$row['id'] ?></td>
                            <td class="waktu-cell"><?= date('d/m/Y H:i:s', strtotime($row['waktu'])) ?></td>
                            <td class="user-cell"><?= htmlspecialchars($row['user'] !== '' ? $row['user'] : '(tidak diketahui)') ?></td>
                            <td><span class="badge <?= $class ?>"><?= $label ?></span></td>
                            <td><?= htmlspecialchars($row['aksi']) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="empty-state">Tidak ada log yang cocok dengan filter ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_halaman > 1): ?>
                <div class="pagination">
                    <?php
                    $qs = $_GET;
                    $qs['halaman'] = max(1, $halaman - 1);
                    $prev_url = 'audit_trail.php?' . http_build_query($qs);
                    $qs['halaman'] = min($total_halaman, $halaman + 1);
                    $next_url = 'audit_trail.php?' . http_build_query($qs);
                    ?>
                    <a href="<?= $prev_url ?>" class="page-link <?= $halaman <= 1 ? 'disabled' : '' ?>">&laquo; Sebelumnya</a>
                    <?php
                    $start = max(1, $halaman - 2);
                    $end = min($total_halaman, $halaman + 2);
                    for ($p = $start; $p <= $end; $p++):
                        $qs['halaman'] = $p;
                        $url = 'audit_trail.php?' . http_build_query($qs);
                    ?>
                    <a href="<?= $url ?>" class="page-link <?= $p == $halaman ? 'active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <a href="<?= $next_url ?>" class="page-link <?= $halaman >= $total_halaman ? 'disabled' : '' ?>">Selanjutnya &raquo;</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
