<?php
session_start();
require 'koneksi.php';

// 1. Cek apakah user sudah login atau belum
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek apakah dia benar-benar Admin
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']); 
    
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $cek_data = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' OR username = '$username'");
    
    if (mysqli_num_rows($cek_data) > 0) {
        $pesan_error = "Email atau Username tersebut sudah terdaftar!";
    } else {
        $query_insert = "INSERT INTO users (nama, username, jabatan, email, password, role) 
                         VALUES ('$nama', '$username', '$jabatan', '$email', '$password_hashed', '$role')";
        
        if (mysqli_query($conn, $query_insert)) {
            $pesan_sukses = "Akun berhasil dibuat dengan role: $role.";
        } else {
            $pesan_error = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Arsip SPRI</title>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-wrapper {
            background-color: var(--surface);
            width: 100%;
            max-width: 500px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-light);
            overflow: hidden;
        }

        .login-header {
            background-color: var(--imigrasi-dark);
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 4px;
            background: linear-gradient(90deg, var(--imigrasi-gold), #D48806);
        }

        .brand-main { font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 700; color: #FFFFFF; }
        
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
            
            <?php if ($pesan_sukses != ""): ?>
                <div class="alert-success"><?php echo $pesan_sukses; ?></div>
            <?php endif; ?>

            <?php if ($pesan_error != ""): ?>
                <div class="alert-error"><?php echo $pesan_error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
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
                    <input type="password" name="password" class="form-control" placeholder="Buat kata sandi" required>
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