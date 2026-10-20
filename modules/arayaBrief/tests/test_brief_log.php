<?php
date_default_timezone_set("Asia/Bangkok");
require_once __DIR__ . '/../models/briefLog.php';

$passed = 0; $failed = 0;
function test($name, $condition) {
    global $passed, $failed;
    if ($condition) { echo "  PASS: {$name}\n"; $passed++; }
    else            { echo "  FAIL: {$name}\n"; $failed++; }
}

set_exception_handler(function ($e) {
    echo "  FAIL: uncaught exception: " . $e->getMessage() . "\n";
    echo "\nPassed: 0  Failed: 1\n";
    exit(1);
});

echo "== schema ==\n";
$sql = arayaBriefSchemaSql();
test("table is l4u_ prefixed", str_contains($sql, 'l4u_araya_brief_logs'));
test("creation is idempotent", str_contains($sql, 'IF NOT EXISTS'));
test("brief_date is indexed", preg_match('/(KEY|INDEX).*brief_date/i', $sql) === 1);
test("no unique index on date alone, so a retry after a failure can be logged",
     preg_match('/UNIQUE[^\n]*brief_date/i', $sql) === 0);

echo "== already-sent check uses a bound query ==\n";
/** A stub recording what it was asked, so the query shape is testable with no DB. */
class FakeDb {
    public array $calls = [];
    private $next;
    public function __construct($next = null) { $this->next = $next; }
    public function query(string $sql, $params = []) {
        $this->calls[] = ['sql' => $sql, 'params' => $params];
        $row = $this->next;
        return new class($row) {
            public function __construct(private $row) {}
            public function fetchArray() { return $this->row; }
        };
    }
}

$db = new FakeDb(null);
test("no row means not sent", arayaBriefAlreadySentToday($db, '2026-10-20') === false);
test("the date is bound, never interpolated",
     $db->calls[0]['params'] === ['2026-10-20', 'sent']
     || $db->calls[0]['params'] === ['2026-10-20']);
test("the date does not appear inline in the SQL",
     !str_contains($db->calls[0]['sql'], '2026-10-20'));

$db = new FakeDb(['id' => 1]);
test("an existing sent row means already sent", arayaBriefAlreadySentToday($db, '2026-10-20') === true);

echo "== logging a send ==\n";
$db = new FakeDb(null);
arayaBriefLogSend($db, '2026-10-20', 'sent', 'the message body');
test("an insert was issued", str_contains(strtoupper($db->calls[0]['sql']), 'INSERT'));
test("every value is bound", count($db->calls[0]['params']) === 3);
test("the body does not appear inline in the SQL",
     !str_contains($db->calls[0]['sql'], 'the message body'));

$db = new FakeDb(null);
arayaBriefLogSend($db, '2026-10-20', 'failed', "quote ' and backslash \ survive");
test("a body with quotes is still bound, not escaped into the SQL",
     str_contains($db->calls[0]['params'][2], "quote '"));

echo "\nPassed: {$passed}  Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
