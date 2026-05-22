<?php
// Pastikan sesi aktif untuk Super Admin
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

$current_page = basename($_SERVER['SCRIPT_NAME']);

// Data User Super Admin
$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'Super Admin';
$user_jabatan = 'Administrator Sistem';
$inisial = substr($user_nama, 0, 1);
?>

<style>
    .sidebar { 
        width: 280px; 
        background-color: #1E293B;
        color: #fff; 
        display: flex; 
        flex-direction: column; 
        box-shadow: 4px 0 24px rgba(0,0,0,0.15); 
        z-index: 10; 
        height: 100vh;
        /* Hapus overflow-y: auto; untuk menghilangkan scrollbar */
        overflow-y: hidden; 
    }
    
    .sidebar-header { padding: 32px 24px; display: flex; align-items: center; gap: 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .brand-main { font-size: 1.15rem; font-weight: 700; color: #F5A623; }
    .brand-sub { font-size: 0.75rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; }

    .sidebar-nav { flex: 1; padding: 24px 16px; }
    .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }
    .nav-item { 
        display: flex; align-items: center; gap: 14px; padding: 14px 18px; 
        border-radius: 10px; color: #CBD5E1; text-decoration: none; 
        font-weight: 500; transition: all 0.3s;
    }
    .nav-item:hover { background-color: rgba(255,255,255,0.1); color: #fff; }
    .nav-item.active { background-color: #0B427B; color: #fff; border-left: 4px solid #F5A623; }

    .sidebar-footer { padding: 24px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; }
    .btn-logout { color: #EF4444; background: rgba(239, 68, 68, 0.1); padding: 8px; border-radius: 8px; display: flex; }
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="brand-title">
            <span class="brand-main">SUPER ADMIN</span>
            <span class="brand-sub">Panel Kendali Penuh</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <li><a href="dashboard_super.php" class="nav-item <?php echo ($current_page == 'dashboard_super.php') ? 'active' : ''; ?>">📊 Dashboard Super</a></li>
            <li><a href="manajemen_user.php" class="nav-item <?php echo ($current_page == 'manajemen_user.php') ? 'active' : ''; ?>">👥 Kelola Pengguna</a></li>
            <li><a href="data_master.php" class="nav-item <?php echo ($current_page == 'data_master.php') ? 'active' : ''; ?>">📂 Data Master</a></li>
            <li><a href="log_aktivitas.php" class="nav-item <?php echo ($current_page == 'log_aktivitas.php') ? 'active' : ''; ?>">📋 Log Aktivitas</a></li>
            <li><a href="pengaturan_sistem.php" class="nav-item <?php echo ($current_page == 'pengaturan_sistem.php') ? 'active' : ''; ?>">⚙️ Pengaturan Sistem</a></li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-name" style="font-size: 0.9rem;"><strong><?php echo htmlspecialchars($user_nama); ?></strong></div>
            <div class="user-role" style="font-size: 0.75rem; color: #94A3B8;">Administrator</div>
        </div>
        <a href="logout.php" class="btn-logout" title="Keluar" onclick="return confirm('Yakin ingin keluar?');">🚪</a>
    </div>
</aside>