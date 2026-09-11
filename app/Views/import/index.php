<?php
/**
 * View: form import CSV. Variabel dari ImportController::index(): $pesan, $pesan_error
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Arsip SPRI - TIKKIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="asset/css/common.css">
    <style>
        body { display: flex; height: 100vh; overflow: hidden; }
        .main-wrapper { display: flex; min-height: 100vh; width: 100%; }
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; background: var(--bg-main); }
        .top-header { height: 88px; background: rgba(244, 247, 250, 0.8); backdrop-filter: blur(10px); display: flex; align-items: center; padding: 0 40px; flex-shrink: 0; position: sticky; top: 0; z-index: 5; border-bottom: 1px solid var(--border-light);}
        .page-title { font-size: 1.5rem; font-weight: 700; color: var(--imigrasi-dark); }
        .content-wrapper { padding: 32px 40px; max-width: 900px; margin: 0 auto; width: 100%; }
        .card-container { background: var(--surface); padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 24px;}
        .card-title { font-size: 1.25rem; font-weight: 700; color: var(--imigrasi-dark); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--bg-main); padding-bottom: 15px;}
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: flex-start; gap: 12px; font-size: 0.95rem; line-height: 1.5;}
        .alert-info { background-color: #F0F9FF; border: 1px solid #BAE6FD; color: #0369A1; }
        .alert-success { background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #047857; }
        .alert-danger { background-color: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; }
        .form-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;}
        .form-group label { font-size: 0.9rem; color: var(--imigrasi-dark); font-weight: 600; }
        .form-control { padding: 14px 18px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 0.95rem; outline: none; background: #F8FAFC; color: var(--text-dark); font-family: 'Inter', sans-serif; transition: 0.2s; width: 100%;}
        .form-control:focus { border-color: var(--imigrasi-blue); background: var(--surface); box-shadow: 0 0 0 3px rgba(11,66,123,0.1);}
        input[type="file"] { background: var(--surface); padding: 10px; cursor: pointer; border: 2px dashed #CBD5E1; color: var(--text-blue-grey); }
        input[type="file"]:hover { border-color: var(--imigrasi-blue); background: #F0F9FF;}
        input[type="file"]::file-selector-button { background: var(--imigrasi-blue); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 15px; transition: 0.2s;}
        input[type="file"]::file-selector-button:hover { background: #08305A; }
        .btn-submit { padding: 14px 30px; background-color: #10B981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'Inter', sans-serif; transition: 0.3s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); font-size: 1rem; width: 100%; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;}
        .btn-submit:hover { background-color: #059669; transform: translateY(-2px); }
        .icon svg { width: 20px; height: 20px; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        <main class="main-content">
            <header class="top-header">
                <h1 class="page-title">Sistem Impor Data Pemohon</h1>
            </header>
            <div class="content-wrapper">
                <div class="card-container">
                    <div class="card-title">
                        <span class="icon" style="font-size: 1.5rem;"><?= icon('import') ?></span> Import Arsip Excel (CSV)
                    </div>
                    <div class="alert alert-info">
                        <div><?= icon('info') ?></div>
                        <div><strong>Info Sistem:</strong> Fitur ini otomatis mendeteksi dan <b>membuang baris Excel yang tidak memiliki nama</b> untuk mencegah database penuh dengan data hantu.</div>
                    </div>

                    <?php if ($pesan): ?><div class="alert alert-success"><div><?= icon('check') ?></div><div><?= $pesan ?></div></div><?php endif; ?>
                    <?php if ($pesan_error): ?><div class="alert alert-danger"><div><?= icon('alert') ?></div><div><?= $pesan_error ?></div></div><?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <?= Csrf::field() ?>
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
                        <button type="submit" name="import" class="btn-submit"><?= icon('upload') ?> Mulai Proses Sinkronisasi</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
