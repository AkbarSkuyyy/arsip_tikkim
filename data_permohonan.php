<?php
// MULAI KUNCI KEAMANAN
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// ==============================================================
// 1. PROSES HAPUS DATA
// ==============================================================
if (isset($_GET['hapus_id'])) {
    $id_hapus = (int)$_GET['hapus_id'];
    
    // Perlindungan agar tidak terhapus massal jika id = 0
    if ($id_hapus > 0) {
        if (mysqli_query($conn, "DELETE FROM permohonan WHERE id = $id_hapus")) {
            echo "<script>alert('Berhasil! Data permohonan telah dihapus.'); window.location.href = 'data_permohonan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data!');</script>";
        }
    } else {
        echo "<script>alert('Error: ID bernilai 0. Pastikan kolom id di database menggunakan AUTO_INCREMENT.'); window.location.href = 'data_permohonan.php';</script>";
    }
}

// ==============================================================
// 2. PROSES SIMPAN DATA BARU (Dari Popup Tambah)
// ==============================================================
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_pemohon']));
    $tgl_lahir_full = $_POST['tgl_lahir'];
    $tahun   = substr($tgl_lahir_full, 0, 4);
    $bulan   = substr($tgl_lahir_full, 5, 2);
    $tanggal = substr($tgl_lahir_full, 8, 2);
    $dokumen = mysqli_real_escape_string($conn, $_POST['dokumen']);
    $jk      = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $ket     = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tgl_input = mysqli_real_escape_string($conn, $_POST['tanggal_input']); 

    $query_simpan = "INSERT INTO permohonan (nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) 
                     VALUES ('$nama', '$tahun', '$bulan', '$tanggal', '$dokumen', '$jk', '$ket', '$tgl_input')";

    if (mysqli_query($conn, $query_simpan)) {
        echo "<script>alert('Berhasil! Data baru ditambahkan.'); window.location.href = 'data_permohonan.php';</script>";
    }
}

// ==============================================================
// 3. PROSES UPDATE DATA (Dari Popup Edit)
// ==============================================================
if (isset($_POST['update'])) {
    $id_edit = (int)$_POST['id_permohonan'];
    
    // PELINDUNG: Pastikan yang diupdate hanya 1 ID yang valid, bukan id 0
    if ($id_edit > 0) {
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_pemohon']));
        $tgl_lahir_full = $_POST['tgl_lahir'];
        $tahun   = substr($tgl_lahir_full, 0, 4);
        $bulan   = substr($tgl_lahir_full, 5, 2);
        $tanggal = substr($tgl_lahir_full, 8, 2);
        $dokumen = mysqli_real_escape_string($conn, $_POST['dokumen']);
        $jk      = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
        $ket     = mysqli_real_escape_string($conn, $_POST['keterangan']);
        $tgl_input = mysqli_real_escape_string($conn, $_POST['tanggal_input']); 

        $query_update = "UPDATE permohonan SET 
                            nama_pemohon = '$nama', tahun_lahir = '$tahun', bulan_lahir = '$bulan', tanggal_lahir = '$tanggal', 
                            dokumen = '$dokumen', jenis_kelamin = '$jk', keterangan = '$ket', tanggal_input = '$tgl_input' 
                         WHERE id = '$id_edit'";

        if (mysqli_query($conn, $query_update)) {
            echo "<script>alert('Berhasil! Data permohonan telah diperbarui.'); window.location.href = 'data_permohonan.php';</script>";
        }
    } else {
        echo "<script>alert('Sistem Menolak Update! Ditemukan Error ID = 0 di database Anda. Harap atur kolom id menjadi AUTO_INCREMENT.');</script>";
    }
}
// ==============================================================

// 4. Ambil Parameter Filter & Pencarian
$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$keyword       = isset($_GET['cari']) ? mysqli_real_escape_string($conn, trim($_GET['cari'])) : '';

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// Menyiapkan syntax SQL tambahan jika user mengetik sesuatu di kotak pencarian
$kondisi_cari = "";
if (!empty($keyword)) {
    $kondisi_cari = " AND nama_pemohon LIKE '%$keyword%' ";
}

