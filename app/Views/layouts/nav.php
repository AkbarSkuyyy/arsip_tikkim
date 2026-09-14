<?php
/**
 * Dispatcher sidebar: pilih sidebar staf vs super berdasarkan peran + mode UI.
 *
 * Mode 'super' hanya untuk admin; mode 'staff' untuk staf dan admin yang
 * memilih tampilan staf (lewat "Kembali ke Tampilan Staf"). Mode di-set oleh
 * DashboardController (staff) dan DashboardSuperController (super), sehingga
 * sidebar konsisten di SEMUA halaman selama satu mode — tidak berubah-ubah.
 */
$use_super = Auth::isSuperadmin() && Session::get('ui_mode', 'staff') === 'super';
include __DIR__ . '/' . ($use_super ? 'sidebar_super.php' : 'sidebar.php');
