<?php
/**
 * Helper fungsi global lintas fitur: validasi tanggal, ikon SVG, log aktivitas.
 * Dipertahankan sebagai fungsi global (bukan static class) agar kompatibel
 * dengan pemanggilan langsung di semua Views (icon(), norm_tgl_lahir(), dst).
 */

function norm_tgl_lahir(mixed $value): ?string
{
    if (!is_string($value) || !preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $value)) return null;
    [$y, $m, $d] = explode('-', $value);
    $y = (int)$y; $m = (int)$m; $d = (int)$d;
    if ($y < 1900 || $y > ((int)date('Y') + 1)) return null;
    if (!checkdate($m, $d, $y)) return null;
    return sprintf('%04d-%02d-%02d', $y, $m, $d);
}

/** HTTP text fields are strings, never arrays, booleans or coerced numbers. */
function archive_text(mixed $value, string $field, int $max, bool $required = false): string
{
    if (!is_string($value)) throw new InvalidArgumentException("$field harus berupa teks.");
    $value = trim($value);
    if (($required && $value === '') || strlen($value) > $max || preg_match('/[\x00-\x1F\x7F]/', $value)) {
        throw new InvalidArgumentException("$field kosong, terlalu panjang, atau mengandung karakter tidak valid.");
    }
    return $value;
}

function archive_data(array $input): array
{
    $data = [];
    foreach (['nama_pemohon'=>255, 'no_permohonan'=>50, 'dokumen'=>50, 'jenis_kelamin'=>1, 'keterangan'=>100] as $key=>$max) {
        $data[$key] = archive_text($input[$key] ?? '', $key, $max, $key !== 'no_permohonan');
    }
    foreach (['tgl_lahir', 'tanggal_input'] as $key) {
        $data[$key] = norm_tgl_lahir($input[$key] ?? null);
        if ($data[$key] === null) throw new InvalidArgumentException("$key tidak valid (YYYY-MM-DD).");
    }
    if ($data['tgl_lahir'] > date('Y-m-d')) throw new InvalidArgumentException('Tanggal lahir tidak boleh di masa depan.');
    if (!in_array($data['jenis_kelamin'], ['L','P'], true)) throw new InvalidArgumentException('Jenis kelamin harus L atau P.');
    $codes = array_map(fn($row) => (string)$row['kode'], MasterDokumen::allActive());
    if (!in_array($data['dokumen'], $codes, true)) throw new InvalidArgumentException('Jenis dokumen tidak aktif atau tidak dikenal.');
    if (!in_array($data['keterangan'], array_column(MasterKeterangan::allActive(), 'nama'), true)) throw new InvalidArgumentException('Keterangan tidak aktif atau tidak dikenal.');
    return $data;
}

/** Empty filter means ALL: reserve it exclusively for full-access roles. */
function archive_scope(): array
{
    if (Auth::isFullDataAccess()) return [];
    $code = Auth::aksesDokumenTunggal();
    $codes = array_map(fn($row) => (string)$row['kode'], MasterDokumen::allActive());
    return $code !== '' && $code !== '*' && strpos($code, ',') === false && in_array($code, $codes, true) ? [$code] : ['__none__'];
}

function archive_report_filters(array $query): array
{
    $keyword = archive_text($query['cari'] ?? '', 'Pencarian', 255);
    $start = norm_tgl_lahir($query['tgl_mulai'] ?? date('Y-m-01'));
    $end = norm_tgl_lahir($query['tgl_selesai'] ?? date('Y-m-t'));
    if ($start === null || $end === null || $start > $end) throw new InvalidArgumentException('Rentang tanggal tidak valid.');
    return [$keyword, $start, $end];
}

function archive_integer(mixed $value, string $field, int $min, int $max): int
{
    if (!is_string($value) || !preg_match('/\A[0-9]{1,10}\z/', $value) || (int)$value < $min || (int)$value > $max) {
        throw new InvalidArgumentException("$field tidak valid.");
    }
    return (int)$value;
}

