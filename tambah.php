<?php
// MULAI KUNCI KEAMANAN: Cek apakah user sudah login
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// AKHIR KUNCI KEAMANAN

require 'koneksi.php';

// Proses ketika tombol simpan ditekan
if (isset($_POST['submit'])) {
    $nama_pemohon   = mysqli_real_escape_string($conn, $_POST['nama_pemohon']);
    $tahun_lahir    = mysqli_real_escape_string($conn, $_POST['tahun_lahir']);
    $bulan_lahir    = mysqli_real_escape_string($conn, $_POST['bulan_lahir']);
    $tanggal_lahir  = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $dokumen        = mysqli_real_escape_string($conn, $_POST['dokumen']);
    $jenis_kelamin  = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Menambahkan CURRENT_DATE() agar data masuk dengan tanggal input hari ini
    $query = "INSERT INTO permohonan (nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) 
              VALUES ('$nama_pemohon', '$tahun_lahir', '$bulan_lahir', '$tanggal_lahir', '$dokumen', '$jenis_kelamin', '$keterangan', CURRENT_DATE())";

    if (mysqli_query($conn, $query)) {
        // Jika berhasil, alihkan kembali ke halaman data_permohonan.php
        header("Location: data_permohonan.php");
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan data: " . mysqli_error($conn) . "');</script>";
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
            /* Palet Utama Institusi */
            --imigrasi-dark: #0A2540;
            --imigrasi-blue: #0B427B;
            --imigrasi-gold: #F5A623;
            
            /* Palet Sekunder & Latar */
            --bg-main: #F4F7FA;
            --surface: #FFFFFF;
            --text-dark: #0F172A;
            --text-blue-grey: #64748B;
            --border-light: #E2E8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-main); 
            color: var(--text-dark); 
            display: flex; 
            height: 100vh; 
            overflow: hidden; 
        }
        
        h1, h2, h3, .brand-main, .form-title { font-family: 'Outfit', sans-serif; }

        /* Main Content Area */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 40px; max-width: 800px; margin: 0 auto; width: 100%; }

        /* Card Form Styling */
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); overflow: hidden; }
        .card-header { padding: 24px 32px; border-bottom: 1px solid var(--border-light); background-color: #F8FAFC; }
        .card-title { font-size: 1.15rem; font-weight: 700; color: var(--imigrasi-dark); }
        .card-body { padding: 32px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; color: var(--text-dark); }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        
        .ttl-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; }
        .radio-group { display: flex; gap: 20px; padding-top: 6px; }
        .radio-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 500; }
        .radio-label input { accent-color: var(--imigrasi-blue); width: 16px; height: 16px; }

        .btn-area { display: flex; gap: 12px; margin-top: 32px; }
        .btn { flex: 1; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; text-align: center; text-decoration: none; }
        .btn-primary { background-color: var(--imigrasi-blue); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,66,123,0.2); }
        .btn-cancel { background-color: #E2E8F0; color: var(--text-blue-grey); }
        .btn-cancel:hover { background-color: #CBD5E1; }
    </style>
</head>
<body>

    <!-- Memuat komponen Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Tambah Data Permohonan</h1>
        </header>

        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Formulir Input Dokumen SPRI</h2>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        
                        <div class="form-group">
                            <label for="nama_pemohon">Nama Lengkap Pemohon</label>
                            <input type="text" id="nama_pemohon" name="nama_pemohon" class="form-control" placeholder="Contoh: Rabiyatussaajiah" required>
                        </div>

                        <div class="form-group">
                            <label>Tanggal Kelahiran</label>
                            <div class="ttl-grid">
                                <input type="number" name="tahun_lahir" class="form-control" placeholder="Tahun (2004)" min="1900" max="2026" required>
                                <input type="number" name="bulan_lahir" class="form-control" placeholder="Bulan (02)" min="1" max="12" required>
                                <input type="number" name="tanggal_lahir" class="form-control" placeholder="Tanggal (08)" min="1" max="31" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dokumen">Jumlah Berkas Dokumen</label>
                            <input type="number" id="dokumen" name="dokumen" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="jenis_kelamin" value="P" required> Perempuan
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan Berkas</label>
                            <select id="keterangan" name="keterangan" class="form-control" required>
                                <option value="Baru">Baru</option>
                                <option value="Penggantian">Penggantian</option>
                            </select>
                        </div>

                        <div class="btn-area">
                            <a href="data_permohonan.php" class="btn btn-cancel">Batal</a>
                            <button type="submit" name="submit" class="btn btn-primary">Simpan Data</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>