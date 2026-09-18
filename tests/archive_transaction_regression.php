<?php
// Fake connection only. Never load production Database or bootstrap.
require __DIR__ . '/../app/Models/Permohonan.php';
class FakeResult {
    public function __construct(private array $row) {}
    public function fetch_assoc(): array { return $this->row; }
}
class FakeConnection {
    public array $events = [];
    public string $engine = 'InnoDB';
    public bool $locked = true;
    public function query(string $sql): FakeResult {
        $this->events[] = $sql;
        if (str_contains($sql, 'GET_LOCK')) return new FakeResult(['locked'=>$this->locked ? 1 : 0]);
        return new FakeResult(['ENGINE'=>$this->engine]);
    }
    public function begin_transaction(): bool { $this->events[] = 'begin'; return true; }
    public function commit(): bool { $this->events[] = 'commit'; return true; }
    public function rollback(): bool { $this->events[] = 'rollback'; return true; }
}
class Database {
    public static FakeConnection $fake;
    public static array $queries = [];
    public static function conn(): FakeConnection { return self::$fake; }
    public static function query(string $sql, string $types, array $params): FakeResult {
        self::$queries[] = [$sql,$types,$params];
        return new FakeResult(['id'=>7]);
    }
}
function expect(bool $ok, string $msg): void { if (!$ok) throw new RuntimeException($msg); }
expect(method_exists(Permohonan::class, 'withArchiveTransaction'), 'Serialized transaction missing');
Database::$fake = new FakeConnection();
expect(Permohonan::withArchiveTransaction(fn()=>42) === 42, 'Result lost');
expect(in_array('begin', Database::$fake->events, true) && in_array('commit', Database::$fake->events, true), 'Success not committed');
expect(str_contains(end(Database::$fake->events), 'RELEASE_LOCK'), 'Lock not released');
Database::$fake = new FakeConnection();
try { Permohonan::withArchiveTransaction(function () { throw new LogicException('synthetic failure'); }); } catch (LogicException $e) {}
expect(in_array('rollback', Database::$fake->events, true) && !in_array('commit', Database::$fake->events, true), 'Failure not rolled back');
expect(str_contains(end(Database::$fake->events), 'RELEASE_LOCK'), 'Failure leaked lock');
foreach (['MyISAM', ''] as $engine) {
    Database::$fake = new FakeConnection(); Database::$fake->engine = $engine;
    $called = false;
    try { Permohonan::withArchiveTransaction(function () use (&$called) { $called = true; }); } catch (RuntimeException $e) {}
    expect(!$called && !in_array('begin', Database::$fake->events, true), 'Nontransactional engine accepted');
}
Database::$fake = new FakeConnection(); Database::$fake->locked = false;
$called = false;
try { Permohonan::withArchiveTransaction(function () use (&$called) { $called = true; }); } catch (RuntimeException $e) {}
expect(!$called, 'Lock failure allowed mutation');
Permohonan::find(7, true);
expect(str_contains(Database::$queries[0][0], 'FOR UPDATE'), 'Old record is not locked');
expect(Permohonan::locationOccupied(['loker_id'=>3,'rak'=>'A','kotak'=>'1'],7), 'Occupied location ignored');
expect(Database::$queries[1][2] === [3,'A',1,7], 'Occupancy excludes wrong ID or slot');
echo "PASS transaction commit/rollback/engine/lock/record/location tests\n";
