<?php
// MULAI KUNCI KEAMANAN: Cek apakah user sudah login
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// AKHIR KUNCI KEAMANAN

require 'koneksi.php';

// Inisialisasi variabel filter
$cari = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$tgl_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($conn, $_GET['tgl_mulai']) : date('Y-m-01'); // Default awal bulan
$tgl_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($conn, $_GET['tgl_selesai']) : date('Y-m-t'); // Default akhir bulan

// Menyusun Query berdasarkan filter
$kondisi = "WHERE 1=1";

if ($cari != '') {
    $kondisi .= " AND nama_pemohon LIKE '%$cari%'";
}
if ($tgl_mulai != '' && $tgl_selesai != '') {
    // Membutuhkan kolom tanggal_input di database
    $kondisi .= " AND tanggal_input BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}

$query = "SELECT * FROM permohonan $kondisi ORDER BY tanggal_input ASC, id ASC";
$result = mysqli_query($conn, $query);

// Menghitung rekapitulasi data
$total_data = 0; $total_L = 0; $total_P = 0; $total_baru = 0; $total_ganti = 0;
$data_laporan = [];

// Tangkap error jika tabel tanggal_input belum ada
if($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data_laporan[] = $row;
        $total_data++;
        if ($row['jenis_kelamin'] == 'L') { $total_L++; } else { $total_P++; }
        if ($row['keterangan'] == 'Baru') { $total_baru++; } else { $total_ganti++; }
    }
}

