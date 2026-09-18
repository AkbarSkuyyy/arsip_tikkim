<?php
/**
 * View: laporan bentuk tabel HTML untuk export .xls. Variabel dari
 * ExportController::excel(): $instansi, $tgl_mulai, $tgl_selesai, $rows
 */
?>
<table style="width:100%; border-collapse: collapse;">
    <tr><td colspan="7" style="text-align:center; font-size:16px; font-weight:bold;"><?= htmlspecialchars(strtoupper($instansi['kementerian']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td></tr>
    <tr><td colspan="7" style="text-align:center; font-size:14px; font-weight:bold;"><?= htmlspecialchars(strtoupper($instansi['instansi']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td></tr>
    <tr><td colspan="7" style="text-align:center; font-size:12px;"><?= htmlspecialchars($instansi['seksi'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td></tr>
    <tr><td colspan="7">&nbsp;</td></tr>
    <tr><td colspan="7" style="text-align:center; font-size:14px; font-weight:bold; text-decoration:underline;">LAPORAN REKAPITULASI DATA PERMOHONAN SPRI</td></tr>
    <tr><td colspan="7" style="text-align:center;">Periode: <?= date('d-m-Y', strtotime($tgl_mulai)) . " s/d " . date('d-m-Y', strtotime($tgl_selesai)) ?></td></tr>
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
        <?php $no = 1; foreach ($rows as $row): $tgl_lahir = !empty($row['tgl_lahir']) ? date('d/m/Y', strtotime($row['tgl_lahir'])) : '-'; ?>
        <tr style="text-align: center;">
            <td><?= $no++ ?></td>
            <td style="text-align: left;"><?= htmlspecialchars($row['nama_pemohon']) ?></td>
            <td><?= $tgl_lahir ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_input'])) ?></td>
            <td><?= htmlspecialchars($row['dokumen']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kelamin']) ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<table style="width:100%;">
    <tr>
        <td colspan="5"></td>
        <td colspan="2" style="text-align:center;">
            Sampit, <?= date('d-m-Y') ?><br>
            <?= htmlspecialchars($instansi['pejabat_jabatan'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?><br><br><br><br>
            <u><?= htmlspecialchars($instansi['pejabat_nama'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></u><br>
            NIP. <?= htmlspecialchars($instansi['pejabat_nip'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        </td>
    </tr>
</table>
