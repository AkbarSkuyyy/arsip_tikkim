<?php
// Pastikan sesi aktif untuk Super Admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'User';
$user_jabatan = isset($_SESSION['user_jabatan']) ? $_SESSION['user_jabatan'] : 'Staff';
$inisial = !empty($user_nama) ? strtoupper(substr($user_nama, 0, 1)) : 'U';

// Mendeteksi apakah user adalah admin
$is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
?>

<style>
    /* CSS Sidebar */
    .sidebar { width: 280px; background-color: #0A2540; color: #fff; display: flex; flex-direction: column; height: 100vh; flex-shrink: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.08); z-index: 10; }
    .sidebar-header { padding: 32px 24px; display: flex; align-items: center; gap: 16px; border-bottom: 1px solid rgba(255,255,255,0.06); }
    
    /* Logo Styling */
    .logo-box { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }

    .brand-title { display: flex; flex-direction: column; }
    .brand-main { font-size: 1.15rem; font-weight: 700; color: #ffffff; font-family: 'Outfit', sans-serif; }
    .brand-sub { font-size: 0.75rem; color: #94A3B8; text-transform: uppercase; margin-top: 3px; font-weight: 600; }
    
    .sidebar-nav { flex: 1; padding: 24px 16px; }
    .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }
    .nav-item { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: 10px; color: #94A3B8; text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: all 0.3s ease; }
    .nav-item:hover { background-color: rgba(255,255,255,0.05); color: #fff; }
    .nav-item.active { background-color: #0B427B; color: #fff; border-left: 4px solid #F5A623; }
    
    .sidebar-footer { padding: 24px; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center; }
    .user-avatar { width: 36px; height: 36px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #F5A623; font-size: 0.9rem; text-transform: uppercase; }
    .btn-logout { color: #EF4444; background: rgba(239, 68, 68, 0.1); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; }
    .btn-logout:hover { background: #EF4444; color: white; }
</style>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <div class="logo-box">
                <img src="asset/img/logo.png" alt="Logo Imigrasi">
            </div>
        </div>
        <div class="brand-title">
            <span class="brand-main">Arsip TIKKIM</span>
            <span class="brand-sub">Imigrasi Kelas II TPI Sampit</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-menu">
            <li>
                <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <span class="icon">📊</span> Dashboard
                </a>
            </li>
            <li>
                <a href="data_permohonan.php" class="nav-item <?php echo ($current_page == 'data_permohonan.php') ? 'active' : ''; ?>">
                    <span class="icon">📁</span> Data Permohonan
                </a>
            </li>
            <li>
                <a href="import_arsip.php" class="nav-item <?php echo ($current_page == 'import_arsip.php') ? 'active' : ''; ?>">
                    <span class="icon">📥</span> Import Arsip Excel
                </a>
            </li>
            <li>
                <a href="laporan.php" class="nav-item <?php echo ($current_page == 'laporan.php') ? 'active' : ''; ?>">
                    <span class="icon">📈</span> Laporan Bulanan
                </a>
            </li>
            <li>
                <a href="pemusnahan_arsip.php" class="nav-item <?php echo ($current_page == 'pemusnahan_arsip.php') ? 'active' : ''; ?>">
                    <span class="icon">🗑️</span> Pemusnahan Arsip
                </a>
            </li>
            
            <?php if ($is_admin): ?>
            <li style="margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                <a href="dashboard_super.php" class="nav-item <?php echo ($current_page == 'dashboard_super.php') ? 'active' : ''; ?>">
                    <span class="icon">👑</span> Panel Super Admin
                </a>
            </li>
            <?php endif; ?>

            <li>
                <a href="pengaturan.php" class="nav-item <?php echo ($current_page == 'pengaturan.php') ? 'active' : ''; ?>">
                    <span class="icon">⚙️</span> Pengaturan
                </a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
    <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
        <div class="user-avatar" style="overflow: hidden; padding: 0;">
            <?php 
            // Cek apakah ada session user_foto dan apakah file tersebut ada di folder
            if (!empty($_SESSION['user_foto']) && file_exists('asset/img/profile/' . $_SESSION['user_foto'])): ?>
                <img src="asset/img/profile/<?php echo $_SESSION['user_foto']; ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <?php echo htmlspecialchars($inisial); ?>
            <?php endif; ?>
        </div>
        
        <div>
            <div style="font-size: 0.9rem; font-weight: 600;"><?php echo htmlspecialchars($user_nama); ?></div>
            <div style="font-size: 0.75rem; color: #94A3B8;"><?php echo htmlspecialchars($user_jabatan); ?></div>
        </div>
    </div>
    <a href="logout.php" class="btn-logout" title="Keluar" onclick="return confirm('Yakin ingin keluar?');">🚪</a>
</div>
</aside>