/** Keep historical locations only when unchanged; never silently relocate old files. */
function archive_location(array $input, array $lockers, ?array $old = null): array
{
    $location = [
        'loker_id' => archive_integer($input['loker_id'] ?? '', 'Loker', 0, 2147483647),
        'rak' => archive_text($input['rak'] ?? '', 'Rak', 5),
        'kotak' => archive_text($input['kotak'] ?? '', 'Urutan', 20),
    ];
    if ($old !== null && $location === ['loker_id'=>(int)$old['loker_id'], 'rak'=>(string)$old['rak'], 'kotak'=>(string)$old['kotak']]) return $location;
    foreach ($lockers as $locker) {
        if ((int)$locker['id'] !== $location['loker_id']) continue;
        $small = (int)$locker['nomor'] >= 9 && (int)$locker['nomor'] <= 11;
        $slot = archive_integer($location['kotak'], 'Urutan', 1, $small ? 100 : 200);
        if (!in_array($location['rak'], $small ? ['A','B','C','D'] : ['A','B','C','D','E'], true)) break;
        $location['kotak'] = (string)$slot;
        return $location;
    }
    throw new InvalidArgumentException('Lokasi harus menggunakan loker aktif dan rak/urutan dalam kapasitas.');
}

function archive_require_record_access(?array $record): void
{
    $scope = archive_scope();
    if (!$record || ($scope !== [] && !in_array((string)$record['dokumen'], $scope, true))) {
        throw new InvalidArgumentException('Data tidak ditemukan atau akses jenis dokumen ditolak.');
    }
}

/** Validate an uploaded file entry from $_FILES before any fopen. Never trust tmp_name/error blindly. */
function archive_uploaded_file(mixed $file, string $ext, int $maxBytes = 1048576): string
{
    if (!is_array($file) || !array_key_exists('error', $file)) {
        throw new InvalidArgumentException('Berkas unggahan tidak valid.');
    }
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Unggahan gagal (kode ' . (int)$error . ').');
    }
    $tmp = $file['tmp_name'] ?? '';
    $name = $file['name'] ?? '';
    $size = $file['size'] ?? 0;
    if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
        throw new InvalidArgumentException('Berkas sementara tidak valid.');
    }
    if (!is_string($name) || strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower($ext)) {
        throw new InvalidArgumentException('Ekstensi berkas harus .' . $ext . '.');
    }
    if (!is_numeric($size) || (int)$size <= 0 || (int)$size > $maxBytes) {
        throw new InvalidArgumentException('Ukuran berkas tidak valid atau melebihi batas.');
    }
    return $tmp;
}

function catatLog($user, $aksi): void
{
    if (trim((string)$user) === '' && Session::has('user_nama')) {
        $user = Session::get('user_nama');
    }
    Database::exec("INSERT INTO logs (user, aksi) VALUES (?, ?)", 'ss', [(string)$user, (string)$aksi]);
}

function flash_toast_script(): string
{
    $f = Session::flashGet();
    if (!$f) return '';
    $type = $f['type'] === 'error' ? 'error' : 'success';
    $msg  = htmlspecialchars($f['message'], ENT_QUOTES);
    return "<script>document.addEventListener('DOMContentLoaded', function(){ toast('$msg', '$type'); });</script>";
}

function icon(string $name): string
{
    static $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'folder'    => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'import'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'report'    => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'crown'     => '<path d="M2 18h20"/><path d="M3 8l4 3 5-6 5 6 4-3-2 9H5z"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'search'    => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'trash'     => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'save'      => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'refresh'   => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'check'     => '<polyline points="20 6 9 17 4 12"/>',
        'alert'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'info'      => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'printer'   => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'upload'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'download'  => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'database'  => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'key'       => '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'tag'       => '<path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12v-2a2 2 0 0 1 2-2h2M7 7h.01"/><path d="M7.5 7.5 20 3l-4.5 12.5-2-6-6-2z"/>',
        'file'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        'lock'      => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'archive'   => '<rect x="2" y="3" width="20" height="6" rx="1"/><path d="M4 9v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/>',
    ];
    if (!isset($icons[$name])) return '';
    return '<span class="icon"><svg viewBox="0 0 24 24">' . $icons[$name] . '</svg></span>';
}
