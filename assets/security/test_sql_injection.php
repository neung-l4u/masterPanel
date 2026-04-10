<?php
/**
 * SQL Injection Test Script
 * ทดสอบว่า QueryBuilder ป้องกัน SQL Injection ได้จริงหรือไม่
 * 
 * วิธีรัน: php assets/security/test_sql_injection.php
 */

require_once __DIR__ . '/QueryBuilder.php';
require_once __DIR__ . '/Sanitizer.php';

echo "==========================================================\n";
echo "  SQL Injection & XSS Protection Test\n";
echo "==========================================================\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$name}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$name}\n";
        $failed++;
    }
}

// ============================================================
// PART 1: SQL Injection Tests — QueryBuilder
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 1: SQL Injection Protection (QueryBuilder)\n";
echo "──────────────────────────────────────────────────────────\n\n";

// --- Test 1: Basic SQL Injection attempt ---
echo "[Test 1] Basic SQL Injection: ' OR 1=1 --\n";
$qb = new QueryBuilder();
$qb->eq('C.`department`', "' OR 1=1 --");
$where = $qb->getWhere();
$binds = $qb->getBinds();

test("WHERE ใช้ ? placeholder ไม่ใช่ค่าตรง", strpos($where, "' OR 1=1") === false);
test("WHERE clause = \" AND C.`department` = ?\"", $where === " AND C.`department` = ?");
test("Bind value เก็บ payload ดิบเป็น string ไม่ execute", $binds[0] === "' OR 1=1 --");
echo "\n";

// --- Test 2: UNION-based injection ---
echo "[Test 2] UNION-based Injection: ' UNION SELECT * FROM staffs --\n";
$qb2 = new QueryBuilder();
$qb2->eq('C.`employee`', "' UNION SELECT * FROM staffs --");
$where2 = $qb2->getWhere();
$binds2 = $qb2->getBinds();

test("WHERE ไม่มี UNION keyword", strpos($where2, "UNION") === false);
test("Payload ถูกเก็บใน binds เป็น string ธรรมดา", $binds2[0] === "' UNION SELECT * FROM staffs --");
echo "\n";

// --- Test 3: Stacked queries ---
echo "[Test 3] Stacked Queries: '; DROP TABLE checkin; --\n";
$qb3 = new QueryBuilder();
$qb3->eq('C.`department`', "'; DROP TABLE checkin; --");
$where3 = $qb3->getWhere();
$binds3 = $qb3->getBinds();

test("WHERE ไม่มี DROP TABLE", strpos($where3, "DROP") === false);
test("Bind value = \"'; DROP TABLE checkin; --\"", $binds3[0] === "'; DROP TABLE checkin; --");
echo "\n";

// --- Test 4: Boolean-based blind injection ---
echo "[Test 4] Boolean-based Blind: ' AND 1=1 AND 'a'='a\n";
$qb4 = new QueryBuilder();
$qb4->eq('C.`status`', "' AND 1=1 AND 'a'='a");
$where4 = $qb4->getWhere();
$binds4 = $qb4->getBinds();

test("WHERE มีแค่ 1 placeholder", substr_count($where4, '?') === 1);
test("ไม่มี AND 1=1 ใน WHERE clause", strpos($where4, "AND 1=1") === false);
echo "\n";

// --- Test 5: Time-based blind injection ---
echo "[Test 5] Time-based Blind: ' OR SLEEP(5) --\n";
$qb5 = new QueryBuilder();
$qb5->eq('C.`department`', "' OR SLEEP(5) --");
$where5 = $qb5->getWhere();
$binds5 = $qb5->getBinds();

test("WHERE ไม่มี SLEEP", strpos($where5, "SLEEP") === false);
test("Payload ถูก bind เป็น parameter", $binds5[0] === "' OR SLEEP(5) --");
echo "\n";

// --- Test 6: Multiple parameters injection ---
echo "[Test 6] Multiple Parameters — ทุกตัวต้องเป็น ? placeholder\n";
$qb6 = new QueryBuilder();
$qb6->eq('C.`department`', "IT' OR '1'='1")
    ->eq('C.`employee`', "admin'--")
    ->gte('DATE(C.`dayCheckIn`)', "2024-01-01' OR 1=1--")
    ->lte('DATE(C.`dayCheckIn`)', "2024-12-31'; DROP TABLE users;--");
$where6 = $qb6->getWhere();
$binds6 = $qb6->getBinds();

test("WHERE มี 4 placeholders (?)", substr_count($where6, '?') === 4);
test("Binds มี 4 ค่า", count($binds6) === 4);
test("ไม่มี OR ใน WHERE clause", strpos($where6, " OR ") === false);
test("ไม่มี DROP ใน WHERE clause", strpos($where6, "DROP") === false);
echo "\n";

// --- Test 7: Empty/null values should be skipped ---
echo "[Test 7] Empty Values — ค่าว่างต้องไม่สร้าง WHERE condition\n";
$qb7 = new QueryBuilder();
$qb7->eq('C.`department`', '')
    ->eq('C.`employee`', null)
    ->eq('C.`status`', 'active');
$where7 = $qb7->getWhere();
$binds7 = $qb7->getBinds();

test("WHERE มีแค่ 1 condition (status)", substr_count($where7, '?') === 1);
test("Binds มีแค่ 1 ค่า", count($binds7) === 1);
test("Bind value = 'active'", $binds7[0] === 'active');
echo "\n";

