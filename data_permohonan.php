<?php
// MULAI KUNCI KEAMANAN: Cek apakah user sudah login
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// AKHIR KUNCI KEAMANAN

require 'koneksi.php';

// 1. Menangani aksi Tambah Data dari Pop-up Modal
if (isset($_POST['submit_tambah'])) {
    $nama_pemohon   = mysqli_real_escape_string($conn, $_POST['nama_pemohon']);
    $tahun_lahir    = mysqli_real_escape_string($conn, $_POST['tahun_lahir']);
    $bulan_lahir    = mysqli_real_escape_string($conn, $_POST['bulan_lahir']);
    $tanggal_lahir  = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $dokumen        = mysqli_real_escape_string($conn, $_POST['dokumen']);
    $jenis_kelamin  = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $keterangan     = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $query_insert = "INSERT INTO permohonan (nama_pemohon, tahun_lahir, bulan_lahir, tanggal_lahir, dokumen, jenis_kelamin, keterangan, tanggal_input) 
                 VALUES ('$nama_pemohon', '$tahun_lahir', '$bulan_lahir', '$tanggal_lahir', '$dokumen', '$jenis_kelamin', '$keterangan', CURRENT_DATE())";
                 
    if (mysqli_query($conn, $query_insert)) {
        catatLog($conn, $_SESSION['user_nama'], "Menambahkan permohonan baru atas nama: " . $nama_pemohon);
        header("Location: data_permohonan.php");
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan data: " . mysqli_error($conn) . "');</script>";
    }
}

// 2. Menangani fitur pencarian tabel
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $query = "SELECT * FROM permohonan WHERE nama_pemohon LIKE '%$keyword%' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM permohonan ORDER BY id DESC";
}

