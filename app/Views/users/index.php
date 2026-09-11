<?php
/**
 * View: Kelola Akun — daftar pengguna + informasi akun.
 * Variabel dari UserController::index(): $users, $dokumen_list
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: space-between; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); display: flex; align-items: center; gap: 10px; }
        .content-wrapper { padding: 32px 40px; max-width: 1300px; margin: 0 auto; width: 100%; }
        .card { background: var(--surface); padding: 28px; border-radius: 16px; border: 1px solid var(--border-light); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin: 0; }
        .btn-add { background: var(--imigrasi-gold); color: var(--imigrasi-dark); padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-add:hover { opacity: 0.9; transform: translateY(-1px); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.78rem; padding: 14px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; text-align: left; white-space: nowrap; }
        td { padding: 14px; border-bottom: 1px solid var(--border-light); font-size: 0.92rem; font-weight: 500; }
        tbody tr:hover { background: rgba(244, 247, 250, 0.5); }
        .role-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .role-admin { background: rgba(245, 166, 35, 0.15); color: #D48806; }
        .role-user { background: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); }
        .akses-chip { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 600; background: rgba(99, 102, 241, 0.12); color: #4F46E5; margin: 1px; }
        .akses-chip.semua { background: rgba(245, 166, 35, 0.15); color: #D48806; }
        .btn-edit, .btn-delete, .btn-reset { padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: 0.2s; display: inline-block; border: none; cursor: pointer; white-space: nowrap; }
        .btn-edit { background: #3B82F6; color: #fff; margin-right: 5px; }
        .btn-reset { background: #6366F1; color: white; margin-right: 5px; }
        .btn-delete { background: #EF4444; color: #fff; }
        .btn-edit:hover, .btn-delete:hover, .btn-reset:hover { opacity: 0.8; }
        .avatar-cell { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--imigrasi-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/nav.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title"><?= icon('users') ?> Kelola Akun</h1>
        </header>
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Daftar Pengguna Sistem (<?= count($users) ?>)</h2>
                    <a href="register.php" class="btn-add"><?= icon('users') ?> Tambah Akun</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Jabatan</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Akses Dokumen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $dok_map = []; foreach ($dokumen_list as $d) $dok_map[$d['kode']] = $d['nama']; ?>
                            <?php foreach ($users as $usr): ?>
                            <tr>
                                <td>
                                    <div class="avatar-cell">
                                        <div class="avatar"><?= htmlspecialchars(strtoupper(substr((string)$usr['nama'], 0, 1))) ?></div>
                                        <span style="font-weight:700; color:var(--imigrasi-dark);"><?= htmlspecialchars($usr['nama']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($usr['username']) ?></td>
                                <td><?= htmlspecialchars($usr['jabatan']) ?></td>
                                <td style="color:var(--text-blue-grey);"><?= htmlspecialchars($usr['email']) ?></td>
                                <td><span class="role-badge <?= $usr['role'] === 'admin' ? 'role-admin' : 'role-user' ?>"><?= htmlspecialchars($usr['role']) ?></span></td>
                                <td>
                                    <?php if (($usr['akses_dokumen'] ?? '*') === '*'): ?>
                                        <span class="akses-chip semua">Semua</span>
                                    <?php else: ?>
                                        <?php $akses_arr = array_filter(array_map('trim', explode(',', (string)($usr['akses_dokumen'] ?? ''))), 'strlen'); ?>
                                        <?php if (empty($akses_arr)): ?>
                                            <span class="akses-chip">-</span>
                                        <?php else: ?>
                                            <?php foreach ($akses_arr as $k): ?>
                                                <span class="akses-chip"><?= htmlspecialchars($dok_map[$k] ?? $k) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_user.php?id=<?= (int)$usr['id'] ?>" class="btn-edit">Edit</a>
                                    <a href="reset_password.php?id=<?= (int)$usr['id'] ?>" class="btn-reset">Reset Pass</a>
                                    <form method="POST" action="hapus_user.php" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        <input type="hidden" name="id" value="<?= (int)$usr['id'] ?>">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="btn-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
