<?php
/**
 * View: master data jenis dokumen & keterangan. Variabel dari MasterDataController::index():
 * $is_admin, $dokumen_list, $ket_list
 */
$f = Session::flashGet();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244,247,250,0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1200px; margin: 0 auto; width: 100%; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); padding: 24px; }
        .card h2 { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--border-light); }
        th { color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-aktif { background: #E6F4EA; color: #107C41; }
        .badge-non { background: #FEE8E8; color: #C62828; }
        .form-inline { display: flex; gap: 8px; margin-bottom: 16px; }
        .form-inline input { flex: 1; padding: 10px 12px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.9rem; }
        .btn-sm { padding: 9px 14px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit; font-size: 0.85rem; }
        .btn-primary { background: var(--imigrasi-blue); color: white; }
        .btn-danger { background: none; color: #C62828; border: 1px solid #F0C0C0; }
        .note { font-size: 0.8rem; color: var(--text-blue-grey); margin-top: 8px; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Master Data</h1>
        </header>
        <div class="content-wrapper">
            <?php if (!$is_admin): ?>
                <div class="card" style="border-color: #F0C0C0;">
                    <h2><?= icon('lock') ?> Akses Terbatas</h2>
                    <p>Hanya admin yang bisa mengubah master data.</p>
                </div>
            <?php endif; ?>

            <div class="grid-2">
                <div class="card">
                    <h2><?= icon('file') ?> Jenis Dokumen</h2>
                    <?php if ($is_admin): ?>
                    <form method="POST" class="form-inline">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="aksi" value="dokumen_simpan">
                        <input type="text" name="kode" placeholder="Kode" required style="max-width: 90px;">
                        <input type="text" name="nama" placeholder="Nama jenis dokumen" required>
                        <button type="submit" class="btn-sm btn-primary">Tambah</button>
                    </form>
                    <?php endif; ?>
                    <table>
                        <thead><tr><th>Kode</th><th>Nama</th><th>Status</th><?php if ($is_admin): ?><th></th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($dokumen_list as $d): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($d['kode']) ?></td>
                                <td><?= htmlspecialchars($d['nama']) ?></td>
                                <td><span class="badge <?= $d['aktif'] ? 'badge-aktif' : 'badge-non' ?>"><?= $d['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                                <?php if ($is_admin && $d['aktif']): ?>
                                <td style="text-align:right;">
                                    <form method="POST" style="display:inline;">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="aksi" value="dokumen_hapus">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Nonaktifkan jenis dokumen ini?')">Nonaktifkan</button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="note">Kode dipakai di kolom <code>permohonan.dokumen</code>.</div>
                </div>

                <div class="card">
                    <h2><?= icon('tag') ?> Keterangan</h2>
                    <?php if ($is_admin): ?>
                    <form method="POST" class="form-inline">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="aksi" value="keterangan_simpan">
                        <input type="text" name="nama" placeholder="Nama keterangan" required>
                        <button type="submit" class="btn-sm btn-primary">Tambah</button>
                    </form>
                    <?php endif; ?>
                    <table>
                        <thead><tr><th>Nama</th><th>Status</th><?php if ($is_admin): ?><th></th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($ket_list as $k): ?>
                            <tr>
                                <td><?= htmlspecialchars($k['nama']) ?></td>
                                <td><span class="badge <?= $k['aktif'] ? 'badge-aktif' : 'badge-non' ?>"><?= $k['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                                <?php if ($is_admin && $k['aktif']): ?>
                                <td style="text-align:right;">
                                    <form method="POST" style="display:inline;">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="aksi" value="keterangan_hapus">
                                        <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Nonaktifkan keterangan ini?')">Nonaktifkan</button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="note">Dipilih di form input permohonan.</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
