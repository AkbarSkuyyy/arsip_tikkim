<?php
session_start();
require 'koneksi.php';

// 1. Ambil data instansi dari database agar otomatis
$q_instansi = mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1");
$instansi = mysqli_fetch_assoc($q_instansi);

// 2. Ambil parameter filter
$tgl_mulai = $_GET['tgl_mulai'];
$tgl_selesai = $_GET['tgl_selesai'];

// 3. Header Excel
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_SPRI_".date('Ymd').".xls");
?>

<table style="width:100%; border-collapse: collapse;">
    <tr><td colspan="7" style="text-align:center; font-size:16px; font-weight:bold;"><?php echo strtoupper($instansi['kementerian']); ?></td></tr>
    <tr><td colspan="7" style="text-align:center; font-size:14px; font-weight:bold;"><?php echo strtoupper($instansi['instansi']); ?></td></tr>
    <tr><td colspan="7" style="text-align:center; font-size:12px;"><?php echo $instansi['seksi']; ?></td></tr>
    <tr><td colspan="7">&nbsp;</td></tr> <tr><td colspan="7" style="text-align:center; font-size:14px; font-weight:bold; text-decoration:underline;">LAPORAN REKAPITULASI DATA PERMOHONAN SPRI</td></tr>
    <tr><td colspan="7" style="text-align:center;">Periode: <?php echo date('d-m-Y', strtotime($tgl_mulai)) . " s/d " . date('d-m-Y', strtotime($tgl_selesai)); ?></td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>
</table>

<table border="1" style="width:100%; border-collapse: collapse; font-family: Arial, sans-serif;">
    <thead>
        <tr style="background-color: #0A2540; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th style="width: 40px; padding: 10px;">NO</th>
            <th style="padding: 10px;">NAMA PEMOHON</th>
            <th style="padding: 10px;">TGL LAHIR</th>
            <th style="padding: 10px;">TGL INPUT</th>
            <th style="padding: 10px;">DOK</th>
            <th style="padding: 10px;">L/P</th>
            <th style="padding: 10px;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $query = "SELECT * FROM permohonan WHERE tanggal_input BETWEEN '$tgl_mulai' AND '$tgl_selesai' ORDER BY tanggal_input ASC";
        $result = mysqli_query($conn, $query);
        $no = 1;
        while($row = mysqli_fetch_assoc($result)): 
            $tgl_lahir = $row['tanggal_lahir'] . "/" . $row['bulan_lahir'] . "/" . $row['tahun_lahir'];
        ?>
        <tr style="text-align: center;">
            <td><?php echo $no++; ?></td>
            <td style="text-align: left;"><?php echo $row['nama_pemohon']; ?></td>
            <td><?php echo $tgl_lahir; ?></td>
            <td><?php echo date('d/m/Y', strtotime($row['tanggal_input'])); ?></td>
            <td><?php echo $row['dokumen']; ?></td>
            <td><?php echo $row['jenis_kelamin']; ?></td>
            <td><?php echo $row['keterangan']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<br>
<table style="width:100%;">
    <tr>
        <td colspan="5"></td>
        <td colspan="2" style="text-align:center;">
            Sampit, <?php echo date('d-m-Y'); ?><br>
            <?php echo $instansi['pejabat_jabatan']; ?><br><br><br><br>
            <u><?php echo $instansi['pejabat_nama']; ?></u><br>
            NIP. <?php echo $instansi['pejabat_nip']; ?>
        </td>
    </tr>
</table>