<?php
/**
 * View: form registrasi akun baru (Kelola Akun). Variabel dari
 * UserController::register(): $pesan_sukses, $pesan_error, $dokumen_list
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Akun - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); display: flex; align-items: center; gap: 10px; }
        .content-wrapper { padding: 40px; max-width: 640px; margin: 0 auto; width: 100%; }
        .card { background: var(--surface); padding: 32px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #34D399; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #F87171; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; color: var(--text-dark); box-sizing: border-box; }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        .btn-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; text-align: center; }
        .btn-primary { background: var(--imigrasi-blue); color: #fff; flex: 1; }
        .btn-primary:hover { background: #08305A; }
        .btn-cancel { background: #E2E8F0; color: #475569; }
        .akses-list { border: 1px solid var(--border-light); border-radius: 8px; padding: 12px; max-height: 220px; overflow-y: auto; background: #F8FAFC; }
        .akses-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; font-weight: 500; cursor: pointer; font-size: 0.9rem; }
        .akses-item input { cursor: pointer; }
        .akses-hint { font-size: 0.8rem; color: var(--text-blue-grey); margin-top: 8px; margin-bottom: 0; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/nav.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?= icon('users') ?> Tambah Akun</h1>
        </header>
        <div class="content-wrapper">
            <div class="card">
                <?php if ($pesan_sukses !== ""): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
                <?php endif; ?>
                <?php if ($pesan_error !== ""): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($pesan_error) ?></div>
                <?php endif; ?>

                <form action="" method="POST">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Buat username (tanpa spasi)" pattern="^\S+$" title="Username tidak boleh menggunakan spasi" required>
                    </div>
                    <div class="form-group">
                        <label>Jabatan / Peran</label>
                        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Staff TIKKIM" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@imigrasi.go.id" required>
                    </div>
                    <div class="form-group">
                        <label>Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="user">User Biasa</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Dokumen yang Dikelola</label>
                        <div id="akses-group" class="akses-list">
                            <?php foreach ($dokumen_list as $i => $d): ?>
                            <label class="akses-item">
                                <input type="radio" name="akses_dokumen" value="<?= htmlspecialchars($d['kode']) ?>" class="akses-cb" <?= $i === 0 ? 'checked' : '' ?> required>
                                <span><?= htmlspecialchars($d['kode']) ?> — <?= htmlspecialchars($d['nama']) ?></span>
                            </label>
                            <?php endforeach; ?>
                            <p class="akses-hint">User biasa hanya mengelola SATU jenis dokumen permohonan. Admin otomatis mendapat akses ke semua jenis dokumen.</p>
                        </div>
                    </div>
                    <div class="btn-group">
                        <a href="kelola_akun.php" class="btn btn-cancel">Kembali</a>
                        <button type="submit" name="register" class="btn btn-primary">Daftarkan Akun</button>
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
