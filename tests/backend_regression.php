<?php
// Isolated: no bootstrap, config, credentials, database or HTTP login.
require __DIR__ . '/../app/Core/Helpers.php';
$passed = 0;
function check(bool $ok, string $message): void {
    global $passed;
    if (!$ok) throw new RuntimeException($message);
    $passed++;
}
function rejects(callable $fn): bool {
    try { $fn(); } catch (InvalidArgumentException $e) { return true; }
    return false;
}
foreach (['2024oops-02-29', '2024-1x-2y', '2024-2-29', ' 2024-02-29', '2024-02-30', [], 2024, null] as $bad) {
    check(norm_tgl_lahir($bad) === null, 'Malformed date accepted: ' . json_encode($bad));
}
check(norm_tgl_lahir('2024-02-29') === '2024-02-29', 'Leap date rejected');
class MasterDokumen { public static function allActive(): array { return [['kode'=>'1'], ['kode'=>'2']]; } }
class MasterKeterangan { public static function allActive(): array { return [['nama'=>'Baru']]; } }
class Auth {
    public static bool $full = false;
    public static string $code = '';
    public static function isFullDataAccess(): bool { return self::$full; }
    public static function aksesDokumenTunggal(): string { return self::$code; }
    public static function canAksesDokumen(string $code): bool { return self::$full || $code === self::$code; }
}
require __DIR__ . '/../app/Controllers/PermohonanController.php';
$extract = new ReflectionMethod(PermohonanController::class, 'extractFormData');
$controller = new PermohonanController();
$valid = ['nama_pemohon'=>'Test', 'no_permohonan'=>'A1', 'tgl_lahir'=>'2000-02-29', 'dokumen'=>'1', 'jenis_kelamin'=>'P', 'keterangan'=>'Baru', 'tanggal_input'=>'2024-01-01'];
foreach (['nama_pemohon'=>[], 'jenis_kelamin'=>'X', 'dokumen'=>'bogus', 'keterangan'=>'bogus', 'tanggal_input'=>'bogus', 'no_permohonan'=>str_repeat('A',51)] as $field=>$bad) {
    check(rejects(fn() => $extract->invoke($controller, array_replace($valid, [$field=>$bad]))), 'Invalid archive field accepted: '.$field);
}
check($extract->invoke($controller, $valid)['jenis_kelamin'] === 'P', 'Valid archive rejected');
check(function_exists('archive_scope'), 'Fail-closed archive scope missing');
foreach (['', '*', '1,2', 'bogus'] as $code) { Auth::$code = $code; check(archive_scope() === ['__none__'], 'Scope opened'); }
Auth::$code = '1'; check(archive_scope() === ['1'], 'Valid scope denied');
Auth::$full = true; check(archive_scope() === [], 'Full access denied'); Auth::$full = false;
check(function_exists('archive_report_filters'), 'Shared report filters missing');
check(archive_report_filters(['cari'=>' Test ', 'tgl_mulai'=>'2024-01-01', 'tgl_selesai'=>'2024-01-02']) === ['Test','2024-01-01','2024-01-02'], 'Report keyword/date lost');
foreach ([['cari'=>[]], ['tgl_mulai'=>[]], ['tgl_mulai'=>'2024-02-30'], ['tgl_mulai'=>'2024-02-02','tgl_selesai'=>'2024-01-01']] as $bad) check(rejects(fn()=>archive_report_filters($bad)), 'Bad report filter accepted');
check(function_exists('archive_integer'), 'Exact integer validator missing');
foreach (['1x', [], true, 1, '-1', '1.0', '01x'] as $bad) check(rejects(fn()=>archive_integer($bad, 'id', 0, 1000)), 'Bad integer accepted');
check(archive_integer('01', 'month', 1, 12) === 1, 'Padded month rejected');
check(function_exists('archive_location'), 'Location validator missing');
$lockers = [['id'=>3, 'nomor'=>'9']];
check(archive_location(['loker_id'=>'3','rak'=>'D','kotak'=>'100'], $lockers) === ['loker_id'=>3,'rak'=>'D','kotak'=>'100'], 'Valid location rejected');
foreach ([['loker_id'=>'3','rak'=>'E','kotak'=>'1'], ['loker_id'=>'3','rak'=>'A','kotak'=>'101'], ['loker_id'=>'99','rak'=>'A','kotak'=>'1'], ['loker_id'=>'0','rak'=>'','kotak'=>'']] as $bad) check(rejects(fn()=>archive_location($bad, $lockers)), 'Bad/new empty location accepted');
$legacy = ['loker_id'=>0,'rak'=>'','kotak'=>''];
check(archive_location(['loker_id'=>'0','rak'=>'','kotak'=>''], $lockers, $legacy) === $legacy, 'Unchanged legacy location not preserved');
check(function_exists('archive_require_record_access'), 'Old-record access guard missing');
Auth::$code = '1';
check(rejects(fn()=>archive_require_record_access(['dokumen'=>'2'])), 'Other document record accessible');
check(rejects(fn()=>archive_require_record_access(null)), 'Missing record accessible');
archive_require_record_access(['dokumen'=>'1']);
class Permohonan {
    public static ?array $old = null;
    public static array $writes = [];
    public static function withArchiveTransaction(callable $fn): mixed { return $fn(); }
    public static function find(int $id, bool $lock = false): ?array { return self::$old; }
    public static function update(int $id, array $data): bool { self::$writes[] = $data; return true; }
    public static function locationOccupied(array $data, int $id): bool { return false; }
}
class MasterLoker { public static function allActive(): array { return [['id'=>3,'nomor'=>'9']]; } }
check(method_exists(PermohonanController::class, 'mutate'), 'Transactional mutation seam missing');
$mutate = new ReflectionMethod(PermohonanController::class, 'mutate');
$post = $valid + ['update'=>'1','id_permohonan'=>'7','loker_id'=>'0','rak'=>'','kotak'=>''];
Permohonan::$old = ['dokumen'=>'2'] + $legacy;
check(rejects(fn()=>$mutate->invoke($controller, $post)), 'Update overwrote forbidden old document');
check(Permohonan::$writes === [], 'Denied update wrote data');
Permohonan::$old['dokumen'] = '1';
$mutate->invoke($controller, $post);
check(count(Permohonan::$writes) === 1, 'Allowed update did not write');
check(Permohonan::$writes[0]['loker_id'] === 0, 'Legacy location moved silently');
require __DIR__ . '/../app/Controllers/ImportController.php';
check(method_exists(ImportController::class, 'parseCsv'), 'CSV validation missing');
$parse = new ReflectionMethod(ImportController::class, 'parseCsv');
function csvStream(string $text) { $fp = fopen('php://temp', 'w+'); fwrite($fp, $text); rewind($fp); return $fp; }
$csvHeader = "no,nama,tahun,bulan,tanggal,dokumen,jenis_kelamin,keterangan\n";
$fp = csvStream($csvHeader . "1,Test,2000,2,29,1,P,Baru\n");
$parsed = $parse->invoke(new ImportController(), $fp, '02'); fclose($fp);
check(count($parsed) === 1 && $parsed[0]['tgl_lahir'] === '2000-02-29', 'CSV valid row changed');
check($parsed[0]['loker_id'] === 0 && $parsed[0]['rak'] === '', 'Historical CSV assigned invented physical location');
foreach (["bad,header\n", $csvHeader."1,Test,2000x,2,29,1,P,Baru\n", $csvHeader."1,Test,2000,2,29,1,X,Baru\n", $csvHeader."1,,2000,2,29,1,P,Baru\n", $csvHeader."1,Test,2000,2,29,1,P,Baru,extra\n"] as $csv) {
    $fp = csvStream($csv); check(rejects(fn()=>$parse->invoke(new ImportController(), $fp, '02')), 'Malformed CSV accepted'); fclose($fp);
}
$fp = csvStream($csvHeader); check(rejects(fn()=>$parse->invoke(new ImportController(), $fp, '13')), 'Bad import month accepted'); fclose($fp);
check(function_exists('archive_uploaded_file'), 'Upload validator missing');
foreach ([null, [], ['error'=>UPLOAD_ERR_PARTIAL], ['error'=>UPLOAD_ERR_OK,'tmp_name'=>'','name'=>'test.csv','size'=>1], ['error'=>UPLOAD_ERR_OK,'tmp_name'=>[]]] as $file) check(rejects(fn()=>archive_uploaded_file($file, 'csv', 1024)), 'Invalid upload accepted');
echo "PASS $passed assertions\n";
