<?php
/**
 * Konfigurasi routing: memetakan setiap halaman lama (yang diakses langsung
 * sebagai file .php) ke [Controller, method]. Setiap file .php di root
 * (login.php, dashboard.php, dst) memanggil route ini lewat app/bootstrap.php.
 */
Router::get('login', 'AuthController', 'login');
Router::get('logout', 'AuthController', 'logout');
Router::get('register', 'UserController', 'register');
Router::get('edit_user', 'UserController', 'edit');
Router::get('reset_password', 'UserController', 'resetPassword');
Router::get('hapus_user', 'UserController', 'destroy');

Router::get('dashboard', 'DashboardController', 'index');
Router::get('dashboard_super', 'DashboardSuperController', 'index');

Router::get('data_permohonan', 'PermohonanController', 'index');
Router::get('laporan', 'LaporanController', 'index');
Router::get('export_excel', 'ExportController', 'excel');
Router::get('import_arsip', 'ImportController', 'index');
Router::get('master_data', 'MasterDataController', 'index');
Router::get('pengaturan', 'PengaturanController', 'index');
Router::get('audit_trail', 'AuditTrailController', 'index');

Router::get('backup', 'BackupController', 'download');
Router::get('backup_restore', 'BackupController', 'restoreForm');
Router::get('realtime', 'RealtimeController', 'index');
