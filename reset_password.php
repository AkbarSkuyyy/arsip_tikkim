<?php
session_start();
require 'koneksi.php';

// Kunci Keamanan: Hanya Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php"); exit;
}

$id = (int)($_GET['id'] ?? 0);
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'");
$user = mysqli_fetch_assoc($query);

if (!$user) { header("Location: dashboard_super.php"); exit; }

$error = "";
$sukses = "";

if (isset($_POST['reset_pw'])) {
    csrf_verify();

    $new_pw = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_password'];

    if (strlen($new_pw) < 8) {
        $error = "Password minimal 8 karakter!";
    } elseif ($new_pw !== $confirm_pw) {
        $error = "Password baru dan konfirmasi tidak cocok!";
    } else {
        $hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
        db_exec("UPDATE users SET password = ? WHERE id = ?", 'si', [$hashed_pw, $id]);
        catatLog($conn, $_SESSION['user_nama'], "Mereset password untuk user: " . $user['username']);
        $sukses = "Password berhasil diubah!";
    }
}
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

        /* Main Layout */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .container { max-width: 500px; margin: 40px auto; width: 100%; padding: 0 20px; }

        /* Card & UI */
        .card { background: var(--surface); padding: 32px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); font-size: 0.9rem; }
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; background: #F8FAFC; }

        /* Buttons */
        .btn-group { display: flex; gap: 10px; margin-top: 24px; }
        .btn { padding: 12px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; flex: 1; }
        .btn-primary { background: var(--imigrasi-blue); color: white; }
        .btn-cancel { background: var(--border-light); color: var(--text-dark); }

        /* Alerts */
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 500; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
        .alert-success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <h2 style="margin-bottom: 8px;">Reset Password</h2>
                <p style="color: #64748B; margin-bottom: 24px;">Akun: <strong><?php echo htmlspecialchars($user['nama']); ?></strong></p>

                <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>
                <?php if($sukses): ?>
                    <div class="alert alert-success">
                        <?php echo $sukses; ?> <br>
                        <a href="dashboard_super.php" style="color:#065F46; font-weight:bold;">Kembali ke Dashboard</a>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo csrf_field(); ?>
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
