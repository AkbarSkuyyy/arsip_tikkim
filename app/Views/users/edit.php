<?php
/**
 * View: edit role user. Variabel dari UserController::edit(): $user
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="container">
            <div class="card">
                <h2 style="margin-bottom: 24px;">Edit Role Pengguna</h2>
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
                    <div class="btn-group">
                        <a href="dashboard_super.php" class="btn btn-cancel">Batal</a>
                        <button type="submit" name="update_role" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
