<?php
/**
 * View: form restore database. Diakses via BackupController::restoreForm().
 */
$f = Session::flashGet();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restore Database - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); display: flex; align-items: center; gap: 10px;}
        .content-wrapper { padding: 40px; max-width: 700px; margin: 0 auto; width: 100%; }
        .card { background: var(--surface); padding: 32px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px; font-size: 0.95rem; line-height: 1.5;}
        .alert-warning { background-color: #FFFBEB; border: 1px solid #FDE68A; color: #92400E; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        input[type="file"] { width: 100%; padding: 12px; border: 2px dashed #CBD5E1; border-radius: 8px; background: #F8FAFC; cursor: pointer; }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; text-align: center; }
        .btn-danger { background: #DC2626; color: #fff; flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;}
        .btn-cancel { background: #E2E8F0; color: #475569; flex: 1; display: flex; align-items: center; justify-content: center;}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?= icon('database') ?> Restore Database</h1>
        </header>
        <div class="content-wrapper">
            <div class="card">
                <?php if ($f): ?>
                    <div class="alert" style="<?= $f['type'] === 'error' ? 'background:#FEE2E2;border-color:#F87171;color:#B91C1C;' : 'background:#D1FAE5;border-color:#34D399;color:#065F46;' ?>">
                        <?= icon($f['type'] === 'error' ? 'alert' : 'check') ?> <?= htmlspecialchars($f['message']) ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-warning">
                    <?= icon('alert') ?>
                    <div><strong>Perhatian:</strong> Restore akan <b>menimpa seluruh data</b> yang ada saat ini dengan isi file backup. Pastikan Anda memilih file <code>.sql</code> yang benar (hasil dari Backup DB).</div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label>Pilih File Backup (.sql)</label>
                        <input type="file" name="file_sql" accept=".sql" required>
                    </div>
                    <div class="btn-group">
                        <a href="dashboard_super.php" class="btn btn-cancel">Batal</a>
                        <button type="submit" name="restore" class="btn btn-danger" onclick="return confirm('Yakin restore? Data saat ini akan ditimpa!');"><?= icon('upload') ?> Pulihkan Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