// 5. Query Rekap Bulanan (Digabung dengan pencarian)
$query_rekap = "SELECT 
                    COUNT(*) as total_data,
                    SUM(CASE WHEN keterangan = 'Baru' THEN 1 ELSE 0 END) as total_baru,
                    SUM(CASE WHEN keterangan LIKE '%Penggantian%' THEN 1 ELSE 0 END) as total_penggantian,
                    SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as total_laki,
                    SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as total_perempuan
                FROM permohonan 
                WHERE MONTH(tanggal_input) = '$bulan_pilihan' 
                AND YEAR(tanggal_input) = '$tahun_pilihan' 
                $kondisi_cari";
$rekap_res = mysqli_query($conn, $query_rekap);
$rekap = mysqli_fetch_assoc($rekap_res);

// 6. Konfigurasi Pagination
$batas_per_halaman = 15; 
$halaman_aktif = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman_aktif > 1) ? ($halaman_aktif * $batas_per_halaman) - $batas_per_halaman : 0;
$total_semua_data = (int)$rekap['total_data'];
$total_halaman = ceil($total_semua_data / $batas_per_halaman);

// 7. Query Data Tabel (Dengan Filter Bulan, Tahun, dan Nama)
$query_tabel = "SELECT * FROM permohonan 
                WHERE MONTH(tanggal_input) = '$bulan_pilihan' 
                AND YEAR(tanggal_input) = '$tahun_pilihan' 
                $kondisi_cari 
                ORDER BY id ASC LIMIT $halaman_awal, $batas_per_halaman";
