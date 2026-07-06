<?php
// MULAI KUNCI KEAMANAN
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require 'koneksi.php';

// PROSES SIMPAN DATA KE DATABASE
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_pemohon']);
    
    // Pecah Tanggal Lahir dari input kalender (Format: YYYY-MM-DD)
    $tgl_lahir_full = $_POST['tgl_lahir'];
    $tahun   = substr($tgl_lahir_full, 0, 4);
    $bulan   = substr($tgl_lahir_full, 5, 2);
    $tanggal = substr($tgl_lahir_full, 8, 2);
    
    $dokumen = mysqli_real_escape_string($conn, $_POST['dokumen']);
    $jk      = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $ket     = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Tanggal input untuk menentukan masuk laporan bulan apa
    $tgl_input = mysqli_real_escape_string($conn, $_POST['tanggal_input']); 

    $query = "INSERT INTO permohonan 
              (nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) 
              VALUES 
              ('$nama', '$tahun', '$bulan', '$tanggal', '$dokumen', '$jk', '$ket', '$tgl_input')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Berhasil! Data permohonan baru telah ditambahkan.');
                window.location.href = 'data_permohonan.php';
              </script>";
    } else {
        echo "<script>alert('Gagal menyimpan data ke database!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Permohonan - Arsip SPRI</title>
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
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }

        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1000px; margin: 0 auto; width: 100%; }

        .card-container { background: var(--surface); padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px;}
        .form-title { font-size: 1.2rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 25px; border-bottom: 2px solid var(--bg-main); padding-bottom: 15px;}
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;}
        .form-group.full-width { grid-column: 1 / -1; }
        
        .form-group label { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
        .form-control { padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.95rem; outline: none; background: var(--bg-main); color: var(--text-dark); font-family: 'Inter', sans-serif; transition: 0.2s;}
        .form-control:focus { border-color: var(--imigrasi-blue); background: var(--surface); box-shadow: 0 0 0 3px rgba(11,66,123,0.1);}
        
        .btn-group { display: flex; gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-light);}
        .btn-submit { padding: 12px 28px; background-color: var(--imigrasi-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); font-size: 0.95rem;}
        .btn-submit:hover { background-color: #08305A; transform: translateY(-2px); }
        .btn-cancel { padding: 12px 28px; background-color: var(--surface); color: var(--text-blue-grey); border: 1px solid var(--border-light); border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;}
        .btn-cancel:hover { background-color: var(--bg-main); color: var(--text-dark); }
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
                <div class="form-title">📝 Formulir Data Pemohon SPRI</div>
                
                <form action="" method="POST">
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
                            <label>Jumlah Dokumen</label>
                            <input type="number" name="dokumen" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Status Keterangan</label>
                            <select name="keterangan" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Keterangan --</option>
                                <option value="Baru">Baru</option>
                                <option value="Penggantian HB">Penggantian HB</option>
                                <option value="Perubahan Nama">Perubahan Nama</option>
                            </select>
                        </div>

                        <div class="form-group full-width" style="background: rgba(245, 166, 35, 0.1); padding: 15px; border-radius: 8px; border: 1px dashed var(--imigrasi-gold); margin-top: 10px;">
                            <label style="color: #D48806;">Tanggal Masuk Arsip (Untuk Laporan Bulanan)</label>
                            <input type="date" name="tanggal_input" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            <small style="color: var(--text-blue-grey); margin-top: 5px; display: block;">*Bulan dan Tahun dari tanggal ini akan menentukan di laporan mana data ini akan muncul.</small>
                        </div>

                    </div>

                    <div class="btn-group">
                        <button type="submit" name="simpan" class="btn-submit">💾 Simpan Data Pemohon</button>
                        <a href="data_permohonan.php" class="btn-cancel">Batal & Kembali</a>
                    </div>
                </form>

            </div>
        </div>
    </main>

</body>
</html>