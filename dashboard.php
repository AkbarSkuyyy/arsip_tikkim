<?php
// MULAI KUNCI KEAMANAN: Cek apakah user sudah login
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// AKHIR KUNCI KEAMANAN

require 'koneksi.php';

// 1. Mengambil total data keseluruhan
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan");
$total_data = mysqli_fetch_assoc($q_total)['total'] ?? 0;

// 2. Mengambil total 'Baru' (Menggunakan LIKE agar lebih fleksibel)
$q_baru = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE keterangan LIKE '%Baru%'");
$total_baru = mysqli_fetch_assoc($q_baru)['total'] ?? 0;

// 3. Mengambil total 'Penggantian' (Menggunakan LIKE untuk menangkap "Penggantian HB")
$q_ganti = mysqli_query($conn, "SELECT COUNT(*) as total FROM permohonan WHERE keterangan LIKE '%Penggantian%'");
$total_ganti = mysqli_fetch_assoc($q_ganti)['total'] ?? 0;

// 4. Mengambil data untuk Grafik Jenis Kelamin (Hanya filter L dan P agar grafik tidak error)
$q_jk = mysqli_query($conn, "SELECT jenis_kelamin, COUNT(*) as jumlah FROM permohonan WHERE jenis_kelamin IN ('L', 'P') GROUP BY jenis_kelamin");
$laki = 0; 
$perempuan = 0;

while($row = mysqli_fetch_assoc($q_jk)) {
    if(strtoupper($row['jenis_kelamin']) == 'L') $laki = $row['jumlah'];
    if(strtoupper($row['jenis_kelamin']) == 'P') $perempuan = $row['jumlah'];
}
$label_jk = ['Laki-laki', 'Perempuan'];
$data_jk = [$laki, $perempuan];

// 5. Mengambil data 5 permohonan terbaru untuk mini-tabel
$q_recent = mysqli_query($conn, "SELECT * FROM permohonan ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Arsip SPRI - Seksi TIKKIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .stat-value, .brand-main { font-family: 'Outfit', sans-serif; }

        /* ==================== MAIN CONTENT ==================== */
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; background: var(--bg-main);}
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; }

        /* ==================== STATS CARDS ==================== */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); position: relative; overflow: hidden; transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: var(--imigrasi-blue); }
        .stat-card:nth-child(2)::before { background: #10B981; } /* Hijau u/ Baru */
        .stat-card:nth-child(3)::before { background: var(--imigrasi-gold); }
        
        .stat-title { font-size: 0.85rem; color: var(--text-blue-grey); font-weight: 600; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--imigrasi-dark); line-height: 1; }

        /* ==================== CHARTS & TABLES ==================== */
        .charts-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; margin-bottom: 32px; }
        .card-container { background: var(--surface); padding: 28px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); }
        .card-title { font-size: 1.1rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
        
        .canvas-container { position: relative; height: 260px; width: 100%; display: flex; justify-content: center; }

        .btn-view-all { background: var(--imigrasi-blue); color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 500; font-size: 0.9rem; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 12px rgba(11,66,123,0.2); }
        .btn-view-all:hover { background: #08305A; transform: translateY(-1px); }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-blue-grey); font-weight: 600; font-size: 0.8rem; padding: 16px 20px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 16px 20px; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; font-weight: 500; color: var(--text-dark); }
        tbody tr:hover { background-color: rgba(244, 247, 250, 0.5); }
        .badge { background-color: rgba(11, 66, 123, 0.1); color: var(--imigrasi-blue); padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
        .badge-baru { background-color: rgba(16, 185, 129, 0.1); color: #059669; }
        .badge-ganti { background-color: rgba(245, 166, 35, 0.1); color: #D48806; }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Dashboard Analitik SPRI</h1>
            </header>

            <div class="content-wrapper">
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Total Keseluruhan</div>
                        <div class="stat-value"><?php echo (int)$total_data; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Permohonan Baru</div>
                        <div class="stat-value"><?php echo (int)$total_baru; ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Penggantian HB</div>
                        <div class="stat-value"><?php echo (int)$total_ganti; ?></div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="card-container">
                        <h2 class="card-title">Jenis Kelamin</h2>
                        <div class="canvas-container">
                            <canvas id="chartGender"></canvas>
                        </div>
                    </div>

                    <div class="card-container">
                        <h2 class="card-title">Status Permohonan SPRI</h2>
                        <div class="canvas-container">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card-container" style="padding: 0;">
                    <div class="card-title" style="padding: 24px 28px 0 28px;">
                        Daftar Permohonan Terbaru
                        <a href="data_permohonan.php" class="btn-view-all">Kelola Data &rarr;</a>
                    </div>
                    <table style="margin-top: 16px;">
                        <thead>
                            <tr>
                                <th style="padding-left: 28px;">NAMA PEMOHON</th>
                                <th style="text-align: center;">DOKUMEN</th>
                                <th style="text-align: center;">JENIS KELAMIN</th>
                                <th style="padding-right: 28px;">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($q_recent) > 0) {
                                while($row = mysqli_fetch_assoc($q_recent)) { 
                                    // Deteksi status pakai stripos agar aman dari perbedaan huruf besar/kecil
                                    $badge_class = (stripos($row['keterangan'], 'baru') !== false) ? 'badge badge-baru' : 'badge badge-ganti';
                            ?>
                            <tr>
                                <td style="padding-left: 28px; font-weight:600; color:var(--imigrasi-dark);"><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                                <td style="text-align: center; color: var(--text-blue-grey);"><?php echo htmlspecialchars($row['dokumen']); ?></td>
                                <td style="text-align: center; color: var(--text-blue-grey);"><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                <td style="padding-right: 28px;"><span class="<?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['keterangan']); ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align: center; padding: 30px; color: var(--text-blue-grey);'>Belum ada data terbaru.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

    <script>
        const labelsGender = <?php echo json_encode($label_jk); ?>;
        const dataGender = <?php echo json_encode($data_jk); ?>;
        
        // Memastikan variabel JS tidak undefined / error jika datanya nol
        const dataBaru = <?php echo $total_baru ? (int)$total_baru : 0; ?>;
        const dataGanti = <?php echo $total_ganti ? (int)$total_ganti : 0; ?>;

        // Skema Warna Imigrasi
        const colors = ['#0B427B', '#F5A623']; // Biru, Emas
        const barColors = ['#10B981', '#F5A623']; // Hijau, Emas

        // Chart Donat - Jenis Kelamin
        const ctxGender = document.getElementById('chartGender').getContext('2d');
        new Chart(ctxGender, {
            type: 'doughnut',
            data: {
                labels: labelsGender,
                datasets: [{
                    data: dataGender,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter' }, padding: 20 } }
                }
            }
        });

        // Chart Bar - Status Permohonan
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: ['Permohonan Baru', 'Penggantian HB'],
                datasets: [{
                    label: 'Jumlah SPRI',
                    data: [dataBaru, dataGanti],
                    backgroundColor: barColors,
                    borderRadius: 8,
                    borderWidth: 0,
                    barThickness: 60
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 10, font: { family: 'Inter' } },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', weight: 600 } }
                    }
                }
            }
        });
    </script>

</body>
</html>