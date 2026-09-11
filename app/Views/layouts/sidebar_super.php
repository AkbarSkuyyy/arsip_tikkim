<?php
// Sidebar khusus Super Admin — konteks navigasi terpisah dari sidebar staf
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_nama = isset($_SESSION['user_nama']) ? $_SESSION['user_nama'] : 'Admin';
$user_jabatan = isset($_SESSION['user_jabatan']) ? $_SESSION['user_jabatan'] : 'Super Admin';
$inisial = !empty($user_nama) ? strtoupper(substr($user_nama, 0, 1)) : 'A';
?>

<style>
    /* CSS Sidebar Super Admin — tema gelap ungu-emas, beda dari sidebar staf */
    .sidebar-super { width: 280px; background-color: #1E1B3A; color: #fff; display: flex; flex-direction: column; height: 100vh; flex-shrink: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.15); z-index: 10; }
    .sidebar-super-header { padding: 32px 24px; display: flex; align-items: center; gap: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); }

    .logo-box { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }
    .logo-box img { width: 100%; height: 100%; object-fit: contain; }

    .brand-title { display: flex; flex-direction: column; }
    .brand-main { font-size: 1.15rem; font-weight: 700; color: #ffffff; font-family: 'Outfit', sans-serif; }
    .brand-sub { font-size: 0.72rem; color: #F5A623; text-transform: uppercase; margin-top: 3px; font-weight: 700; letter-spacing: 0.5px; }

    .super-mode-badge { margin: 16px 24px 0; padding: 8px 14px; background: rgba(245,166,35,0.12); border: 1px solid rgba(245,166,35,0.3); border-radius: 8px; display: flex; align-items: center; gap: 8px; font-size: 0.75rem; font-weight: 700; color: #F5A623; text-transform: uppercase; letter-spacing: 0.5px; }

    .sidebar-super-nav { flex: 1; padding: 20px 16px; }
    .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 8px; }
    .nav-section-label { font-size: 0.7rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 700; padding: 16px 18px 6px; }
    .nav-item { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: 10px; color: #9CA3AF; text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: all 0.3s ease; }
    .nav-item:hover { background-color: rgba(255,255,255,0.06); color: #fff; }
    .nav-item.active { background-color: rgba(245,166,35,0.15); color: #F5A623; border-left: 4px solid #F5A623; }

    .sidebar-super-footer { padding: 24px; border-top: 1px solid rgba(255,255,255,0.08); display: flex; justify-content: space-between; align-items: center; }
    .user-avatar { width: 36px; height: 36px; background: rgba(245,166,35,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #F5A623; font-size: 0.9rem; text-transform: uppercase; }
    .btn-logout { color: #EF4444; background: rgba(239, 68, 68, 0.12); width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; }
    .btn-logout:hover { background: #EF4444; color: white; }
    .btn-back-staff { display: flex; align-items: center; gap: 10px; margin: 0 16px 16px; padding: 12px 16px; background: rgba(255,255,255,0.05); border-radius: 10px; color: #D1D5DB; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: 0.2s; }
    .btn-back-staff:hover { background: rgba(255,255,255,0.1); color: #fff; }
</style>

<aside class="sidebar-super">
    <div class="sidebar-super-header">
        <div class="logo-box">
            <img src="asset/img/logo.png" alt="Logo Imigrasi">
        </div>
        <div class="brand-title">
            <span class="brand-main">Arsip TIKKIM</span>
            <span class="brand-sub">Super Admin Panel</span>
        </div>
    </div>

    <div class="super-mode-badge">
        <?= icon('crown') ?> Mode Administrator
    </div>

    <nav class="sidebar-super-nav">
        <ul class="nav-menu">
            <li>
                <a href="dashboard_super.php" class="nav-item <?= ($GLOBALS['current_page'] == 'dashboard_super.php') ? 'active' : '' ?>">
                    <?= icon('dashboard') ?> Dashboard Analitik
                </a>
            </li>

            <li class="nav-section-label">Manajemen Data</li>
            <li>
                <a href="master_data.php" class="nav-item <?= ($GLOBALS['current_page'] == 'master_data.php') ? 'active' : '' ?>">
                    <?= icon('database') ?> Master Data
                </a>
            </li>
            <li>
                <a href="data_permohonan.php" class="nav-item <?= ($GLOBALS['current_page'] == 'data_permohonan.php' && empty($GLOBALS['current_jenis'])) ? 'active' : '' ?>">
                    <?= icon('folder') ?> Data Permohonan
                </a>
            </li>
            <?php foreach (MasterDokumen::allActive() as $md): ?>
            <li>
                <a href="data_permohonan.php?jenis=<?= urlencode($md['kode']) ?>" class="nav-item <?= ($GLOBALS['current_page'] == 'data_permohonan.php' && ($GLOBALS['current_jenis'] ?? '') === $md['kode']) ? 'active' : '' ?>" style="padding-left: 34px;">
                    <?= icon('file') ?> <?= htmlspecialchars($md['nama']) ?>
                </a>
            </li>
            <?php endforeach; ?>
            <li>
                <a href="laporan.php" class="nav-item <?= ($GLOBALS['current_page'] == 'laporan.php') ? 'active' : '' ?>">
                    <?= icon('report') ?> Laporan Bulanan
                </a>
            </li>

            <li class="nav-section-label">Pengguna & Keamanan</li>
            <li>
                <a href="kelola_akun.php" class="nav-item <?= ($GLOBALS['current_page'] == 'kelola_akun.php') ? 'active' : '' ?>">
                    <?= icon('users') ?> Kelola Akun
                </a>
            </li>
            <li>
                <a href="register.php" class="nav-item <?= ($GLOBALS['current_page'] == 'register.php') ? 'active' : '' ?>" style="padding-left: 34px;">
                    <?= icon('edit') ?> Tambah Akun
                </a>
            </li>
            <li>
                <a href="audit_trail.php" class="nav-item <?= ($GLOBALS['current_page'] == 'audit_trail.php') ? 'active' : '' ?>">
                    <?= icon('report') ?> Audit Trail
                </a>
            </li>

            <li class="nav-section-label">Sistem</li>
            <li>
                <a href="backup.php" class="nav-item <?= ($GLOBALS['current_page'] == 'backup.php') ? 'active' : '' ?>">
                    <?= icon('database') ?> Backup Database
                </a>
            </li>
            <li>
                <a href="backup_restore.php" class="nav-item <?= ($GLOBALS['current_page'] == 'backup_restore.php') ? 'active' : '' ?>">
                    <?= icon('upload') ?> Restore Database
                </a>
            </li>
            <li>
                <a href="pengaturan.php" class="nav-item <?= ($GLOBALS['current_page'] == 'pengaturan.php') ? 'active' : '' ?>">
                    <?= icon('settings') ?> Pengaturan
                </a>
            </li>
        </ul>
    </nav>

    <a href="dashboard.php" class="btn-back-staff">
        <?= icon('arrow-left') ?> Kembali ke Tampilan Staf
    </a>

    <div class="sidebar-super-footer">
        <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar" style="overflow: hidden; padding: 0;">
                <?php if (!empty($_SESSION['user_foto']) && file_exists('asset/img/profile/' . $_SESSION['user_foto'])): ?>
                    <img src="asset/img/profile/<?= htmlspecialchars($_SESSION['user_foto']) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <?= htmlspecialchars($inisial) ?>
                <?php endif; ?>
            </div>
            <div>
                <div style="font-size: 0.9rem; font-weight: 600;"><?= htmlspecialchars($user_nama) ?></div>
                <div style="font-size: 0.75rem; color: #F5A623;"><?= htmlspecialchars($user_jabatan) ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout" title="Keluar" onclick="return confirm('Yakin ingin keluar?');"><?= icon('logout') ?></a>
    </div>
</aside>
