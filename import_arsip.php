<?php
// MULAI KUNCI KEAMANAN
session_start();
if (!isset($_SESSION['login'])) { 
    header("Location: login.php"); 
    exit; 
}
require 'koneksi.php';

if (isset($_POST['import'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $bulan_arsip = $_POST['bulan_arsip']; 
    $tahun_arsip = date('Y'); // Mengikuti tahun server atau bisa diubah manual misal '2026'
    
    $tanggal_input = $tahun_arsip . '-' . $bulan_arsip . '-01'; 

    $handle = fopen($file, "r");
    
    // Lewati 1 baris pertama karena berisi header
    fgetcsv($handle, 1000, ","); 

    $berhasil = 0;
    $gagal = 0;
    $dilewati = 0; // Untuk menghitung baris kosong yang dibuang

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        
        // Ambil nama pemohon terlebih dahulu
        $nama = isset($data[1]) ? trim($data[1]) : '';
        
        // FILTER KETAT: Jika NAMA PEMOHON kosong, langsung buang baris ini!
        if (empty($nama)) {
            $dilewati++;
            continue; 
        }

        // Pembersihan angka secara akurat (Menghapus akhiran ".0" tapi mempertahankan angka asli)
        $tahun_raw   = isset($data[2]) ? preg_replace('/\.0$/', '', trim($data[2])) : '';
        $bulan_raw   = isset($data[3]) ? preg_replace('/\.0$/', '', trim($data[3])) : '';
        $tanggal_raw = isset($data[4]) ? preg_replace('/\.0$/', '', trim($data[4])) : '';
        $dokumen_raw = isset($data[5]) ? preg_replace('/\.0$/', '', trim($data[5])) : '';

        // Keamanan SQL Injection
        $nama       = mysqli_real_escape_string($conn, $nama);
        $tahun      = mysqli_real_escape_string($conn, $tahun_raw);
        
        // Format bulan dan tanggal agar selalu menjadi 2 digit
        $bulan      = ($bulan_raw !== '') ? mysqli_real_escape_string($conn, str_pad((int)$bulan_raw, 2, "0", STR_PAD_LEFT)) : '';
        $tanggal    = ($tanggal_raw !== '') ? mysqli_real_escape_string($conn, str_pad((int)$tanggal_raw, 2, "0", STR_PAD_LEFT)) : '';
        
        $dokumen    = mysqli_real_escape_string($conn, $dokumen_raw);
        $jk         = isset($data[6]) ? mysqli_real_escape_string($conn, trim($data[6])) : '';
        $keterangan = isset($data[7]) ? mysqli_real_escape_string($conn, trim($data[7])) : '';

        // Query Insert
        $query = "INSERT INTO permohonan 
                  (nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) 
                  VALUES 
                  ('$nama', '$tahun', '$bulan', '$tanggal', '$dokumen', '$jk', '$keterangan', '$tanggal_input')";
        
        if(mysqli_query($conn, $query)){
            $berhasil++;
        } else {
            $gagal++;
        }
    }
    fclose($handle);
    
    // Pesan Notifikasi
    $nama_bulan = date("F", mktime(0, 0, 0, $bulan_arsip, 10));
    $pesan = "<strong>Berhasil!</strong> $berhasil data masuk ke bulan $nama_bulan. <br><span style='font-size: 0.85rem; font-weight:normal; margin-top:5px; display:inline-block;'>(Sebanyak $dilewati baris Excel yang kosong/tanpa nama telah dibuang secara otomatis).</span>";
    if($gagal > 0) {
        $pesan_error = "$gagal data gagal diproses.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Arsip SPRI - TIKKIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Palet Warna Imigrasi */
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

        /* KUNCI TATA LETAK FLEKSIBEL (SEJAJAR SIDEBAR) */
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; background: var(--bg-main); }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        
        .content-wrapper { padding: 32px 40px; max-width: 900px; margin: 0 auto; width: 100%; }

        /* KOTAK KARTU UTAMA */
        .card-container { background: var(--surface); padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px;}
        .card-title { font-size: 1.25rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--bg-main); padding-bottom: 15px;}
        
        /* STYLE ALERT (Pemberitahuan) */
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; font-size: 0.95rem; line-height: 1.5;}
        .alert-info { background-color: #F0F9FF; border: 1px solid #BAE6FD; color: #0369A1; }
        .alert-success { background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; }
        .alert-danger { background-color: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }

        /* FORM STYLE */
        .form-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;}
        .form-group label { font-size: 0.9rem; color: var(--imigrasi-dark); font-weight: 600; }
        
        .form-control { padding: 14px 18px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.95rem; outline: none; background: #F8FAFC; color: var(--text-dark); font-family: 'Inter', sans-serif; transition: 0.2s; width: 100%;}
        .form-control:focus { border-color: var(--imigrasi-blue); background: var(--surface); box-shadow: 0 0 0 3px rgba(11,66,123,0.1);}
        
        /* KHUSUS INPUT FILE */
        input[type="file"] { background: var(--surface); padding: 10px; cursor: pointer; border: 2px dashed #CBD5E1; color: var(--text-blue-grey); }
        input[type="file"]:hover { border-color: var(--imigrasi-blue); background: #F0F9FF;}
        input[type="file"]::file-selector-button { background: var(--imigrasi-blue); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 15px; transition: 0.2s;}
        input[type="file"]::file-selector-button:hover { background: #08305A; }

        .btn-submit { padding: 14px 30px; background-color: #10B981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); font-size: 1rem; width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;}
        .btn-submit:hover { background-color: #059669; transform: translateY(-2px); }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Sistem Impor Data Pemohon</h1>
            </header>

            <div class="content-wrapper">
                
                <div class="card-container">
                    <div class="card-title">
                        <span style="font-size: 1.5rem;">📥</span> Import Arsip Excel (CSV)
                    </div>
                    
                    <div class="alert alert-info">
                        <div style="font-size: 1.2rem;">💡</div>
                        <div>
                            <strong>Info Sistem:</strong> Fitur ini otomatis mendeteksi dan <b>membuang baris Excel yang tidak memiliki nama</b> untuk mencegah database penuh dengan data hantu.
                        </div>
                    </div>

                    <?php if(isset($pesan)) echo "<div class='alert alert-success'><div style='font-size: 1.2rem;'>✅</div><div>$pesan</div></div>"; ?>
                    <?php if(isset($pesan_error)) echo "<div class='alert alert-danger'><div style='font-size: 1.2rem;'>⚠️</div><div>$pesan_error</div></div>"; ?>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label>1. Pilih Tujuan Bulan Arsip:</label>
                            <select name="bulan_arsip" class="form-control" required>
                                <option value="" disabled selected>-- Pilih Bulan --</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                            <small style="color: var(--text-blue-grey); font-size: 0.8rem; margin-top: -5px;">*Tentukan pada laporan bulan apa file ini akan dihitung.</small>
                        </div>

                        <div class="form-group">
                            <label>2. Unggah File Excel Anda (.csv):</label>
                            <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                            <small style="color: var(--text-blue-grey); font-size: 0.8rem; margin-top: -5px;">*Pastikan file yang diunggah berformat <strong>CSV (Comma Delimited)</strong>.</small>
                        </div>
                        
                        <button type="submit" name="import" class="btn-submit">
                            ⚡ Mulai Proses Sinkronisasi
                        </button>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>