<?php
// KUNCI KEAMANAN
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// Ambil master data untuk dropdown
$q_dok = mysqli_query($conn, "SELECT kode, nama FROM master_dokumen WHERE aktif=1 ORDER BY kode");
$q_ket = mysqli_query($conn, "SELECT nama FROM master_keterangan WHERE aktif=1 ORDER BY nama");

// PROSES SIMPAN DATA KE DATABASE
if (isset($_POST['simpan'])) {
    csrf_verify();

    $nama      = trim($_POST['nama_pemohon']);
    $tgl_lahir = norm_tgl_lahir($_POST['tgl_lahir'] ?? null);
    $dokumen   = trim($_POST['dokumen']);
    $jk        = ($_POST['jenis_kelamin'] === 'P') ? 'P' : 'L';
    $ket       = trim($_POST['keterangan']);
    $tgl_input = $_POST['tanggal_input'] ?? date('Y-m-d');

    if ($tgl_lahir === null) {
        flash_set('error', 'Tanggal lahir tidak valid!');
    } elseif (db_exec("INSERT INTO permohonan (nama_pemohon, tgl_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) VALUES (?, ?, ?, ?, ?, ?)", 'ssssss', [$nama, $tgl_lahir, $dokumen, $jk, $ket, $tgl_input])) {
        catatLog($conn, $_SESSION['user_nama'], "Menambahkan permohonan baru atas nama: " . $nama);
        flash_set('success', 'Data permohonan baru telah ditambahkan.');
    } else {
        flash_set('error', 'Gagal menyimpan data ke database!');
    }
    header("Location: data_permohonan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Permohonan - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1000px; margin: 0 auto; width: 100%; }

        .card-container { background: var(--surface); padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px;}
        .form-title { font-size: 1.2rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 25px; border-bottom: 2px solid var(--bg-main); padding-bottom: 15px; display: flex; align-items: center; gap: 10px;}

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;}
        .form-group.full-width { grid-column: 1 / -1; }

        .form-group label { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
        .form-control { padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.95rem; outline: none; background: var(--bg-main); color: var(--text-dark); font-family: 'Inter', sans-serif; transition: 0.2s;}
        .form-control:focus { border-color: var(--imigrasi-blue); background: var(--surface); box-shadow: 0 0 0 3px rgba(11,66,123,0.1);}

        .btn-group { display: flex; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-light);}
        .btn-submit { padding: 12px 28px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); font-size: 0.95rem; display: inline-flex; align-items: center; gap: 8px;}
        .btn-submit:hover { background-color: #08305A; transform: translateY(-2px); }
        .btn-cancel { padding: 12px 28px; background-color: var(--surface); color: var(--text-blue-grey); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;}
        .btn-cancel:hover { background-color: var(--bg-main); color: var(--text-dark); }
        .icon svg { width: 16px; height: 16px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Input Data Permohonan Baru</h1>
        </header>

        <div class="content-wrapper">
            <div class="card-container">
                <div class="form-title"><?= icon('edit') ?> Formulir Data Pemohon SPRI</div>

                <form action="" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label>Nama Lengkap Pemohon</label>
                            <input type="text" name="nama_pemohon" class="form-control" placeholder="Masukkan nama sesuai dokumen" required>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Gender --</option>
                                <option value="L">Laki-Laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jenis Dokumen</label>
                            <select name="dokumen" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Jenis Dokumen --</option>
                                <?php while ($d = mysqli_fetch_assoc($q_dok)): ?>
                                <option value="<?= htmlspecialchars($d['kode']) ?>"><?= htmlspecialchars($d['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status Keterangan</label>
                            <select name="keterangan" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Keterangan --</option>
                                <?php while ($k = mysqli_fetch_assoc($q_ket)): ?>
                                <option value="<?= htmlspecialchars($k['nama']) ?>"><?= htmlspecialchars($k['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group full-width" style="background: rgba(245, 166, 35, 0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--imigrasi-gold); margin-top: 10px;">
                            <label style="color: #D48806;">Tanggal Masuk Arsip (Untuk Laporan Bulanan)</label>
                            <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            <small style="color: var(--text-blue-grey); margin-top: 5px; display: block;">*Bulan dan Tahun dari tanggal ini akan menentukan di laporan mana data ini akan muncul.</small>
                        </div>

                    </div>

                    <div class="btn-group">
                        <button type="submit" name="simpan" class="btn-submit"><?= icon('save') ?> Simpan Data Pemohon</button>
                        <a href="data_permohonan.php" class="btn-cancel">Batal & Kembali</a>
                    </div>
                </form>

            </div>
        </div>
    </main>

</body>
</html>
