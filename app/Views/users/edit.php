<?php
/**
 * View: edit role + jenis dokumen yang dikelola user. Variabel dari
 * UserController::edit(): $user, $dokumen_list, $akses_sekarang, $error
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; width: 100%; }
        .card { background: #fff; padding: 32px; border-radius: 16px; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 8px; background: #F8FAFC; }
        .btn-group { display: flex; gap: 10px; margin-top: 30px; }
        .btn { padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; text-align: center; flex: 1; }
        .btn-primary { background: #0B427B; color: #fff; }
        .btn-cancel { background: #E2E8F0; color: #475569; }
        .akses-list { border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px; max-height: 220px; overflow-y: auto; background: #F8FAFC; }
        .akses-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-weight: 500; cursor: pointer; font-size: 0.9rem; }
        .akses-item input { cursor: pointer; }
        .akses-hint { font-size: 0.8rem; color: #64748B; margin-top: 8px; margin-bottom: 0; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/nav.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h2 style="margin-bottom: 24px;">Edit Role &amp; Jenis Dokumen</h2>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label>Nama Pengguna</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['jabatan']) ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Pilih Role</label>
                        <select name="role" class="form-control">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User Biasa</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin (Super Admin)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Dokumen yang Dikelola</label>
                        <div id="akses-group" class="akses-list">
                            <?php foreach ($dokumen_list as $d): ?>
                            <label class="akses-item">
                                <input type="radio" name="akses_dokumen" value="<?= htmlspecialchars($d['kode']) ?>" class="akses-cb" <?= $akses_sekarang === $d['kode'] ? 'checked' : '' ?> required>
                                <span><?= htmlspecialchars($d['kode']) ?> — <?= htmlspecialchars($d['nama']) ?></span>
                            </label>
                            <?php endforeach; ?>
                            <p class="akses-hint">User biasa hanya mengelola SATU jenis dokumen permohonan. Admin otomatis mendapat akses ke semua jenis dokumen.</p>
                        </div>
                    </div>
                    <div class="btn-group">
                        <a href="kelola_akun.php" class="btn btn-cancel">Batal</a>
                        <button type="submit" name="update_role" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        const roleSel = document.querySelector('select[name="role"]');
        const aksesGroup = document.getElementById('akses-group');
        function syncAkses() {
            const isAdmin = roleSel.value === 'admin';
            document.querySelectorAll('.akses-cb').forEach(function (cb) { cb.disabled = isAdmin; cb.required = !isAdmin; });
            aksesGroup.style.opacity = isAdmin ? '0.5' : '1';
        }
        roleSel.addEventListener('change', syncAkses);
        syncAkses();
    </script>
</body>
</html>
