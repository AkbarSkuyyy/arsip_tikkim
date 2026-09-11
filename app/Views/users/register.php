<?php
/**
 * View: form registrasi akun baru. Variabel dari UserController::register():
 * $pesan_sukses, $pesan_error
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .login-wrapper { background-color: var(--surface); width: 100%; max-width: 500px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); border: 1px solid var(--border-light); overflow: hidden; }
        .login-header { background-color: var(--imigrasi-dark); padding: 30px; text-align: center; position: relative; }
        .login-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--imigrasi-gold), #D48806); }
        .brand-main { font-size: 1.4rem; font-weight: 700; color: #FFFFFF; }
        .login-body { padding: 40px 30px; }
        .login-title { font-size: 1.2rem; font-weight: 600; color: var(--imigrasi-dark); margin-bottom: 24px; text-align: center; }
        .alert-success { background-color: #D1FAE5; color: #065F46; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; border: 1px solid #34D399; text-align: center; }
        .alert-error { background-color: #FEE2E2; color: #B91C1C; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; border: 1px solid #F87171; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; color: var(--text-dark); }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        .btn-login { width: 100%; padding: 14px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; font-family: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-login:hover { background-color: #08305A; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,66,123,0.2); }
        .login-footer { text-align: center; padding: 20px 30px; background-color: #F8FAFC; border-top: 1px solid var(--border-light); font-size: 0.9rem; color: var(--text-blue-grey); }
        .login-footer a { color: var(--imigrasi-blue); font-weight: 600; text-decoration: none; }
        .login-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-header">
            <h1 class="brand-main">Tambah Akun Pengguna</h1>
        </div>
        <div class="login-body">
            <h2 class="login-title">Formulir Registrasi</h2>

            <?php if ($pesan_sukses !== ""): ?>
                <div class="alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
            <?php endif; ?>
            <?php if ($pesan_error !== ""): ?>
                <div class="alert-error"><?= htmlspecialchars($pesan_error) ?></div>
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
                <button type="submit" name="register" class="btn-login">Daftarkan Akun</button>
            </form>
        </div>
        <div class="login-footer">
            <a href="dashboard_super.php">Kembali</a>
        </div>
    </div>
</body>
</html>
