<?php
/** Isolated regression harness. No bootstrap, config, credentials or live DB.
 * Run: C:/xampp/php/php.exe tests/backend_auth_regression.php
 * Source is loaded into a test namespace so mysqli/session functions are mocked.
 */
namespace BackendAuthRegression;

class Session {
    public static array $data = [];
    public static function get($key, $default = null) { return self::$data[$key] ?? $default; }
    public static function set($key, $value): void { self::$data[$key] = $value; }
    public static function has($key): bool { return isset(self::$data[$key]); }
    public static function start(): void {}
    public static function destroy(): void { self::$data = []; }
}
class Result {
    public function __construct(public array $rows) {}
}
function mysqli_fetch_assoc($result): ?array { return array_shift($result->rows); }
class Database {
    public static bool $locked = false;
    public static bool $allowLock = true;
    public static bool $failWrite = false;
    public static int $writes = 0;
    public static function query($sql, $types = '', $params = []): Result {
        if (str_contains($sql, 'FROM login_throttle')) return new Result([]);
        if (str_contains($sql, 'RELEASE_LOCK')) { self::$locked = false; return new Result([['released' => 1]]); }
        if (str_contains($sql, 'GET_LOCK')) {
            self::$locked = self::$allowLock;
            return new Result([['acquired' => self::$locked ? 1 : 0]]);
        }
        if (str_contains($sql, 'COUNT(*)')) {
            expect(self::$locked, 'Count performed outside lock');
            $count = count(array_filter(User::$rows, fn($r) => $r['role'] === 'superadmin' && $r['id'] !== $params[0]));
            return new Result([['t' => $count]]);
        }
        throw new \RuntimeException('Unexpected mock query');
    }
    public static function exec($sql, $types = '', $params = []): bool {
        if (str_contains($sql, 'login_throttle')) return true;
        expect(self::$locked, 'Mutation performed outside lock');
        self::$writes++;
        if (self::$failWrite) return false;
        if (str_starts_with($sql, 'UPDATE users SET role')) {
            User::$rows[$params[2]]['role'] = $params[0];
            User::$rows[$params[2]]['akses_dokumen'] = $params[1];
        } elseif (str_starts_with($sql, 'DELETE FROM users')) { unset(User::$rows[$params[0]]); }
        else throw new \RuntimeException('Unexpected mock mutation');
        return true;
    }
}
class User {
    public static int $lookups = 0;
    public static function findByLogin($identifier): ?array { self::$lookups++; return self::$rows[1] ?? null; }
    public static function delete($id): bool { return Database::exec('DELETE FROM users WHERE id = ?', 'i', [$id]); }
    public static array $rows = [];
    public static bool $fail = false;
    public static function find($id): ?array {
        if (self::$fail) throw new \RuntimeException('Mock unavailable');
        return self::$rows[$id] ?? null;
    }
}
$GLOBALS['rotations'] = 0;
function session_regenerate_id($delete = false): bool {
    if (!$delete) throw new \RuntimeException('Old SID must be deleted');
    $GLOBALS['rotations']++;
    return true;
}
class ViewRendered extends \RuntimeException {}
class Redirected extends \RuntimeException {}
class Csrf { public static function verify(): void {} }
function header($value): void { throw new Redirected($value); }
function catatLog(...$args): void {}
function password_verify($plain, $hash): bool { return $plain === 'fixture-password' && $hash === 'mock-hash-one'; }
function loadSource(string $relative): void {
    $source = file_get_contents(__DIR__ . '/../' . $relative);
    // Replace only the view boundary; execute actual controller logic with mocks.
    $source = str_replace("require __DIR__ . '/../Views/auth/login.php';", 'throw new \\BackendAuthRegression\\ViewRendered($error);', $source);
    eval('namespace ' . __NAMESPACE__ . ';' . substr($source, 5));
}
loadSource('app/Core/Auth.php');
loadSource('app/Controllers/AuthController.php');
loadSource('app/Controllers/UserController.php');
function expect($condition, string $message): void {
    if (!$condition) throw new \RuntimeException($message);
}
function invoke($object, string $method, ...$args) {
    return (new \ReflectionMethod($object, $method))->invoke($object, ...$args);
}
$tests = [];
$tests['untrusted forwarded header cannot change throttle IP'] = function () {
    putenv('TRUSTED_PROXIES');
    $_SERVER = ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '203.0.113.20'];
    expect(invoke(new AuthController(), 'clientIp') === '127.0.0.1', 'Untrusted XFF was accepted');
};
$tests['trusted proxy chain uses nearest untrusted address'] = function () {
    putenv('TRUSTED_PROXIES=127.0.0.1,192.0.2.10');
    $_SERVER = ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_X_FORWARDED_FOR' => '198.51.100.99,203.0.113.20,192.0.2.10'];
    expect(invoke(new AuthController(), 'clientIp') === '203.0.113.20', 'Spoofed leftmost address was accepted');
    $_SERVER['HTTP_X_FORWARDED_FOR'] = 'garbage,203.0.113.20';
    expect(invoke(new AuthController(), 'clientIp') === '127.0.0.1', 'Malformed chain not rejected');
    $_SERVER['HTTP_X_FORWARDED_FOR'] = ['203.0.113.20'];
    expect(invoke(new AuthController(), 'clientIp') === '127.0.0.1', 'Non-string header not rejected');
    putenv('TRUSTED_PROXIES');
};
function fixture(): array {
    return ['id' => 1, 'password' => 'mock-hash-one', 'role' => 'superadmin', 'akses_dokumen' => '*',
        'nama' => 'Fixture', 'username' => 'fixture', 'jabatan' => 'Test', 'foto_profile' => null];
}
function authenticatedFixture(): void {
    Session::$data = []; User::$fail = false; User::$rows = [1 => fixture()];
    Auth::establishSession(User::$rows[1]);
}
$tests['legacy sessions are revoked, not silently upgraded'] = function () {
    Session::$data = ['login' => true, 'user_id' => 1, 'user_role' => 'superadmin'];
    User::$rows = [1 => fixture()];
    expect(!Auth::check() && Session::$data === [], 'Legacy session retains access');
};
$tests['authenticated session rotates SID before storing identity'] = function () {
    $before = $GLOBALS['rotations']; authenticatedFixture();
    expect($GLOBALS['rotations'] === $before + 1, 'SID not rotated');
    expect(Auth::check(), 'Fresh session rejected');
    expect(!in_array('mock-hash-one', Session::$data, true), 'Password hash copied into session');
};
$tests['role and document access refresh from current user'] = function () {
    authenticatedFixture(); $before = $GLOBALS['rotations'];
    User::$rows[1]['role'] = 'user'; User::$rows[1]['akses_dokumen'] = 'PASPOR';
    expect(Auth::check(), 'Role change need not log out');
    expect(Auth::role() === 'user' && Session::get('user_akses') === 'PASPOR', 'Privileges stale');
    expect($GLOBALS['rotations'] === $before + 1, 'Role change did not rotate SID');
};
$tests['deleted user loses session'] = function () {
    authenticatedFixture(); User::$rows = [];
    expect(!Auth::check() && Session::$data === [], 'Deleted user retains session');
};
$tests['password reset revokes existing session'] = function () {
    authenticatedFixture(); User::$rows[1]['password'] = 'mock-hash-two';
    expect(!Auth::check() && Session::$data === [], 'Reset password retains session');
};
$tests['lookup failure and invalid role fail closed'] = function () {
    authenticatedFixture(); User::$fail = true;
    expect(!Auth::check() && Session::$data === [], 'Lookup failure retains session');
    authenticatedFixture(); User::$rows[1]['role'] = 'unknown';
    expect(!Auth::check(), 'Unknown role accepted');
};
$tests['login credentials reject arrays and missing fields'] = function () {
    foreach ([[], ['user_input' => [], 'password' => 'test'], ['user_input' => 'x', 'password' => []],
        ['user_input' => '   ', 'password' => 'test'], ['user_input' => 12, 'password' => false]] as $post) {
        $_POST = $post;
        expect(invoke(new AuthController(), 'loginInput') === null, 'Invalid credentials accepted');
    }
    $_POST = ['user_input' => ' fixture ', 'password' => ' spaced password '];
    expect(invoke(new AuthController(), 'loginInput') === ['fixture', ' spaced password '], 'Password altered');
};
function guardFixture(): void {
    authenticatedFixture(); Database::$allowLock = true; Database::$failWrite = false; Database::$writes = 0;
}
function blockedMutation(int $id, ?string $role): void {
    try { invoke(new UserController(), 'mutateAccount', $id, $role, '*'); }
    catch (\DomainException $e) { return; }
    throw new \RuntimeException('Mutation was not blocked');
}
$tests['last superadmin demotion blocked but unchanged role allowed'] = function () {
    guardFixture(); blockedMutation(1, 'viewer');
    expect(Database::$writes === 0 && !Database::$locked, 'Blocked demotion wrote or leaked lock');
    expect(invoke(new UserController(), 'mutateAccount', 1, 'superadmin', '*'), 'Same role rejected');
};
$tests['ordinary user and non-last superadmin can change role'] = function () {
    guardFixture(); User::$rows[2] = array_replace(fixture(), ['id' => 2, 'role' => 'user']);
    expect(invoke(new UserController(), 'mutateAccount', 2, 'admin', '*'), 'Ordinary role update rejected');
    User::$rows[2]['role'] = 'superadmin';
    expect(invoke(new UserController(), 'mutateAccount', 2, 'viewer', '*'), 'Non-last demotion rejected');
    expect(!Database::$locked, 'Lock leaked');
};
$tests['serialized demotions preserve at least one superadmin'] = function () {
    guardFixture(); User::$rows[2] = array_replace(fixture(), ['id' => 2]);
    expect(invoke(new UserController(), 'mutateAccount', 2, 'viewer', '*'), 'First demotion failed');
    blockedMutation(1, 'viewer');
    expect(User::$rows[1]['role'] === 'superadmin' && Database::$writes === 1, 'Last admin removed');
};
$tests['delete shares lock and blocks self deletion'] = function () {
    guardFixture(); blockedMutation(1, null);
    User::$rows[2] = array_replace(fixture(), ['id' => 2]);
    expect(invoke(new UserController(), 'mutateAccount', 2, null), 'Non-last deletion rejected');
    expect(!isset(User::$rows[2]) && !Database::$locked, 'Delete failed or lock leaked');
};
$tests['lock timeout and failed mutation do not report success'] = function () {
    guardFixture(); Database::$allowLock = false;
    try { invoke(new UserController(), 'mutateAccount', 1, 'superadmin', '*'); }
    catch (\RuntimeException $e) { /* expected */ }
    expect(Database::$writes === 0, 'Mutation ran without lock');
    Database::$allowLock = true; Database::$failWrite = true;
    expect(!invoke(new UserController(), 'mutateAccount', 1, 'superadmin', '*'), 'Write failure reported success');
    expect(!Database::$locked, 'Failure leaked lock'); Database::$failWrite = false;
};
$tests['revoked actor cannot mutate after waiting for lock'] = function () {
    guardFixture(); User::$rows[1]['role'] = 'viewer';
    blockedMutation(1, 'superadmin');
    expect(Database::$writes === 0 && !Database::$locked, 'Revoked actor changed role');
};
$tests['controller malformed login renders error without account lookup'] = function () {
    Session::$data = []; User::$lookups = 0; $_GET = [];
    $_POST = ['login' => '1', 'user_input' => ['bad'], 'password' => ['bad']];
    $rendered = false;
    try { (new AuthController())->login(); }
    catch (ViewRendered $e) { $rendered = $e->getMessage() !== ''; }
    expect($rendered && User::$lookups === 0 && !Session::has('login'), 'Invalid login entered authentication');
};
$tests['controller successful mock login rotates session and binds revocation proof'] = function () {
    Session::$data = []; User::$rows = [1 => fixture()]; User::$fail = false;
    $_POST = ['login' => '1', 'user_input' => 'fixture', 'password' => 'fixture-password'];
    $before = $GLOBALS['rotations']; $redirected = false;
    try { (new AuthController())->login(); }
    catch (Redirected $e) { $redirected = $e->getMessage() === 'Location: dashboard_super.php'; }
    expect($redirected && $GLOBALS['rotations'] === $before + 1 && Auth::check(), 'Mock login not securely established');
};
$failed = 0;
foreach ($tests as $name => $test) {
    try { $test(); echo "PASS $name\n"; }
    catch (\Throwable $e) { $failed++; echo "FAIL $name: {$e->getMessage()}\n"; }
}
echo count($tests) . " tests, $failed failures; isolated mocks, no live DB\n";
exit($failed ? 1 : 0);
