<?php
/**
 * Dispatcher sidebar: pilih sidebar staf vs super.
 *
 * Aturan deterministik (tidak bergantung pada ui_mode yang mudah bocor):
 *  - Superadmin + mode 'super' (default setelah login) -> sidebar super.
 *  - Selain itu (admin, petugas, viewer, atau superadmin yang memilih
 *    "Kembali ke Tampilan Staf") -> sidebar staf.
 *
 * Mode di-set oleh DashboardController (staff) dan DashboardSuperController
 * (super). Karena superadmin selalu diarahkan ke dashboard_super saat login,
 * ui_mode otomatis 'super'. Halaman lain tidak perlu set mode lagi — mode
 * sudah tersimpan konsisten di session.
 */
if (!Session::has('ui_mode')) {
    Session::set('ui_mode', Auth::isSuperadmin() ? 'super' : 'staff');
}

$use_super = Auth::isSuperadmin() && Session::get('ui_mode', 'staff') === 'super';
include __DIR__ . '/' . ($use_super ? 'sidebar_super.php' : 'sidebar.php');
