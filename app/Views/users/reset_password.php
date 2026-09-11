<?php
/**
 * View: reset password user. Variabel dari UserController::resetPassword():
 * $user, $error, $sukses
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .container { max-width: 500px; margin: 40px auto; width: 100%; padding: 0 20px; }
        .card { background: var(--surface); padding: 32px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); font-size: 0.9rem; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; background: #F8FAFC; }
        .btn-group { display: flex; gap: 10px; margin-top: 24px; }
        .btn { padding: 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; flex: 1; }
        .btn-primary { background: var(--imigrasi-blue); color: white; }
        .btn-cancel { background: var(--border-light); color: var(--text-dark); }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/nav.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h2 style="margin-bottom: 8px;">Reset Password</h2>
                <p style="color: #64748B; margin-bottom: 24px;">Akun: <strong><?= htmlspecialchars($user['nama']) ?></strong></p>

                <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($sukses): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($sukses) ?> <br>
                        <a href="dashboard_super.php" style="color:#065F46; font-weight:bold;">Kembali ke Dashboard</a>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8" placeholder="Ulangi password">
                    </div>
                    <div class="btn-group">
                        <a href="dashboard_super.php" class="btn btn-cancel">Batal</a>
                        <button type="submit" name="reset_pw" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