// --- Test 8: LIKE injection ---
echo "[Test 8] LIKE Injection: %' OR 1=1 --\n";
$qb8 = new QueryBuilder();
$qb8->like('C.`employee`', "%' OR 1=1 --");
$where8 = $qb8->getWhere();
$binds8 = $qb8->getBinds();

test("WHERE ใช้ LIKE ?", strpos($where8, "LIKE ?") !== false);
test("Payload ถูก wrap ด้วย % เป็น bind value", $binds8[0] === "%%' OR 1=1 --%");
echo "\n";

// --- Test 9: raw() should NOT be used with user input ---
echo "[Test 9] raw() — ใช้กับ fixed condition เท่านั้น\n";
$qb9 = new QueryBuilder();
$qb9->raw("AND w.wSystemAmelia = 1");
$where9 = $qb9->getWhere();
$binds9 = $qb9->getBinds();

test("raw() ใส่ condition ตรงๆ ได้", strpos($where9, "AND w.wSystemAmelia = 1") !== false);
test("raw() ไม่มี binds", count($binds9) === 0);
echo "\n";

// ============================================================
// PART 2: XSS Protection Tests — Sanitizer
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 2: XSS Protection (Sanitizer)\n";
echo "──────────────────────────────────────────────────────────\n\n";

// --- Test 10: Basic XSS ---
echo "[Test 10] Basic XSS: <script>alert('XSS')</script>\n";
$xss1 = esc("<script>alert('XSS')</script>");
test("Script tag ถูก escape", strpos($xss1, '<script>') === false);
test("ผลลัพธ์ = escaped string", $xss1 === "&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;");
echo "\n";

// --- Test 11: Attribute injection ---
echo "[Test 11] Attribute Injection: \"><script>alert(1)</script>\n";
$xss2 = escAttr('"><script>alert(1)</script>');
test("Double quote ถูก escape", strpos($xss2, '"') === false);
test("Script tag ถูก escape", strpos($xss2, '<script>') === false);
echo "\n";

// --- Test 12: URL injection ---
echo "[Test 12] URL Injection: javascript:alert(1)\n";
$xss3 = escUrl("javascript:alert(1)");
test("javascript: protocol ถูกบล็อก → '#'", $xss3 === '#');
echo "\n";

echo "[Test 12b] Valid URL: https://example.com\n";
$xss3b = escUrl("https://example.com");
test("Valid URL ผ่านปกติ", $xss3b === 'https://example.com');
echo "\n";

// --- Test 13: Event handler injection ---
echo "[Test 13] Event Handler: \" onmouseover=\"alert(1)\n";
$xss4 = escAttr('" onmouseover="alert(1)');
test("Double quote ถูก escape ป้องกัน attribute breakout", strpos($xss4, '"') === false);
echo "\n";

// --- Test 14: Cookie XSS simulation ---
echo "[Test 14] Cookie XSS: <img src=x onerror=alert(1)>\n";
$xss5 = esc('<img src=x onerror=alert(1)>');
test("img tag ถูก escape", strpos($xss5, '<img') === false);
test("onerror ถูก escape", strpos($xss5, 'onerror') !== false); // text is there but escaped
test("< ถูกแปลงเป็น &lt;", strpos($xss5, '&lt;') !== false);
echo "\n";

// --- Test 15: clean() strips tags ---
echo "[Test 15] clean(): <b>bold</b><script>hack</script>\n";
$xss6 = clean('<b>bold</b><script>hack</script>');
test("HTML tags ถูกลบหมด", $xss6 === 'boldhack');
echo "\n";

// --- Test 16: Null handling ---
echo "[Test 16] Null/empty handling\n";
test("esc(null) = ''", esc(null) === '');
test("escUrl('') = ''", escUrl('') === '');
test("clean(null) = ''", clean(null) === '');
echo "\n";

// ============================================================
// PART 3: Comparison — Before vs After
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 3: Before vs After Comparison\n";
echo "──────────────────────────────────────────────────────────\n\n";

$malicious_department = "' OR 1=1 --";

// BEFORE (vulnerable — string concatenation)
$where_before = " AND C.`department` = '" . $malicious_department . "'";
$sql_before = "SELECT * FROM checkin C WHERE 1=1" . $where_before;

echo "[BEFORE] Vulnerable SQL:\n";
echo "  " . $sql_before . "\n";
echo "  → ⚠️  SQL ถูก inject! WHERE จะ return ทุก row\n\n";

// AFTER (safe — QueryBuilder)
$qb_after = new QueryBuilder();
$qb_after->eq('C.`department`', $malicious_department);
$sql_after = "SELECT * FROM checkin C WHERE 1=1" . $qb_after->getWhere();
$binds_after = $qb_after->getBinds();

echo "[AFTER] Safe SQL with QueryBuilder:\n";
echo "  SQL:  " . $sql_after . "\n";
echo "  Bind: [\"" . implode('", "', $binds_after) . "\"]\n";
echo "  → ✅ Payload ถูกส่งเป็น parameter, DB จะ match ตรงตัวกับ string \"' OR 1=1 --\"\n";
echo "     ไม่มี row ไหนมี department ชื่อนี้ → return 0 rows (ปลอดภัย)\n\n";

// ============================================================
// Summary
// ============================================================
echo "==========================================================\n";
$total = $passed + $failed;
echo "  Results: {$passed}/{$total} passed";
if ($failed > 0) {
    echo " ({$failed} FAILED)";
}
echo "\n";
echo "==========================================================\n";

exit($failed > 0 ? 1 : 0);
