<?php
// MULAI KUNCI KEAMANAN
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// ==============================================================
// 1. PROSES HAPUS SATUAN
// ==============================================================
if (isset($_GET['hapus_id'])) {
    $id_hapus = (int)$_GET['hapus_id'];
    if ($id_hapus > 0) {
        if (mysqli_query($conn, "DELETE FROM permohonan WHERE id = $id_hapus")) {
            echo "<script>alert('Berhasil! Data arsip telah dimusnahkan secara permanen.'); window.location.href = 'pemusnahan_arsip.php';</script>";
        } else {
            echo "<script>alert('Gagal memusnahkan data!');</script>";
        }
    }
}

// ==============================================================
// 2. PROSES MUSNAHKAN SEMUA DATA > 5 TAHUN
// ==============================================================
if (isset($_POST['musnahkan_semua'])) {
    $query_musnahkan = "DELETE FROM permohonan WHERE tanggal_input <= DATE_SUB(CURDATE(), INTERVAL 5 YEAR)";
    if (mysqli_query($conn, $query_musnahkan)) {
        echo "<script>alert('Berhasil! Semua arsip yang lebih dari 5 tahun telah dimusnahkan.'); window.location.href = 'pemusnahan_arsip.php';</script>";
    } else {
        echo "<script>alert('Gagal memusnahkan data massal!');</script>";
    }
}

// ==============================================================
// 3. QUERY DATA ARSIP > 5 TAHUN
// ==============================================================
$query_tabel = "SELECT * FROM permohonan WHERE tanggal_input <= DATE_SUB(CURDATE(), INTERVAL 5 YEAR) ORDER BY tanggal_input ASC";
$data_res = mysqli_query($conn, $query_tabel);
$total_data = mysqli_num_rows($data_res);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemusnahan Arsip - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Menggunakan style yang sama persis dengan data_permohonan.php */
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
        
        .alert-box { background-color: rgba(239, 68, 68, 0.1); border: 1px solid #EF4444; color: #B91C1C; padding: 15px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; font-size: 0.95rem; }

        .table-title { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;}
        
        /* Tombol Musnahkan Semua */
        .btn-musnah-semua { background: #EF4444; color: white; padding: 10px 18px; border: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); cursor: pointer; font-family: 'Inter', sans-serif;}
        .btn-musnah-semua:hover { background: #DC2626; transform: translateY(-1px); }

        .table-responsive { overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 16px 15px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 16px 15px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; font-weight: 500; color: var(--text-dark); }
        tbody tr:hover { background-color: rgba(244, 247, 250, 0.5); }
        
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; display: inline-block; text-align: center;}
        .badge-keterangan { background-color: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); }
        
        .action-flex { display: flex; gap: 8px; justify-content: center; }
        .btn-aksi { padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif;}
        .btn-hapus { background: rgba(239, 68, 68, 0.05); color: #EF4444; border-color: rgba(239, 68, 68, 0.2); }
        .btn-hapus:hover { background: #EF4444; color: white; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="content">
            <header class="top-header">
                <h1 class="page-title">Pemusnahan Arsip (> 5 Tahun)</h1>
            </header>

            <div class="content-wrapper">
                
                <div class="alert-box">
                    ⚠️ <b>Perhatian:</b> Halaman ini menampilkan arsip permohonan yang tanggal masuknya (tanggal input) sudah melewati batas waktu 5 tahun dari tanggal hari ini. Data yang dimusnahkan tidak dapat dikembalikan.
                </div>

                <div class="card-container" style="padding: 0;">
                    <div class="table-title" style="padding: 24px 28px 0 28px;">
                        <div>
                            Daftar Arsip Kedaluwarsa 
                            <span style="font-size: 0.85rem; color: var(--text-blue-grey); font-weight: normal; margin-left: 10px;">(Total: <?= $total_data; ?> Arsip)</span>
                        </div>
                        
                        <?php if($total_data > 0): ?>
                        <form action="" method="POST" onsubmit="return confirm('PERINGATAN! Anda yakin ingin memusnahkan SEMUA data arsip ini secara permanen?');">
                            <button type="submit" name="musnahkan_semua" class="btn-musnah-semua">🗑️ Musnahkan Semua Data</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="table-responsive">
                        <table style="margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="padding-left: 28px; width: 50px;">NO</th>
                                    <th>NAMA LENGKAP</th>
                                    <th>TANGGAL LAHIR</th>
                                    <th>TANGGAL INPUT (MASUK)</th>
                                    <th>KETERANGAN</th>
                                    <th style="text-align: center; padding-right: 28px;">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if (mysqli_num_rows($data_res) > 0):
                                    while ($row = mysqli_fetch_assoc($data_res)): 
                                        $tgl_lahir = (!empty($row['tanggal_lahir']) && $row['tanggal_lahir'] != '00') 
                                            ? $row['tanggal_lahir'] . '-' . $row['bulan_lahir'] . '-' . $row['tahun_lahir'] : '-';
                                        
                                        // Format Tanggal Input
                                        $tgl_input = date('d-m-Y', strtotime($row['tanggal_input']));
                                ?>
                                    <tr>
                                        <td style="padding-left: 28px; color: var(--text-blue-grey); font-weight: 600;"><?= $no++; ?></td>
                                        <td style="font-weight: 700; color: var(--imigrasi-dark);"><?= htmlspecialchars($row['nama_pemohon']); ?></td>
                                        <td><?= $tgl_lahir; ?></td>
                                        <td style="color: #EF4444; font-weight: 600;"><?= $tgl_input; ?></td>
                                        <td><span class="badge badge-keterangan"><?= htmlspecialchars($row['keterangan']); ?></span></td>
                                        
                                        <td style="padding-right: 28px;">
                                            <div class="action-flex">
                                                <a href="?hapus_id=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin memusnahkan arsip atas nama: <?= htmlspecialchars($row['nama_pemohon'], ENT_QUOTES) ?> secara permanen?');" class="btn-aksi btn-hapus">🗑️ Musnahkan</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile; 
                                else:
                                ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 60px 20px; color: var(--text-blue-grey);">
                                            <div style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;">✨</div>
                                            Tidak ada arsip yang berumur lebih dari 5 tahun saat ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>