<?php
// File ini hanya dijalankan di dalam dashboard.php jika kondisi terpenuhi
$q_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");
?>

<div class="card-container" style="border: 2px solid var(--imigrasi-gold);">
    <h3>👑 Panel Super Admin</h3>
    <a href="register.php">Tambah Akun Baru</a>
    <table>
        <thead>
            <tr><th>Nama</th><th>Username</th><th>Jabatan</th></tr>
        </thead>
        <tbody>
            <?php while($usr = mysqli_fetch_assoc($q_users)) { ?>
            <tr>
                <td><?php echo $usr['nama']; ?></td>
                <td><?php echo $usr['username']; ?></td>
                <td><?php echo $usr['jabatan']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>