<?php
session_start();

// 1. KUNCI KEAMANAN (Cek Login)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. Validasi ketat untuk akses Super Admin menggunakan 'user_role'
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

require 'koneksi.php';

// Fungsi bantuan untuk mengambil data dengan aman (mencegah error bool given)
function get_count($conn, $query) {
    $res = mysqli_query($conn, $query);
    if ($res) {
        $data = mysqli_fetch_assoc($res);
        return $data['total'] ?? 0;
    }
    return 0;
}

// Mengambil data dengan aman
$total_data = get_count($conn, "SELECT COUNT(*) as total FROM permohonan");
$total_baru = get_count($conn, "SELECT COUNT(*) as total FROM permohonan WHERE keterangan='Baru'");
$total_ganti = get_count($conn, "SELECT COUNT(*) as total FROM permohonan WHERE keterangan='Penggantian'");

// Mengambil daftar user & logs (dengan pengecekan)
$q_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
$q_logs = mysqli_query($conn, "SELECT * FROM logs ORDER BY waktu DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Super Admin - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
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
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .brand-main { font-family: 'Outfit', sans-serif; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); position: relative; }
        .stat-title { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase; margin-bottom: 8px; }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--imigrasi-dark); }

        /* Card Container */
        .card-container { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        
        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 16px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; }
        td { padding: 16px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; font-weight: 500; }
        
        .btn-add { background: var(--imigrasi-gold); color: var(--imigrasi-dark); padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; transition: 0.2s; }
        .btn-add:hover { opacity: 0.9; transform: translateY(-2px); }

        .log-item { margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-light); }
        .log-aksi { font-weight: 600; font-size: 0.9rem; color: var(--imigrasi-blue); }
        .log-meta { font-size: 0.8rem; color: var(--text-blue-grey); margin-top: 4px; }

        /* Styling tombol aksi */
        .btn-edit, .btn-delete {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
        display: inline-block;
}

        .btn-edit {
            background: #3B82F6; /* Biru */
            color: #fff;
            margin-right: 5px;
        }

        .btn-delete {
            background: #EF4444; /* Merah */
            color: #fff;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">👑 Panel Super Admin</h1>
        </header>

        <div class="content-wrapper">
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-title">Total Permohonan</div>
                    <div class="stat-value"><?php echo $total_data; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Permohonan Baru</div>
                    <div class="stat-value"><?php echo $total_baru; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Penggantian</div>
                    <div class="stat-value"><?php echo $total_ganti; ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <div class="card-container">
                    <div class="card-header">
                        <h2>Daftar Pengguna Sistem</h2>
                        <div>
                            <a href="backup.php" class="btn-backup" style="background: #10B981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-right: 10px;">💾 Backup DB</a>
                            <a href="register.php" class="btn-add">+ Tambah Akun</a>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr><th>NAMA</th><th>USERNAME</th><th>JABATAN</th></tr>
                        </thead>
                        <tbody>
                            <?php while($usr = mysqli_fetch_assoc($q_users)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usr['nama']); ?></td>
                                <td><?php echo htmlspecialchars($usr['username']); ?></td>
                                <td><?php echo htmlspecialchars($usr['jabatan']); ?></td>
                                <td><strong><?php echo htmlspecialchars($usr['role']); ?></strong></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $usr['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="reset_password.php?id=<?php echo $usr['id']; ?>" class="btn-reset" style="background:#6366F1; color:white; padding:6px 12px; border-radius:6px; font-size:0.8rem; text-decoration:none;">Reset Pass</a>
                                    <a href="hapus_user.php?id=<?php echo $usr['id']; ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="btn-delete">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-container">
                    <h2>Log Aktivitas</h2>
                    <div style="margin-top: 20px;">
                        <?php while($log = mysqli_fetch_assoc($q_logs)): ?>
                        <div class="log-item">
                            <div class="log-aksi"><?php echo htmlspecialchars($log['aksi']); ?></div>
                            <div class="log-meta"><?php echo htmlspecialchars($log['waktu']); ?> | <?php echo htmlspecialchars($log['user']); ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>