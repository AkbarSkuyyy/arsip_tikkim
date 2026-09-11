<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $user_input = mysqli_real_escape_string($conn, $_POST['user_input']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. Ambil data user berdasarkan email ATAU username saja (tanpa password di WHERE)
    $query = "SELECT * FROM users WHERE email = '$user_input' OR username = '$user_input'";
    $result = mysqli_query($conn, $query);

    // 2. Cek apakah user ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 3. Gunakan password_verify untuk mengecek kecocokan password yang di-hash
        if (password_verify($password, $row['password'])) {
            
            // Login Berhasil
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_nama'] = $row['nama'];
            $_SESSION['user_jabatan'] = $row['jabatan'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_role'] = $row['role']; // Penting untuk cek Admin/User
            $_SESSION['user_foto'] = $row['foto_profile'];

            // Catat log (Pastikan fungsi ini sudah didefinisikan/include)
            if (function_exists('catatLog')) {
                catatLog($conn, $_SESSION['user_nama'], "User berhasil login ke sistem.");
            }
            
            header("Location: dashboard.php");
            exit;
        } else {
            // Password salah
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        // User tidak ditemukan
        $error = "Email atau Username tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
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
            --error-red: #EF4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); color: var(--text-dark); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }

        .login-wrapper { background-color: var(--surface); width: 100%; max-width: 440px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); border: 1px solid var(--border-light); overflow: hidden; }

        .login-header { background-color: var(--imigrasi-dark); padding: 40px 30px; text-align: center; position: relative; }
        .login-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--imigrasi-gold), #D48806); }

        .logo-box { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .logo-box img { width: 100%; height: 100%; object-fit: contain; }
        .brand-main { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 700; color: #FFFFFF; letter-spacing: 0.5px; }
        .brand-sub { display: block; font-size: 0.8rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; font-weight: 600; }

        .login-body { padding: 40px 30px; }
        .login-title { font-size: 1.2rem; font-weight: 600; color: var(--imigrasi-dark); margin-bottom: 24px; text-align: center; }

        .alert-error { background-color: #FEE2E2; color: #B91C1C; padding: 12px 16px; border-radius: 8px; font-size: 0.9rem; font-weight: 500; margin-bottom: 20px; border: 1px solid #F87171; text-align: center; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 14px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; color: var(--text-dark); }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }

        .btn-login { width: 100%; padding: 14px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; font-family: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-login:hover { background-color: #08305A; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,66,123,0.2); }

        .login-footer { text-align: center; padding: 20px 30px; background-color: #F8FAFC; border-top: 1px solid var(--border-light); font-size: 0.8rem; color: var(--text-blue-grey); }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-header">
            <div class="logo-box">
                <img src="asset/img/logo.png" alt="Logo Imigrasi">
            </div>
            <h1 class="brand-main">Sistem Arsip TIKKIM</h1>
            <span class="brand-sub">Imigrasi Kelas II TPI Sampit</span>
        </div>

        <div class="login-body">
            <h2 class="login-title">Masuk ke Akun Anda</h2>
            
            <?php if ($error != ""): ?>
                <div class="alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="user_input">Email atau Username</label>
                    <input type="text" id="user_input" name="user_input" class="form-control" placeholder="Masukkan Email atau Username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="btn-login">Masuk Sistem</button>
            </form>
        </div>
        
        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> Seksi TIKKIM - Kanim Sampit
        </div>
    </div>

</body>
</html>