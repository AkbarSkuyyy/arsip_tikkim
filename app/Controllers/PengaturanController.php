<?php
/**
 * Controller: pengaturan profil pengguna + identitas instansi.
 */
class PengaturanController
{
    public function index(): void
    {
        Auth::requireLogin();

        $pesan_sukses = "";
        $pesan_error = "";
        $user_id_aktif = Auth::userId();

        // 1. UPDATE PROFIL & FOTO
        if (isset($_POST['simpan_profil'])) {
            Csrf::verify();

            $nama = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);

            if (User::usernameTaken($username, $user_id_aktif)) {
                $pesan_error = "Username tersebut sudah digunakan pengguna lain!";
            } else {
                Database::exec("UPDATE users SET nama = ?, username = ?, email = ? WHERE id = ?", 'sssi', [$nama, $username, $email, $user_id_aktif]);
                Session::set('user_nama', $nama);
                Session::set('username', $username);
                $pesan_sukses = "Data profil berhasil diperbarui!";

                if (!empty($_FILES['foto']['name'])) {
                    $file = $_FILES['foto'];
                    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png'];

                    if (in_array($ekstensi, $allowed)) {
                        $target_dir = "asset/img/profile/";
                        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

                        $new_name = "profile_" . $user_id_aktif . "_" . time() . "." . $ekstensi;
                        if (move_uploaded_file($file['tmp_name'], $target_dir . $new_name)) {
                            Database::exec("UPDATE users SET foto_profile = ? WHERE id = ?", 'si', [$new_name, $user_id_aktif]);
                            Session::set('user_foto', $new_name);
                            $pesan_sukses = "Profil dan Foto berhasil diperbarui!";
                        }
                    } else {
                        $pesan_error = "Hanya boleh format JPG/PNG!";
                    }
                }
            }
        }

        // 2. UPDATE PASSWORD
        if (isset($_POST['simpan_password'])) {
            Csrf::verify();

            $pass_lama = $_POST['pass_lama'];
            $pass_baru = $_POST['pass_baru'];

            $dt_user = User::find($user_id_aktif);

            if (strlen($pass_baru) < 8) {
                $pesan_error = "Password baru minimal 8 karakter!";
            } elseif ($dt_user && password_verify($pass_lama, $dt_user['password'])) {
                $hashed = password_hash($pass_baru, PASSWORD_DEFAULT);
                User::updatePassword($user_id_aktif, $hashed);
                catatLog(Auth::userNama(), "Mengubah password akun sendiri");
                $pesan_sukses = "Password berhasil diperbarui!";
            } else {
                $pesan_error = "Password Saat Ini salah!";
            }
        }

        // 3. UPDATE PENGATURAN INSTANSI
        if (isset($_POST['simpan_aplikasi'])) {
            Csrf::verify();

            $data = [
                'kementerian' => trim($_POST['kementerian']),
                'instansi' => trim($_POST['instansi']),
                'seksi' => trim($_POST['seksi']),
                'pejabat_nama' => trim($_POST['pejabat_nama']),
                'pejabat_jabatan' => trim($_POST['pejabat_jabatan']),
                'pejabat_nip' => trim($_POST['pejabat_nip']),
            ];

            if (Pengaturan::update($data)) {
                $pesan_sukses = "Pengaturan instansi berhasil disimpan!";
            } else {
                $pesan_error = "Gagal menyimpan pengaturan instansi.";
            }
        }

        $data_profil = User::find($user_id_aktif) ?? ['nama' => '', 'username' => '', 'jabatan' => 'Administrator', 'email' => '', 'foto_profile' => 'default.png'];
        $data_instansi = Pengaturan::get() ?? ['kementerian' => '', 'instansi' => '', 'seksi' => '', 'pejabat_nama' => '', 'pejabat_jabatan' => '', 'pejabat_nip' => ''];

        require __DIR__ . '/../Views/pengaturan/index.php';
    }
}
