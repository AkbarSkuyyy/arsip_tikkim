<?php
/**
 * View: pengaturan profil + keamanan + instansi. Variabel dari
 * PengaturanController::index(): $pesan_sukses, $pesan_error, $data_profil, $data_instansi
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 40px; max-width: 1200px; margin: 0 auto; width: 100%; }
        .alert-success { background-color: #D1FAE5; color: #065F46; padding: 16px 24px; border-radius: 8px; font-weight: 500; margin-bottom: 24px; border: 1px solid #34D399; display: flex; align-items: center; gap: 10px; }
        .alert-error { background-color: #FEE2E2; color: #B91C1C; padding: 16px 24px; border-radius: 8px; font-weight: 500; margin-bottom: 24px; border: 1px solid #F87171; display: flex; align-items: center; gap: 10px; }
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
        .card { background: var(--surface); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); overflow: hidden; margin-bottom: 32px; }
        .card-header { padding: 24px 32px; border-bottom: 1px solid var(--border-light); background-color: #F8FAFC; }
        .card-title { font-size: 1.15rem; font-weight: 700; color: var(--imigrasi-dark); }
        .card-subtitle { font-size: 0.85rem; color: var(--text-blue-grey); margin-top: 4px; }
        .card-body { padding: 32px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border-light); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #F8FAFC; outline: none; transition: 0.2s; color: var(--text-dark); }
        .form-control:focus { border-color: var(--imigrasi-blue); background: #fff; box-shadow: 0 0 0 3px rgba(11, 66, 123, 0.1); }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; color: white; width: 100%; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,66,123,0.2); }
        .btn-primary { background-color: var(--imigrasi-blue); }
        .btn-dark { background-color: var(--imigrasi-dark); }
        .avatar-preview-wrapper img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid var(--surface); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .file-input { width: 100%; padding: 8px; border: 1px dashed var(--border-light); border-radius: 8px; background: #F8FAFC; cursor: pointer; }
        .file-input:hover { border-color: var(--imigrasi-blue); }
        @media (max-width: 992px) { .settings-grid { grid-template-columns: 1fr; gap: 0; } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Pengaturan Sistem</h1>
        </header>
        <div class="content-wrapper">
            <?php if ($pesan_sukses !== ""): ?>
                <div class="alert-success"><?= icon('check') ?> <?= $pesan_sukses ?></div>
            <?php endif; ?>
            <?php if ($pesan_error !== ""): ?>
                <div class="alert-error"><?= icon('alert') ?> <?= $pesan_error ?></div>
            <?php endif; ?>

            <div class="settings-grid">
                <div class="settings-column">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Profil Pengguna</h2>
                            <p class="card-subtitle">Kelola foto dan informasi data diri Anda.</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <?= Csrf::field() ?>
                                <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 30px;">
                                    <div class="avatar-preview-wrapper">
                                        <?php
                                        $foto = !empty($data_profil['foto_profile']) ? $data_profil['foto_profile'] : 'default.png';
                                        if (!file_exists('asset/img/profile/' . $foto)) { $foto = 'default.png'; }
                                        ?>
                                        <img id="preview-img" src="asset/img/profile/<?= htmlspecialchars($foto) ?>" alt="Foto Profil">
                                    </div>
                                    <div style="flex: 1;">
                                        <label style="display:block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Pilih Foto Baru</label>
                                        <input type="file" name="foto" id="inputFoto" accept="image/*" class="file-input" onchange="previewImage(event)">
                                        <p style="font-size: 0.75rem; color: var(--text-blue-grey); margin-top: 5px;">Maksimal 2MB (JPG/PNG)</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data_profil['nama']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data_profil['username']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Jabatan / Peran</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($data_profil['jabatan']) ?>" readonly style="background: #E2E8F0; color: #64748B;">
                                </div>
                                <div class="form-group">
                                    <label>Email Akses</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data_profil['email']) ?>" required>
                                </div>
                                <button type="submit" name="simpan_profil" class="btn btn-primary">Simpan Semua Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="settings-column">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Keamanan Akun</h2>
                            <p class="card-subtitle">Perbarui kata sandi untuk menjaga keamanan sistem.</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <?= Csrf::field() ?>
                                <div class="form-group">
                                    <label>Password Saat Ini</label>
                                    <input type="password" name="pass_lama" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" name="pass_baru" class="form-control" placeholder="Minimal 8 karakter" minlength="8" required>
                                </div>
                                <button type="submit" name="simpan_password" class="btn btn-dark">Perbarui Password</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Pengaturan Instansi & Cetak</h2>
                            <p class="card-subtitle">Informasi ini akan digunakan pada kop surat dan tanda tangan PDF.</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <?= Csrf::field() ?>
                                <div class="form-group">
                                    <label>Kementerian / Induk Lembaga</label>
                                    <input type="text" name="kementerian" class="form-control" value="<?= htmlspecialchars($data_instansi['kementerian']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Unit Pelaksana Teknis (UPT)</label>
                                    <input type="text" name="instansi" class="form-control" value="<?= htmlspecialchars($data_instansi['instansi']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Divisi / Seksi</label>
                                    <input type="text" name="seksi" class="form-control" value="<?= htmlspecialchars($data_instansi['seksi']) ?>" required>
                                </div>
                                <hr style="border: 0; border-top: 1px dashed var(--border-light); margin: 30px 0;">
                                <h3 style="font-size: 1rem; color: var(--imigrasi-dark); margin-bottom: 16px;">Pejabat Pengesah (Laporan Bulanan)</h3>
                                <div class="form-group">
                                    <label>Nama Pejabat</label>
                                    <input type="text" name="pejabat_nama" class="form-control" value="<?= htmlspecialchars($data_instansi['pejabat_nama']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Jabatan Penandatangan</label>
                                    <input type="text" name="pejabat_jabatan" class="form-control" value="<?= htmlspecialchars($data_instansi['pejabat_jabatan']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>NIP Pejabat</label>
                                    <input type="text" name="pejabat_nip" class="form-control" placeholder="Masukkan NIP (Opsional)" value="<?= htmlspecialchars($data_instansi['pejabat_nip']) ?>">
                                </div>
                                <button type="submit" name="simpan_aplikasi" class="btn btn-primary">Simpan Pengaturan Aplikasi</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                document.getElementById('preview-img').src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>