$result = mysqli_query($conn, $query);
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
            --danger-red: #EF4444;
            --edit-orange: #F59E0B;
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

        /* ==================== MAIN CONTENT AREA ==================== */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }

        /* ==================== TOOLBAR (SEARCH & ADD) ==================== */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .search-form { display: flex; gap: 10px; }
        .search-input { padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 10px; font-family: inherit; width: 320px; outline: none; font-size: 0.95rem; transition: all 0.2s; background: var(--surface); box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .search-input:focus { border-color: var(--imigrasi-blue); box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        
        .btn { padding: 12px 20px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; font-family: inherit; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn:hover { transform: translateY(-1px); opacity: 0.95; }
        .btn-search { background-color: var(--imigrasi-blue); color: white; box-shadow: 0 4px 12px rgba(11,66,123,0.15); }
        .btn-add { background-color: var(--imigrasi-dark); color: white; box-shadow: 0 4px 12px rgba(10,37,64,0.15); }
        .btn-reset { background-color: #E2E8F0; color: var(--text-blue-grey); }

        /* ==================== TABLE CONTAINER ==================== */
        .card-table-container { background: var(--surface); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; min-width: 900px; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 18px 24px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; background: #F8FAFC; }
        td { padding: 18px 24px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; color: var(--text-dark); font-weight: 500; }
        tbody tr:hover { background-color: rgba(244, 247, 250, 0.5); }
        
        /* Badges & Actions */
        .badge { background-color: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .badge.ganti { background-color: rgba(245, 166, 35, 0.1); color: #D48806; }
        
        .action-links { display: flex; gap: 8px; }
        .btn-action { padding: 6px 14px; border-radius: 6px; font-size: 0.82rem; text-decoration: none; color: white; font-weight: 600; transition: opacity 0.2s; }
        .btn-edit { background-color: var(--edit-orange); box-shadow: 0 2px 6px rgba(245,158,11,0.2); }
        .btn-delete { background-color: var(--danger-red); box-shadow: 0 2px 6px rgba(239,68,68,0.2); }
        .btn-action:hover { opacity: 0.85; }

        /* ==================== MODERN POP-UP MODAL ==================== */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(10, 37, 64, 0.5); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-content {
            background-color: var(--surface); padding: 36px; border-radius: 20px;
            border: 1px solid var(--border-light); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            max-width: 520px; width: 100%; animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .form-title { font-size: 1.4rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 24px; border-bottom: 1px solid var(--border-light); padding-bottom: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-blue-grey); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 14px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; outline: none; transition: 0.2s; background: #F8FAFC; }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        .ttl-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; }
        .radio-group { display: flex; gap: 20px; padding-top: 6px; }
        .radio-label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 500; }
        .radio-label input { accent-color: var(--imigrasi-blue); width: 16px; height: 16px; }
        .btn-area { display: flex; gap: 12px; margin-top: 32px; }
        .btn-modal { flex: 1; justify-content: center; }
        .btn-cancel { background-color: #E2E8F0; color: var(--text-blue-grey); }
    </style>
</head>
<body>

    <!-- Memuat komponen Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Data Seluruh Permohonan</h1>
        </header>

        <div class="content-wrapper">
            <div class="toolbar">
                <form action="data_permohonan.php" method="GET" class="search-form">
                    <input type="text" name="cari" class="search-input" placeholder="Cari nama pemohon..." value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit" class="btn btn-search">🔍 Cari</button>
                    <?php if(isset($_GET['cari']) && $_GET['cari'] != '') { ?>
                        <a href="data_permohonan.php" class="btn btn-reset">Reset</a>
                    <?php } ?>
                </form>
                
                <button id="btnBukaModal" class="btn btn-add">＋ Tambah Data Baru</button>
            </div>

            <div class="card-table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">NO</th>
                            <th>NAMA PEMOHON</th>
                            <th>TTL (THN-BLN-TGL)</th>
                            <th style="text-align: center; width: 100px;">DOKUMEN</th>
                            <th style="text-align: center; width: 100px;">L/P</th>
                            <th>KETERANGAN</th>
                            <th style="width: 160px; text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) { 
                                $badge_class = ($row['keterangan'] == 'Baru') ? 'badge' : 'badge ganti';
                        ?>
                        <tr>
                            <td style="text-align: center; color: var(--text-blue-grey);"><?php echo $no++; ?></td>
                            <td style="color: var(--imigrasi-dark); font-weight: 600;"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                            <td style="color: var(--text-blue-grey);"><?php echo htmlspecialchars($row['tahun_lahir'] . ' - ' . $row['bulan_lahir'] . ' - ' . $row['tanggal_lahir']); ?></td>
                            <td style="text-align: center; color: var(--text-blue-grey);"><?php echo htmlspecialchars($row['dokumen']); ?></td>
                            <td style="text-align: center; font-weight: 600;"><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                            <td><span class="<?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['keterangan']); ?></span></td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="btn-action btn-edit">Edit</a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus data permohonan ini?');">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 40px; color: var(--text-blue-grey);'>Data tidak ditemukan atau basis data kosong.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalTambah" class="modal-overlay">
        <div class="modal-content">
            <h2 class="form-title">Input Dokumen Baru</h2>
            
            <form action="data_permohonan.php" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap Pemohon</label>
                    <input type="text" name="nama_pemohon" class="form-control" placeholder="Contoh: Rabiyatussaajiah" required>
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
                    <label>Jumlah Berkas Dokumen</label>
                    <input type="number" name="dokumen" class="form-control" value="1" min="1" required>
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
                    <label>Keterangan Berkas</label>
                    <select name="keterangan" class="form-control" required>
                        <option value="Baru">Baru</option>
                        <option value="Penggantian">Penggantian</option>
                    </select>
                </div>

                <div class="btn-area">
                    <button type="button" id="btnTutupModal" class="btn btn-cancel btn-modal">Batal</button>
                    <button type="submit" name="submit_tambah" class="btn btn-search btn-modal">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalTambah');
        const btnBuka = document.getElementById('btnBukaModal');
        const btnTutup = document.getElementById('btnTutupModal');

        btnBuka.addEventListener('click', () => { modal.style.display = 'flex'; });
        btnTutup.addEventListener('click', () => { modal.style.display = 'none'; });
        window.addEventListener('click', (e) => { if (e.target === modal) { modal.style.display = 'none'; } });
    </script>

</body>
</html>