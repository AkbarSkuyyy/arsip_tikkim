<?php
/**
 * View: kelola arsip permohonan (CRUD modal + filter bulan/tahun + pagination).
 * Variabel dari PermohonanController::index(): $bulan_pilihan, $tahun_pilihan, $keyword,
 * $nama_bulan, $rekap, $batas_per_halaman, $halaman_aktif, $halaman_awal,
 * $total_semua_data, $total_halaman, $data_rows
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Permohonan - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }
        .content { flex: 1; padding: 30px; overflow-x: hidden; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }
        .card-container { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px;}
        .filter-form { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase; }
        .form-group input, .form-group select { padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; width: 220px; font-size: 0.95rem; outline: none; background: var(--bg-main); color: var(--text-dark); font-family: 'Inter', sans-serif;}
        .form-group input:focus, .form-group select:focus { border-color: var(--imigrasi-blue); background: var(--surface);}
        .btn-cari { padding: 12px 24px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); height: 44px; display: inline-flex; align-items: center; gap: 8px; }
        .btn-cari:hover { background-color: #08305A; transform: translateY(-2px); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); position: relative; overflow: hidden; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--imigrasi-blue); }
        .stat-card:nth-child(2)::before { background: #10B981; }
        .stat-card:nth-child(3)::before { background: var(--imigrasi-gold); }
        .stat-card:nth-child(4)::before { background: #06B6D4; }
        .stat-title { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; margin-bottom: 10px; text-transform: uppercase;}
        .stat-value { font-size: 2.2rem; font-weight: 700; color: var(--imigrasi-dark); line-height: 1; }
        .stat-sub { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 500; margin-top: 8px;}
        .table-title { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;}
        .btn-tambah { background: var(--imigrasi-blue); color: white; padding: 10px 18px; border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); cursor: pointer; font-family: 'Inter', sans-serif; display: inline-flex; align-items: center; gap: 8px;}
        .btn-tambah:hover { background: #08305A; transform: translateY(-1px); }
        .table-responsive { overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 16px 15px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 16px 15px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; font-weight: 500; color: var(--text-dark); }
        tbody tr:hover { background-color: rgba(244, 247, 250, 0.5); }
        .badge { background-color: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; text-align: center;}
        .badge-baru { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
        .badge-ganti { background-color: rgba(245, 166, 35, 0.1); color: #D48806; }
        .badge-jk { background-color: var(--bg-main); color: var(--text-blue-grey); }
        .action-flex { display: flex; gap: 8px; justify-content: center; }
        .btn-aksi { padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-family: 'Inter', sans-serif;}
        .btn-edit { background: rgba(11, 66, 123, 0.05); color: var(--imigrasi-blue); border-color: rgba(11, 66, 123, 0.2); }
        .btn-edit:hover { background: var(--imigrasi-blue); color: white; }
        .btn-hapus { background: rgba(239, 68, 68, 0.05); color: #EF4444; border-color: rgba(239, 68, 68, 0.2); }
        .btn-hapus:hover { background: #EF4444; color: white; }
        .pagination-container { display: flex; justify-content: space-between; align-items: center; margin-top: 24px; flex-wrap: wrap; gap: 15px; }
        .pagination-info { font-size: 0.9rem; color: var(--text-blue-grey); font-weight: 500;}
        .pagination-nav { display: flex; gap: 8px; list-style: none; }
        .page-link { padding: 8px 16px; border: 1px solid var(--border-light); background: var(--surface); color: var(--text-dark); text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; transition: all 0.2s; }
        .page-link:hover:not(.disabled):not(.active) { background-color: var(--bg-main); }
        .page-link.active { background-color: var(--imigrasi-blue); color: #fff; border-color: var(--imigrasi-blue); }
        .page-link.disabled { color: var(--text-blue-grey); opacity: 0.5; pointer-events: none; background-color: var(--bg-main); border-color: var(--border-light);}
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 37, 64, 0.6); backdrop-filter: blur(4px); display: none; justify-content: center; align-items: center; z-index: 9999; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-container { background: var(--surface); width: 90%; max-width: 700px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); transform: translateY(-20px); transition: transform 0.3s ease; display: flex; flex-direction: column; max-height: 90vh; }
        .modal-overlay.active .modal-container { transform: translateY(0); }
        .modal-header { padding: 24px 30px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.2rem; font-weight: 700; color: var(--imigrasi-dark); font-family: 'Outfit', sans-serif; display: flex; align-items: center; gap: 10px;}
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--text-blue-grey); cursor: pointer; transition: 0.2s; }
        .modal-close:hover { color: #EF4444; }
        .modal-body { padding: 30px; overflow-y: auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group-modal { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;}
        .form-group-modal.full-width { grid-column: 1 / -1; }
        .form-group-modal label { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase;}
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.95rem; outline: none; background: var(--bg-main); color: var(--text-dark); font-family: 'Inter', sans-serif; transition: 0.2s;}
        .form-control:focus { border-color: var(--imigrasi-blue); background: var(--surface); box-shadow: 0 0 0 3px rgba(11,66,123,0.1);}
        .modal-footer { padding: 20px 30px; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 15px; background: var(--bg-main); border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;}
        .btn-cancel { padding: 10px 24px; background: var(--surface); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer; font-weight: 600; color: var(--text-blue-grey); transition: 0.2s;}
        .btn-cancel:hover { background: #E2E8F0; color: var(--text-dark); }
        .btn-save { padding: 10px 24px; background: var(--imigrasi-blue); border: none; border-radius: 8px; cursor: pointer; font-weight: 600; color: white; transition: 0.2s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); display: inline-flex; align-items: center; gap: 8px;}
        .btn-save:hover { background: #08305A; transform: translateY(-1px); }
        .icon svg { width: 16px; height: 16px; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include __DIR__ . '/../layouts/nav.php'; ?>
        <div class="content">
            <header class="top-header">
                <h1 class="page-title">Kelola Arsip Permohonan</h1>
            </header>
            <div class="content-wrapper">
                <div class="card-container">
                    <form action="" method="GET" class="filter-form">
                        <div class="form-group">
                            <label>Cari Pemohon</label>
                            <input type="text" name="cari" value="<?= htmlspecialchars($keyword) ?>" placeholder="Ketik nama di sini..." style="width: 250px;">
                        </div>
                        <div class="form-group">
                            <label>Bulan Laporan</label>
                            <select name="bulan">
                                <?php foreach ($nama_bulan as $key => $value): ?>
                                    <option value="<?= $key ?>" <?= $bulan_pilihan == $key ? 'selected' : '' ?>><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun Laporan</label>
                            <select name="tahun">
                                <?php $tahun_sekarang = date('Y'); for ($i = $tahun_sekarang + 1; $i >= $tahun_sekarang - 5; $i--): ?>
                                    <option value="<?= $i ?>" <?= $tahun_pilihan == $i ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-cari"><?= icon('search') ?> Terapkan Filter</button>
                    </form>
                </div>

                <h2 style="font-size: 1.1rem; color: var(--imigrasi-dark); margin-bottom: 16px;">
                    Rekapitulasi: <?= (!empty($keyword) ? 'Pencarian "'.htmlspecialchars($keyword).'" di ' : '') . $nama_bulan[$bulan_pilihan] . ' ' . $tahun_pilihan ?>
                </h2>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Pemohon</div>
                        <div class="stat-value"><?= $total_semua_data ?></div>
                        <div class="stat-sub">Dokumen SPRI</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Permohonan Baru</div>
                        <div class="stat-value"><?= (int)$rekap['total_baru'] ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Penggantian HB</div>
                        <div class="stat-value"><?= (int)$rekap['total_penggantian'] ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Rasio Jenis Kelamin</div>
                        <div class="stat-value" style="font-size: 1.5rem; line-height: 1.4;">
                            <span style="color: var(--imigrasi-blue);">L: <?= (int)$rekap['total_laki'] ?></span> |
                            <span style="color: var(--imigrasi-gold);">P: <?= (int)$rekap['total_perempuan'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-container" style="padding: 0;">
                    <div class="table-title" style="padding: 24px 28px 0 28px;">
                        Rincian Pemohon Paspor
                        <button onclick="openModalTambah()" class="btn-tambah"><?= icon('edit') ?> Tambah Data</button>
                    </div>
                    <div class="table-responsive">
                        <table style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="padding-left: 28px; width: 50px;">NO</th>
                                    <th>NAMA LENGKAP</th>
                                    <th>TANGGAL LAHIR</th>
                                    <th style="text-align: center;">GENDER</th>
                                    <th style="text-align: center;">DOKUMEN</th>
                                    <th>STATUS</th>
                                    <th style="text-align: center; padding-right: 28px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($data_rows) > 0): $no = $halaman_awal + 1; foreach ($data_rows as $row):
                                    $tgl_tampil = !empty($row['tgl_lahir']) ? date('d-m-Y', strtotime($row['tgl_lahir'])) : '-';
                                    $tgl_edit = !empty($row['tgl_lahir']) ? $row['tgl_lahir'] : '';
                                    $badge_class = (strpos(strtolower($row['keterangan']), 'baru') !== false) ? 'badge badge-baru' : 'badge badge-ganti';
                                ?>
                                    <tr>
                                        <td style="padding-left: 28px; color: var(--text-blue-grey); font-weight: 600;"><?= $no++ ?></td>
                                        <td style="font-weight: 700; color: var(--imigrasi-dark);"><?= htmlspecialchars($row['nama_pemohon']) ?></td>
                                        <td><?= $tgl_tampil ?></td>
                                        <td style="text-align: center;"><span class="badge badge-jk"><?= htmlspecialchars($row['jenis_kelamin']) ?></span></td>
                                        <td style="text-align: center; color: var(--text-blue-grey);"><?= htmlspecialchars($row['dokumen']) ?></td>
                                        <td><span class="<?= $badge_class ?>"><?= htmlspecialchars($row['keterangan']) ?></span></td>
                                        <td style="padding-right: 28px;">
                                            <div class="action-flex">
                                                <button onclick="openModalEdit('<?= $row['id'] ?>', '<?= htmlspecialchars($row['nama_pemohon'], ENT_QUOTES) ?>', '<?= $tgl_edit ?>', '<?= $row['jenis_kelamin'] ?>', '<?= $row['dokumen'] ?>', '<?= $row['keterangan'] ?>', '<?= $row['tanggal_input'] ?>')" class="btn-aksi btn-edit"><?= icon('edit') ?> Edit</button>
                                                <form method="POST" action="data_permohonan.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus arsip atas nama: <?= htmlspecialchars($row['nama_pemohon'], ENT_QUOTES) ?>?');">
                                                    <input type="hidden" name="hapus_id" value="<?= $row['id'] ?>">
                                                    <?= Csrf::field() ?>
                                                    <button type="submit" class="btn-aksi btn-hapus"><?= icon('trash') ?> Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 60px 20px; color: var(--text-blue-grey);">
                                            <div style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.5; display: inline-flex;"><?= icon('folder') ?></div>
                                            <br>Belum ada data arsip untuk bulan <?= $nama_bulan[$bulan_pilihan] . ' ' . $tahun_pilihan ?>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_halaman > 0): ?>
                    <div class="pagination-container" style="padding: 0 28px 24px 28px;">
                        <div class="pagination-info">Menampilkan <b><?= ($total_semua_data == 0) ? 0 : $halaman_awal + 1 ?></b> - <b><?= min($halaman_awal + $batas_per_halaman, $total_semua_data) ?></b> dari <b><?= $total_semua_data ?></b> pemohon</div>
                        <ul class="pagination-nav">
                            <?php if ($halaman_aktif > 1): ?>
                                <li><a class="page-link" href="?cari=<?= urlencode($keyword) ?>&bulan=<?= $bulan_pilihan ?>&tahun=<?= $tahun_pilihan ?>&halaman=<?= $halaman_aktif - 1 ?>">&laquo; Prev</a></li>
                            <?php else: ?>
                                <li><span class="page-link disabled">&laquo; Prev</span></li>
                            <?php endif; ?>
                            <?php
                            $start_number = max(1, $halaman_aktif - 2);
                            $end_number = min($total_halaman, $halaman_aktif + 2);
                            if ($start_number > 1) {
                                echo '<li><a class="page-link" href="?cari='.urlencode($keyword).'&bulan='.$bulan_pilihan.'&tahun='.$tahun_pilihan.'&halaman=1">1</a></li>';
                                if ($start_number > 2) echo '<li><span class="page-link disabled">...</span></li>';
                            }
                            for ($i = $start_number; $i <= $end_number; $i++):
                            ?>
                                <li><a class="page-link <?= ($halaman_aktif == $i) ? 'active' : '' ?>" href="?cari=<?= urlencode($keyword) ?>&bulan=<?= $bulan_pilihan ?>&tahun=<?= $tahun_pilihan ?>&halaman=<?= $i ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php
                            if ($end_number < $total_halaman) {
                                if ($end_number < $total_halaman - 1) echo '<li><span class="page-link disabled">...</span></li>';
                                echo '<li><a class="page-link" href="?cari='.urlencode($keyword).'&bulan='.$bulan_pilihan.'&tahun='.$tahun_pilihan.'&halaman='.$total_halaman.'">'.$total_halaman.'</a></li>';
                            }
                            ?>
                            <?php if ($halaman_aktif < $total_halaman): ?>
                                <li><a class="page-link" href="?cari=<?= urlencode($keyword) ?>&bulan=<?= $bulan_pilihan ?>&tahun=<?= $tahun_pilihan ?>&halaman=<?= $halaman_aktif + 1 ?>">Next &raquo;</a></li>
                            <?php else: ?>
                                <li><span class="page-link disabled">Next &raquo;</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalTambah">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title"><?= icon('edit') ?> Tambah Pemohon SPRI</div>
                <button type="button" class="modal-close" onclick="closeModalTambah()">&times;</button>
            </div>
            <form action="" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group-modal full-width">
                            <label>Nama Lengkap Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Gender --</option>
                                <option value="L">Laki-Laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label>Jumlah Dokumen</label>
                            <input type="number" name="dokumen" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Status Keterangan</label>
                            <select name="keterangan" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Keterangan --</option>
                                <option value="Baru">Baru</option>
                                <option value="Penggantian HB">Penggantian HB</option>
                                <option value="Perubahan Nama">Perubahan Nama</option>
                            </select>
                        </div>
                        <div class="form-group-modal full-width" style="background: rgba(11, 66, 123, 0.05); padding: 15px; border-radius: 8px; border: 1px dashed var(--imigrasi-blue); margin-top: 10px;">
                            <label style="color: var(--imigrasi-blue);">Tanggal Masuk Arsip</label>
                            <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalTambah()">Batal</button>
                    <button type="submit" name="simpan" class="btn-save"><?= icon('save') ?> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEdit">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title"><?= icon('edit') ?> Edit Data Pemohon</div>
                <button type="button" class="modal-close" onclick="closeModalEdit()">&times;</button>
            </div>
            <form action="" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body">
                    <div class="form-grid">
                        <input type="hidden" name="id_permohonan" id="edit_id">
                        <div class="form-group-modal full-width">
                            <label>Nama Lengkap Pemohon</label>
                            <input type="text" name="nama_pemohon" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" id="edit_tgl_lahir" class="form-control" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="edit_jk" class="form-control" required>
                                <option value="L">Laki-Laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label>Jumlah Dokumen</label>
                            <input type="number" name="dokumen" id="edit_dokumen" class="form-control" min="1" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Status Keterangan</label>
                            <select name="keterangan" id="edit_keterangan" class="form-control" required>
                                <option value="Baru">Baru</option>
                                <option value="Penggantian HB">Penggantian HB</option>
                                <option value="Perubahan Nama">Perubahan Nama</option>
                            </select>
                        </div>
                        <div class="form-group-modal full-width" style="background: rgba(245, 166, 35, 0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--imigrasi-gold); margin-top: 10px;">
                            <label style="color: #D48806;">Pindah Bulan Laporan (Tanggal Input)</label>
                            <input type="date" name="tanggal_input" id="edit_tgl_input" class="form-control" required>
                            <small style="color: var(--text-blue-grey); margin-top: 5px; display: block;">*Ubah tanggal ini jika Anda ingin memindahkan data pemohon ini ke laporan bulan lain.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalEdit()">Batal</button>
                    <button type="submit" name="update" class="btn-save" style="background: #D48806;"><?= icon('refresh') ?> Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <?= flash_toast_script() ?>
    <script src="asset/js/toast.js"></script>
    <script>
        const mTambah = document.getElementById('modalTambah');
        const mEdit = document.getElementById('modalEdit');
        function openModalTambah() { mTambah.classList.add('active'); }
        function closeModalTambah() { mTambah.classList.remove('active'); }
        function openModalEdit(id, nama, tglLahir, jk, dok, ket, tglInput) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_tgl_lahir').value = tglLahir;
            document.getElementById('edit_jk').value = jk;
            document.getElementById('edit_dokumen').value = dok;
            document.getElementById('edit_keterangan').value = ket;
            document.getElementById('edit_tgl_input').value = tglInput;
            mEdit.classList.add('active');
        }
        function closeModalEdit() { mEdit.classList.remove('active'); }
        window.onclick = function(event) {
            if (event.target == mTambah) closeModalTambah();
            if (event.target == mEdit) closeModalEdit();
        }
    </script>
</body>
</html>