$data_res = mysqli_query($conn, $query_tabel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Permohonan - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --imigrasi-dark: #0A2540; --imigrasi-blue: #0B427B; --imigrasi-gold: #F5A623; 
            --bg-main: #F4F7FA; --surface: #FFFFFF; --text-dark: #0F172A;
            --text-blue-grey: #64748B; --border-light: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .stat-value, .brand-main { font-family: 'Outfit', sans-serif; }

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
        .btn-cari { padding: 12px 24px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); height: 44px;}
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
        .btn-tambah { background: var(--imigrasi-blue); color: white; padding: 10px 18px; border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); cursor: pointer; font-family: 'Inter', sans-serif;}
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

        /* TOMBOL AKSI EDIT & HAPUS */
        .action-flex { display: flex; gap: 8px; justify-content: center; }
        .btn-aksi { padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif;}
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

        /* MODAL POPUP */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 37, 64, 0.6); backdrop-filter: blur(4px); display: none; justify-content: center; align-items: center; z-index: 9999; opacity: 0; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-container { background: var(--surface); width: 90%; max-width: 700px; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); transform: translateY(-20px); transition: transform 0.3s ease; display: flex; flex-direction: column; max-height: 90vh; }
        .modal-overlay.active .modal-container { transform: translateY(0); }
        .modal-header { padding: 24px 30px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-size: 1.2rem; font-weight: 700; color: var(--imigrasi-dark); font-family: 'Outfit', sans-serif;}
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
        .btn-save { padding: 10px 24px; background: var(--imigrasi-blue); border: none; border-radius: 8px; cursor: pointer; font-weight: 600; color: white; transition: 0.2s; box-shadow: 0 4px 12px rgba(11,66,123,0.2);}
        .btn-save:hover { background: #08305A; transform: translateY(-1px); }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="content">
            <header class="top-header">
                <h1 class="page-title">Kelola Arsip Permohonan</h1>
            </header>

            <div class="content-wrapper">
                
                <div class="card-container">
                    <form action="" method="GET" class="filter-form">
                        
                        <div class="form-group">
                            <label>Cari Pemohon</label>
                            <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Ketik nama di sini..." style="width: 250px;">
                        </div>

                        <div class="form-group">
                            <label>Bulan Laporan</label>
                            <select name="bulan">
                                <?php foreach ($nama_bulan as $key => $value): ?>
                                    <option value="<?= $key; ?>" <?= $bulan_pilihan == $key ? 'selected' : ''; ?>><?= $value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun Laporan</label>
                            <select name="tahun">
                                <?php 
                                $tahun_sekarang = date('Y');
                                for ($i = $tahun_sekarang + 1; $i >= $tahun_sekarang - 5; $i--): 
                                ?>
                                    <option value="<?= $i; ?>" <?= $tahun_pilihan == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-cari">🔍 Terapkan Filter</button>
                    </form>
                </div>

                <h2 style="font-size: 1.1rem; color: var(--imigrasi-dark); margin-bottom: 16px;">
                    Rekapitulasi: <?= (!empty($keyword) ? 'Pencarian "'.$keyword.'" di ' : '') . $nama_bulan[$bulan_pilihan] . ' ' . $tahun_pilihan; ?>
                </h2>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Pemohon</div>
                        <div class="stat-value"><?= $total_semua_data; ?></div>
                        <div class="stat-sub">Dokumen SPRI</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Permohonan Baru</div>
                        <div class="stat-value"><?= (int)$rekap['total_baru']; ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Penggantian HB</div>
                        <div class="stat-value"><?= (int)$rekap['total_penggantian']; ?></div>
                        <div class="stat-sub">Dokumen Diterbitkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Rasio Jenis Kelamin</div>
                        <div class="stat-value" style="font-size: 1.5rem; line-height: 1.4;">
                            <span style="color: var(--imigrasi-blue);">L: <?= (int)$rekap['total_laki']; ?></span> | 
                            <span style="color: var(--imigrasi-gold);">P: <?= (int)$rekap['total_perempuan']; ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-container" style="padding: 0;">
                    <div class="table-title" style="padding: 24px 28px 0 28px;">
                        Rincian Pemohon Paspor
                        <button onclick="openModalTambah()" class="btn-tambah">+ Tambah Data</button>
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
                                <?php 
                                $no = $halaman_awal + 1;
                                if (mysqli_num_rows($data_res) > 0):
                                    while ($row = mysqli_fetch_assoc($data_res)): 
                                        
                                        $tgl_tampil = (!empty($row['tanggal_lahir']) && $row['tanggal_lahir'] != '00') 
                                            ? $row['tanggal_lahir'] . '-' . $row['bulan_lahir'] . '-' . $row['tahun_lahir'] : '-';
                                        
                                        $tahun_db = $row['tahun_lahir'];
                                        $bulan_db = str_pad($row['bulan_lahir'], 2, "0", STR_PAD_LEFT);
                                        $tanggal_db = str_pad($row['tanggal_lahir'], 2, "0", STR_PAD_LEFT);
                                        $tgl_edit = ($tahun_db != '0' && !empty($tahun_db)) ? "$tahun_db-$bulan_db-$tanggal_db" : "";

                                        $badge_class = (strpos(strtolower($row['keterangan']), 'baru') !== false) ? 'badge badge-baru' : 'badge badge-ganti';
                                ?>
                                    <tr>
                                        <td style="padding-left: 28px; color: var(--text-blue-grey); font-weight: 600;"><?= $no++; ?></td>
                                        <td style="font-weight: 700; color: var(--imigrasi-dark);"><?= htmlspecialchars($row['nama_pemohon']); ?></td>
                                        <td><?= $tgl_tampil; ?></td>
                                        <td style="text-align: center;"><span class="badge badge-jk"><?= htmlspecialchars($row['jenis_kelamin']); ?></span></td>
                                        <td style="text-align: center; color: var(--text-blue-grey);"><?= htmlspecialchars($row['dokumen']); ?></td>
                                        <td><span class="<?= $badge_class; ?>"><?= htmlspecialchars($row['keterangan']); ?></span></td>
                                        
                                        <td style="padding-right: 28px;">
                                            <div class="action-flex">
                                                <button onclick="openModalEdit('<?= $row['id'] ?>', '<?= htmlspecialchars($row['nama_pemohon'], ENT_QUOTES) ?>', '<?= $tgl_edit ?>', '<?= $row['jenis_kelamin'] ?>', '<?= $row['dokumen'] ?>', '<?= $row['keterangan'] ?>', '<?= $row['tanggal_input'] ?>')" class="btn-aksi btn-edit">✏️ Edit</button>
                                                
                                                <a href="?cari=<?= urlencode($keyword) ?>&bulan=<?= $bulan_pilihan ?>&tahun=<?= $tahun_pilihan ?>&halaman=<?= $halaman_aktif ?>&hapus_id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus arsip atas nama: <?= htmlspecialchars($row['nama_pemohon'], ENT_QUOTES) ?>?');" class="btn-aksi btn-hapus">🗑️ Hapus</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 60px 20px; color: var(--text-blue-grey);">
                                            <div style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;">📁</div>
                                            Belum ada data arsip untuk bulan <?= $nama_bulan[$bulan_pilihan] . ' ' . $tahun_pilihan; ?>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($total_halaman > 0): ?>
                    <div class="pagination-container" style="padding: 0 28px 24px 28px;">
                        <div class="pagination-info">Menampilkan <b><?= ($total_semua_data == 0) ? 0 : $halaman_awal + 1; ?></b> - <b><?= min($halaman_awal + $batas_per_halaman, $total_semua_data); ?></b> dari <b><?= $total_semua_data; ?></b> pemohon</div>
                        <ul class="pagination-nav">
                            <?php if($halaman_aktif > 1): ?>
                                <li><a class="page-link" href="?cari=<?= urlencode($keyword); ?>&bulan=<?= $bulan_pilihan; ?>&tahun=<?= $tahun_pilihan; ?>&halaman=<?= $halaman_aktif - 1; ?>">« Prev</a></li>
                            <?php else: ?>
                                <li><span class="page-link disabled">« Prev</span></li>
                            <?php endif; ?>

                            <?php 
                            $start_number = max(1, $halaman_aktif - 2);
                            $end_number = min($total_halaman, $halaman_aktif + 2);
                            if ($start_number > 1) {
                                echo '<li><a class="page-link" href="?cari='.urlencode($keyword).'&bulan='.$bulan_pilihan.'&tahun='.$tahun_pilihan.'&halaman=1">1</a></li>';
                                if ($start_number > 2) echo '<li><span class="page-link disabled">...</span></li>';
                            }
                            for($i = $start_number; $i <= $end_number; $i++): 
                            ?>
                                <li><a class="page-link <?= ($halaman_aktif == $i) ? 'active' : ''; ?>" href="?cari=<?= urlencode($keyword); ?>&bulan=<?= $bulan_pilihan; ?>&tahun=<?= $tahun_pilihan; ?>&halaman=<?= $i; ?>"><?= $i; ?></a></li>
                            <?php endfor; ?>

                            <?php 
                            if ($end_number < $total_halaman) {
                                if ($end_number < $total_halaman - 1) echo '<li><span class="page-link disabled">...</span></li>';
                                echo '<li><a class="page-link" href="?cari='.urlencode($keyword).'&bulan='.$bulan_pilihan.'&tahun='.$tahun_pilihan.'&halaman='.$total_halaman.'">'.$total_halaman.'</a></li>';
                            }
                            ?>

                            <?php if($halaman_aktif < $total_halaman): ?>
                                <li><a class="page-link" href="?cari=<?= urlencode($keyword); ?>&bulan=<?= $bulan_pilihan; ?>&tahun=<?= $tahun_pilihan; ?>&halaman=<?= $halaman_aktif + 1; ?>">Next »</a></li>
                            <?php else: ?>
                                <li><span class="page-link disabled">Next »</span></li>
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
                <div class="modal-title">📝 Tambah Pemohon SPRI</div>
                <button type="button" class="modal-close" onclick="closeModalTambah()">&times;</button>
            </div>
            <form action="" method="POST">
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
                            <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalTambah()">Batal</button>
                    <button type="submit" name="simpan" class="btn-save">💾 Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEdit">
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title">✏️ Edit Data Pemohon</div>
                <button type="button" class="modal-close" onclick="closeModalEdit()">&times;</button>
            </div>
            <form action="" method="POST">
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
                    <button type="submit" name="update" class="btn-save" style="background: #D48806;">🔄 Update Data</button>
                </div>
            </form>
        </div>
    </div>

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