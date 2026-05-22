<?php
// MULAI KUNCI KEAMANAN: Cek apakah user sudah login
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
// AKHIR KUNCI KEAMANAN

require 'koneksi.php';

// Variabel untuk menampung pesan notifikasi
$pesan_sukses = "";
$pesan_error = "";

$user_id_aktif = $_SESSION['user_id'];

// ==========================================
// 1. PROSES UPDATE DATA JIKA TOMBOL DITEKAN
// ==========================================

// Logika ketika tombol simpan profil ditekan
if (isset($_POST['simpan_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah username yang baru dimasukkan sudah dipakai oleh akun LAIN
    $cek_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' AND id != '$user_id_aktif'");
    
    if (mysqli_num_rows($cek_username) > 0) {
        $pesan_error = "Username tersebut sudah digunakan pengguna lain!";
    } else {
        // Update ke database tabel users sesuai ID yang login
        $query_update = "UPDATE users SET nama='$nama', username='$username', email='$email' WHERE id='$user_id_aktif'";
        if (mysqli_query($conn, $query_update)) {
            $pesan_sukses = "Data profil berhasil diperbarui!";
            
            // Perbarui juga data di sesi agar nama di sidebar langsung berubah
            $_SESSION['user_nama'] = $nama;
            $_SESSION['username'] = $username;
        } else {
            $pesan_error = "Gagal memperbarui profil: " . mysqli_error($conn);
        }
    }
}

// Logika ketika tombol simpan pengaturan instansi ditekan
if (isset($_POST['simpan_aplikasi'])) {
    $kementerian = mysqli_real_escape_string($conn, $_POST['kementerian']);
    $instansi = mysqli_real_escape_string($conn, $_POST['instansi']);
    $seksi = mysqli_real_escape_string($conn, $_POST['seksi']);
    $pejabat_nama = mysqli_real_escape_string($conn, $_POST['pejabat_nama']);
    $pejabat_jabatan = mysqli_real_escape_string($conn, $_POST['pejabat_jabatan']);
    $pejabat_nip = mysqli_real_escape_string($conn, $_POST['pejabat_nip']);

    // Update ke database tabel pengaturan (asumsi pengaturan global disimpan di id = 1)
    $query_update = "UPDATE pengaturan SET 
                    kementerian='$kementerian', 
                    instansi='$instansi', 
                    seksi='$seksi', 
                    pejabat_nama='$pejabat_nama', 
                    pejabat_jabatan='$pejabat_jabatan', 
                    pejabat_nip='$pejabat_nip' 
                    WHERE id=1";
                    
    if (mysqli_query($conn, $query_update)) {
        $pesan_sukses = "Pengaturan instansi berhasil disimpan!";
    } else {
        $pesan_error = "Gagal menyimpan pengaturan: " . mysqli_error($conn);
    }
}

// ==========================================
// 2. MENGAMBIL DATA TERBARU DARI DATABASE
// ==========================================

// Ambil data User yang sedang login
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id_aktif'");
if ($q_user && mysqli_num_rows($q_user) > 0) {
    $data_profil = mysqli_fetch_assoc($q_user);
} else {
    $data_profil = ['nama' => '', 'username' => '', 'jabatan' => 'Administrator', 'email' => ''];
}

// Ambil data Pengaturan Instansi
$q_instansi = mysqli_query($conn, "SELECT * FROM pengaturan WHERE id = 1");
if ($q_instansi && mysqli_num_rows($q_instansi) > 0) {
    $data_instansi = mysqli_fetch_assoc($q_instansi);
} else {
    $data_instansi = ['kementerian' => '', 'instansi' => '', 'seksi' => '', 'pejabat_nama' => '', 'pejabat_jabatan' => '', 'pejabat_nip' => ''];
}

// Proses Upload & Crop Foto
if (isset($_POST['upload_foto_crop'])) {
    $img_data = $_POST['image_base64'];
    $img_data = str_replace('data:image/png;base64,', '', $img_data);
    $img_data = str_replace('data:image/jpeg;base64,', '', $img_data);
    $img_data = base64_decode($img_data);
    
    // Buat nama file unik
    $nama_file = "profile_" . $user_id_aktif . "_" . time() . ".jpg";
    $path = 'asset/img/profile/' . $nama_file;
    
    if (file_put_contents($path, $img_data)) {
        // Update database
        mysqli_query($conn, "UPDATE users SET foto_profile = '$nama_file' WHERE id = '$user_id_aktif'");
        $_SESSION['user_foto'] = $nama_file; // Update session
        $pesan_sukses = "Foto profil berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal menyimpan foto.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - Arsip SPRI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
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
            --success-green: #10B981;
            --error-red: #EF4444;
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
        
        h1, h2, h3, .brand-main { font-family: 'Outfit', sans-serif; }
        
        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light); }
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 40px; max-width: 1200px; margin: 0 auto; width: 100%; }

        /* Alert Notification */
        .alert-success { background-color: #D1FAE5; color: #065F46; padding: 16px 24px; border-radius: 8px; font-weight: 500; margin-bottom: 24px; border: 1px solid #34D399; display: flex; align-items: center; gap: 10px; }
        .alert-error { background-color: #FEE2E2; color: #B91C1C; padding: 16px 24px; border-radius: 8px; font-weight: 500; margin-bottom: 24px; border: 1px solid #F87171; display: flex; align-items: center; gap: 10px; }
        
        /* Settings Grid Layout */
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }

        /* Card Form Styling */
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

        /* Profile Avatar Placeholder */
        .avatar-upload { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .avatar-circle { width: 80px; height: 80px; background-color: var(--imigrasi-gold); color: var(--imigrasi-dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; font-family: 'Outfit'; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-transform: uppercase; }
        .avatar-text h4 { font-size: 1.1rem; color: var(--text-dark); margin-bottom: 4px; }
        .avatar-text p { font-size: 0.85rem; color: var(--text-blue-grey); }

        /* Responsive Layout untuk Layar Kecil */
        @media (max-width: 992px) {
            .settings-grid { grid-template-columns: 1fr; gap: 0; }
            .top-header { padding: 0 20px; }
            .content-wrapper { padding: 20px; }
        }
    </style>
</head>
<body>

    <!-- Memuat komponen Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <h1 class="page-title">Pengaturan Sistem</h1>
        </header>

        <div class="content-wrapper">
            
            <?php if($pesan_sukses != "") { ?>
                <div class="alert-success">
                    <span>✅</span> <?php echo $pesan_sukses; ?>
                </div>
            <?php } ?>
            
            <?php if($pesan_error != "") { ?>
                <div class="alert-error">
                    <span>⚠️</span> <?php echo $pesan_error; ?>
                </div>
            <?php } ?>

            <div class="settings-grid">
                
                <div class="settings-column">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Profil Pengguna</h2>
                            <p class="card-subtitle">Kelola informasi data diri dan kredensial akses Anda.</p>
                        </div>
                        <div class="card-body">
                            <div class="avatar-upload">
                                <div class="avatar-circle" id="preview-avatar" style="cursor:pointer; overflow:hidden;" onclick="document.getElementById('input_foto').click()">
                                    <?php if (!empty($data_profil['foto_profile']) && file_exists('asset/img/profile/' . $data_profil['foto_profile'])): ?>
                                        <img src="asset/img/profile/<?php echo $data_profil['foto_profile']; ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <?php echo substr($data_profil['nama'], 0, 1); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="avatar-text">
                                    <h4>Foto Profil</h4>
                                    <p>Klik lingkaran untuk mengganti foto</p>
                                    <input type="file" id="input_foto" accept="image/*" style="display:none;">
                                </div>
                            </div>
                            
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data_profil['nama']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" name="username" class="form-control" value="<?php echo isset($data_profil['username']) ? htmlspecialchars($data_profil['username']) : ''; ?>" pattern="^\S+$" title="Username tidak boleh menggunakan spasi" required>
                                </div>
                                <div class="form-group">
                                    <label>Jabatan / Peran</label>
                                    <input type="text" name="jabatan" class="form-control" value="<?php echo htmlspecialchars($data_profil['jabatan']); ?>" readonly style="background: #E2E8F0; color: #64748B;">
                                </div>
                                <div class="form-group">
                                    <label>Email Akses</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data_profil['email']); ?>" required>
                                </div>
                                <button type="submit" name="simpan_profil" class="btn btn-primary">Simpan Profil</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Keamanan Akun</h2>
                            <p class="card-subtitle">Perbarui kata sandi untuk menjaga keamanan sistem.</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Password Saat Ini</label>
                                    <input type="password" name="pass_lama" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" name="pass_baru" class="form-control" placeholder="Minimal 8 karakter">
                                </div>
                                <button type="button" class="btn btn-dark" onclick="alert('Fitur ubah password sedang dalam pengembangan.');">Perbarui Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="settings-column">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Pengaturan Instansi & Cetak</h2>
                            <p class="card-subtitle">Informasi ini akan digunakan pada kop surat dan tanda tangan PDF.</p>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="form-group">
                                    <label>Kementerian / Induk Lembaga</label>
                                    <input type="text" name="kementerian" class="form-control" value="<?php echo htmlspecialchars($data_instansi['kementerian']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Unit Pelaksana Teknis (UPT)</label>
                                    <input type="text" name="instansi" class="form-control" value="<?php echo htmlspecialchars($data_instansi['instansi']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Divisi / Seksi</label>
                                    <input type="text" name="seksi" class="form-control" value="<?php echo htmlspecialchars($data_instansi['seksi']); ?>" required>
                                </div>
                                
                                <hr style="border: 0; border-top: 1px dashed var(--border-light); margin: 30px 0;">
                                
                                <h3 style="font-size: 1rem; color: var(--imigrasi-dark); margin-bottom: 16px;">Pejabat Pengesah (Laporan Bulanan)</h3>
                                
                                <div class="form-group">
                                    <label>Nama Pejabat</label>
                                    <input type="text" name="pejabat_nama" class="form-control" value="<?php echo htmlspecialchars($data_instansi['pejabat_nama']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Jabatan Penandatangan</label>
                                    <input type="text" name="pejabat_jabatan" class="form-control" value="<?php echo htmlspecialchars($data_instansi['pejabat_jabatan']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>NIP Pejabat</label>
                                    <input type="text" name="pejabat_nip" class="form-control" placeholder="Masukkan NIP (Opsional)" value="<?php echo htmlspecialchars($data_instansi['pejabat_nip']); ?>">
                                </div>

                                <button type="submit" name="simpan_aplikasi" class="btn btn-primary">Simpan Pengaturan Aplikasi</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <div id="modalCrop" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; padding:20px; border-radius:10px; max-width:500px; width:90%;">
        <h3>Potong Foto</h3>
        <div style="width:100%; height:300px; overflow:hidden; margin:20px 0;">
            <img id="image_to_crop" style="max-width:100%;">
        </div>
        <button type="button" class="btn btn-dark" onclick="cropImage()">Simpan & Potong</button>
        <button type="button" class="btn" onclick="document.getElementById('modalCrop').style.display='none'" style="background:#64748B;">Batal</button>
    </div>
</div>

<form method="POST" id="form_crop" style="display:none;">
    <input type="hidden" name="image_base64" id="image_base64">
    <button type="submit" name="upload_foto_crop" id="btn_upload_final"></button>
</form>

<script>
    let cropper;
    const inputFoto = document.getElementById('input_foto');
    const imageToCrop = document.getElementById('image_to_crop');
    const modalCrop = document.getElementById('modalCrop');

    inputFoto.onchange = (e) => {
        const files = e.target.files;
        if (files && files.length > 0) {
            const reader = new FileReader();
            reader.onload = (e) => {
                imageToCrop.src = e.target.result;
                modalCrop.style.display = 'flex';
                if (cropper) cropper.destroy();
                cropper = new Cropper(imageToCrop, { aspectRatio: 1, viewMode: 1 });
            };
            reader.readAsDataURL(files[0]);
        }
    };

    function cropImage() {
        const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
        document.getElementById('image_base64').value = canvas.toDataURL('image/jpeg');
        document.getElementById('btn_upload_final').click();
    }
</script>
</body>
</html>