// Format Teks Periode Laporan
$teks_periode = date('d/m/Y', strtotime($tgl_mulai)) . " - " . date('d/m/Y', strtotime($tgl_selesai));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi - Arsip SPRI</title>
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
            --excel-green: #107C41;
            --pdf-red: #E3242B;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Inter', sans-serif; background: var(--bg-main); color: var(--text-dark); display: flex; height: 100vh; overflow: hidden; }
        h1, h2, h3, .brand-main { font-family: 'Outfit', sans-serif; }

        /* Main Content Area */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 1400px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; align-items: center; }

        /* PANEL FILTER & EXPORT */
        .panel-kontrol { width: 100%; max-width: 1000px; background: var(--surface); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px; overflow: hidden; }
        .filter-form { display: flex; flex-wrap: wrap; gap: 16px; padding: 20px 24px; align-items: flex-end; }
        
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-blue-grey); }
        .form-control { padding: 10px 14px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; }
        .form-control:focus { border-color: var(--imigrasi-blue); }
        
        .btn { padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s; color: white; text-decoration: none; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .btn-primary { background-color: var(--imigrasi-blue); }
        .btn-pdf { background-color: var(--pdf-red); }
        .btn-excel { background-color: var(--excel-green); }
        
        .quick-filters { background: #F8FAFC; padding: 12px 24px; border-top: 1px solid var(--border-light); display: flex; gap: 12px; align-items: center; }
        .quick-btn { background: none; border: 1px solid var(--border-light); padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; color: var(--text-dark); font-weight: 500; background: white; }
        .quick-btn:hover { background: var(--border-light); }

        /* DOKUMEN LAPORAN (KERTAS A4) */
        .report-document { background: var(--surface); width: 100%; max-width: 1000px; padding: 60px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border-light); border-radius: 8px; margin-bottom: 40px; }
        
        .report-heading { text-align: center; margin-bottom: 30px; }
        .report-heading h3 { font-size: 1.4rem; margin-bottom: 8px; color: var(--imigrasi-dark); }
        .report-heading p { font-size: 0.95rem; color: var(--text-dark); font-weight: 600; }

        /* Mini Rekapitulasi di atas tabel */
        .rekap-box { display: flex; justify-content: center; gap: 24px; margin-bottom: 20px; padding: 12px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 6px; }
        .rekap-item { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); }
        .rekap-item span { color: var(--imigrasi-blue); }

        /* Tabel Laporan */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem; }
        th, td { border: 1px solid #CBD5E1; padding: 10px 14px; }
        th { background-color: var(--imigrasi-dark); color: white; text-align: center; font-weight: 600; letter-spacing: 0.5px; }
        td { color: var(--text-dark); font-weight: 500; }
        tbody tr:nth-child(even) { background-color: #F8FAFC; }

        /* PRINT CSS (KHUSUS PDF) */
        @media print {
            body { background: white; }
            .sidebar, .top-header, .panel-kontrol { display: none !important; }
            .main-content { overflow: visible; }
            .content-wrapper { padding: 0; }
            .report-document { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
            th { background-color: #0A2540 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .rekap-box, tbody tr:nth-child(even) { background-color: #F8FAFC !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Laporan & Rekapitulasi SPRI</h1>
        </header>

        <div class="content-wrapper">
            
            <div class="panel-kontrol">
                <form method="GET" action="laporan.php" class="filter-form" id="formFilter">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Cari Nama Pemohon</label>
                        <input type="text" name="cari" class="form-control" placeholder="Ketik nama..." value="<?php echo htmlspecialchars($cari); ?>">
                    </div>
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" value="<?php echo htmlspecialchars($tgl_mulai); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" value="<?php echo htmlspecialchars($tgl_selesai); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">🔍 Terapkan</button>
                    
                    <div style="width: 1px; height: 38px; background: var(--border-light); margin: 0 8px;"></div>
                    
                    <button type="button" class="btn btn-pdf" onclick="window.print()">📄 Cetak PDF</button>
                    <a href="export_excel.php?cari=<?php echo urlencode($cari); ?>&tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_selesai=<?php echo $tgl_selesai; ?>" 
                        class="btn btn-excel" style="background-color: var(--excel-green);">
                        <span>📊</span> Unduh Excel
                    </a>
                </form>

                <div class="quick-filters">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-blue-grey);">Filter Cepat:</span>
                    <button class="quick-btn" onclick="setFilterCepat('hari_ini')">Hari Ini</button>
                    <button class="quick-btn" onclick="setFilterCepat('bulan_ini')">Bulan Ini</button>
                    <button class="quick-btn" onclick="setFilterCepat('tahun_ini')">Tahun Ini</button>
                </div>
            </div>

            <div class="report-document" id="printableArea">

                <div class="report-heading">
                    <h3>REKAPITULASI DOKUMEN PERMOHONAN SPRI</h3>
                    <p>PERIODE: <?php echo $teks_periode; ?></p>
                </div>

                <div class="rekap-box">
                    <div class="rekap-item">Total: <span><?php echo $total_data; ?></span></div>
                    <div class="rekap-item">Laki-laki: <span><?php echo $total_L; ?></span></div>
                    <div class="rekap-item">Perempuan: <span><?php echo $total_P; ?></span></div>
                    <div class="rekap-item">Baru: <span><?php echo $total_baru; ?></span></div>
                    <div class="rekap-item">Penggantian: <span><?php echo $total_ganti; ?></span></div>
                </div>

                <table id="tabelLaporan">
                    <thead>
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>NAMA PEMOHON</th>
                            <th>TGL LAHIR</th>
                            <th>TGL INPUT</th>
                            <th style="width: 80px;">DOK</th>
                            <th style="width: 80px;">L/P</th>
                            <th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if(count($data_laporan) > 0) {
                            foreach($data_laporan as $row) { 
                                // Format tanggal lahir jadi DD/MM/YYYY
                                $tgl_lahir_format = str_pad($row['tanggal_lahir'], 2, "0", STR_PAD_LEFT) . "/" . 
                                                    str_pad($row['bulan_lahir'], 2, "0", STR_PAD_LEFT) . "/" . 
                                                    $row['tahun_lahir'];
                                
                                // Format tanggal input
                                $tgl_input_format = isset($row['tanggal_input']) ? date('d/m/Y', strtotime($row['tanggal_input'])) : '-';
                        ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_pemohon']); ?></td>
                            <td style="text-align: center;"><?php echo $tgl_lahir_format; ?></td>
                            <td style="text-align: center; color: var(--text-blue-grey);"><?php echo $tgl_input_format; ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($row['dokumen']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 30px;'>Tidak ada data pada periode atau pencarian ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </main>

    <script>
        // 1. Fungsi Filter Cepat (Harian, Bulanan, Tahunan)
        function setFilterCepat(jenis) {
            let today = new Date();
            let startDate, endDate;

            // Format YYYY-MM-DD
            const formatDate = (date) => {
                let d = new Date(date), month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;
                return [year, month, day].join('-');
            }

            if (jenis === 'hari_ini') {
                startDate = today;
                endDate = today;
            } else if (jenis === 'bulan_ini') {
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (jenis === 'tahun_ini') {
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
            }

            document.getElementById('tgl_mulai').value = formatDate(startDate);
            document.getElementById('tgl_selesai').value = formatDate(endDate);
            document.getElementById('formFilter').submit();
        }

        // 2. Fungsi Export HTML Table ke format Excel (.xls)
        function exportToExcel(tableID, filename = ''){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body>' + tableSelect.outerHTML.replace(/ /g, '%20') + '</body></html>';
            
            filename = filename?filename+'.xls':'excel_data.xls';
            downloadLink = document.createElement("a");
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }
    </script>

</body>
</html>