<?php
/**
 * generateStoreID.php
 *
 * สร้าง storeID รูปแบบใหม่ 4 ท่อน:  {ชื่อร้าน}-{ประเทศ}-{ประเภทร้าน}-{สาขา}
 * ตัวอย่าง: guaytiewruekhunpa-TH-TRT-0001
 *
 *   ท่อน 1  ชื่อร้าน  = shopName ตัวเล็กทั้งหมด ตัดสัญลักษณ์/ช่องว่างออก ยกเว้น "&" แปลงเป็น "and"
 *   ท่อน 2  ประเทศ   = AU, NZ, UK, US, CA, TH
 *   ท่อน 3  ประเภท   = RST (Restaurants & Takeaways)
 *                      TM  (Thai Massage)
 *                      TRT (Thai Restaurants & Takeaways)
 *                      00  (ไม่ทราบประเภท)
 *   ท่อน 4  สาขา     = เลข 4 หลัก นับจาก storeID เดิมบน monday.com
 *                      โดยดูเฉพาะรายการที่ "ชื่อร้าน + ประเทศ + ประเภท" ตรงกัน
 *                      ถ้าไม่เจอเลย = 0001
 *
 * หมายเหตุเรื่องการนับสาขา: อ้างอิงข้อมูลจริงบนบอร์ด TH ที่ร้าน "ภูกาษาวาเล่ย์"
 * มีทั้ง phukasavalley-TH-TRT-0001 และ phukasavalley-TH-TM-0001 (เลข 0001 ทั้งคู่)
 * แปลว่าเลขสาขาแยกนับตามประเภทร้าน ไม่ได้นับรวมทุกประเภทของชื่อร้านเดียวกัน
 */

if (!defined('STORE_ID_MONDAY_TOKEN')) {
    define('STORE_ID_MONDAY_TOKEN', 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjYxNzI4MTY4NiwiYWFpIjoxMSwidWlkIjo1NzY1NDA2MSwiaWFkIjoiMjAyNi0wMi0wNVQwMjowNDo0NC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MjIxMTY5MjAsInJnbiI6ImFwc2UyIn0.RIk109S-veeBTyvud8wxzCc656ytoFfVMgA5zyfo3sM');
}

/**
 * บอร์ด Projects ของแต่ละประเทศ พร้อม column id ของช่อง "Store ID"
 * column id ไม่เหมือนกันในแต่ละบอร์ด จึงต้อง map ไว้ตายตัว
 *
 * UK / NZ / CA มีช่อง "Store ID" 2 คอลัมน์ (ของเก่ารูปแบบ UK03-000028
 * กับของใหม่ที่ยังว่าง) ตรงนี้เลือกคอลัมน์ที่มีข้อมูลรูปแบบใหม่จริง
 * ถ้าอนาคตย้ายไปคอลัมน์ใหม่ ให้แก้ตรงนี้ที่เดียว
 */
function storeIdBoardConfig()
{
    return [
        'TH' => ['board' => '1943203205', 'column' => 'text_mm6vpm1k'],
        'AU' => ['board' => '1943203246', 'column' => 'text_mm6vbv48'],
        'NZ' => ['board' => '1943203264', 'column' => 'text_mm6ve2r2'],
        'CA' => ['board' => '1943203287', 'column' => 'text_mm6vxf5f'],
        'UK' => ['board' => '1943203305', 'column' => 'text_mm6vgsnp'],
        'US' => ['board' => '1940392927', 'column' => 'text_mm6v18bb'],
    ];
}

/**
 * ท่อน 3: แปลง formType เป็นรหัสประเภทร้าน
 */
function storeIdTypeCode($formType)
{
    $map = [
        'Restaurants & Takeaways'       => 'RST',
        'Thai Massage'                  => 'TM',
        'Thai Restaurants & Takeaways'  => 'TRT',
    ];

    $key = trim((string) $formType);
    // เผื่อค่าที่ส่งมาเป็น HTML entity (&amp;) จาก option ในฟอร์ม
    $key = html_entity_decode($key, ENT_QUOTES, 'UTF-8');

    return $map[$key] ?? '00';
}

/**
 * ท่อน 2: ปรับ country code ให้อยู่ในชุดที่ใช้จริง (AU, NZ, UK, US, CA, TH)
 */
function storeIdCountryCode($country)
{
    $code = strtoupper(trim((string) $country));
    // เผื่อมีการส่ง USA / GB มา ให้ normalise เข้าชุดเดียวกับบอร์ด
    $alias = ['USA' => 'US', 'GB' => 'UK'];

    return $alias[$code] ?? $code;
}

/**
 * ท่อน 1: แปลงชื่อร้านเป็น slug ตัวเล็ก
 *
 * - "&" -> "and"  (Family Hair & Spa -> familyhairandspa)
 * - ตัดสัญลักษณ์และช่องว่างออกทั้งหมด เหลือ a-z 0-9
 * - ชื่อภาษาไทยจะไม่เหลืออักษรใดๆ ผู้ใช้ต้องกรอก slug เอง (ดู $overrideSlug)
 */
function storeIdShopSlug($shopName)
{
    $name = (string) $shopName;
    $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');

    // เปลี่ยน & (รวมถึงที่มีช่องว่างรอบข้าง) เป็น and ก่อนตัดสัญลักษณ์
    $name = preg_replace('/\s*&\s*/u', 'and', $name);

    $name = mb_strtolower($name, 'UTF-8');

    // เหลือเฉพาะ a-z และ 0-9
    $slug = preg_replace('/[^a-z0-9]/', '', $name);

    return $slug ?? '';
}

/**
 * ดึง storeID ทั้งหมดจากบอร์ดของประเทศนั้น (รองรับ pagination)
 * คืนค่าเป็น array ของสตริง storeID
 */
function storeIdFetchExisting($boardId, $columnId, $token = STORE_ID_MONDAY_TOKEN)
{
    $values = [];
    $cursor = null;
    $guard  = 0;

    do {
        if ($cursor === null) {
            $query = 'query { boards(ids: [' . $boardId . ']) { items_page(limit: 500) { cursor items { column_values(ids: ["' . $columnId . '"]) { text } } } } }';
        } else {
            $query = 'query { next_items_page(limit: 500, cursor: "' . $cursor . '") { cursor items { column_values(ids: ["' . $columnId . '"]) { text } } } }';
        }

        $ch = curl_init('https://api.monday.com/v2');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['query' => $query]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: ' . $token,
                'API-Version: 2024-01',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $err) {
            error_log('[generateStoreID] curl error: ' . $err);
            return ['ok' => false, 'values' => $values];
        }

        $data = json_decode($raw, true);
        if (isset($data['errors'])) {
            error_log('[generateStoreID] monday error: ' . json_encode($data['errors']));
            return ['ok' => false, 'values' => $values];
        }

        if ($cursor === null) {
            $page = $data['data']['boards'][0]['items_page'] ?? null;
        } else {
            $page = $data['data']['next_items_page'] ?? null;
        }

        if (!$page) {
            break;
        }

        foreach (($page['items'] ?? []) as $item) {
            $text = $item['column_values'][0]['text'] ?? '';
            if ($text !== '' && $text !== null) {
                $values[] = trim($text);
            }
        }

        $cursor = $page['cursor'] ?? null;
        $guard++;
    } while ($cursor && $guard < 50);

    return ['ok' => true, 'values' => $values];
}

/**
 * หาเลขสาขาถัดไปจากรายการ storeID เดิม
 * นับเฉพาะตัวที่ prefix ("ชื่อร้าน-ประเทศ-ประเภท-") ตรงกันแบบ case-insensitive
 */
function storeIdNextBranchNo(array $existing, $prefix)
{
    $max = 0;
    $prefixLower = strtolower($prefix);
    $len = strlen($prefixLower);

    foreach ($existing as $value) {
        if (strncasecmp($value, $prefixLower, $len) !== 0) {
            continue;
        }
        $tail = substr($value, $len);
        // ต้องเป็นตัวเลขล้วนเท่านั้น กัน suffix แปลกๆ
        if ($tail !== '' && ctype_digit($tail)) {
            $n = (int) $tail;
            if ($n > $max) {
                $max = $n;
            }
        }
    }

    return $max + 1;
}

/**
 * สร้าง storeID เต็มรูปแบบ
 *
 * @param string $shopName     ชื่อร้านจากฟอร์ม
 * @param string $country      country_code จากฟอร์ม (AU/NZ/UK/US/CA/TH)
 * @param string $formType     Industrial Type จากฟอร์ม
 * @param string $overrideSlug ถ้าส่งมา จะใช้แทนการ slug ชื่อร้านอัตโนมัติ
 *                             (จำเป็นสำหรับร้านชื่อภาษาไทย ที่ slug อัตโนมัติจะได้ค่าว่าง)
 *
 * @return array{storeID:string, slug:string, country:string, type:string,
 *               branch:int, looked_up:bool, reason:string}
 */
function generateStoreID($shopName, $country, $formType, $overrideSlug = '')
{
    $slug    = trim((string) $overrideSlug) !== ''
        ? storeIdShopSlug($overrideSlug)
        : storeIdShopSlug($shopName);
    $cc      = storeIdCountryCode($country);
    $type    = storeIdTypeCode($formType);

    $result = [
        'storeID'   => '',
        'slug'      => $slug,
        'country'   => $cc,
        'type'      => $type,
        'branch'    => 1,
        'looked_up' => false,
        'reason'    => '',
    ];

    // ไม่มี slug (เช่นชื่อร้านเป็นภาษาไทยล้วน) -> สร้างไม่ได้ ต้องให้คนกรอก slug เอง
    if ($slug === '') {
        $result['reason'] = 'empty_slug';
        return $result;
    }

    $config = storeIdBoardConfig();
    if (!isset($config[$cc])) {
        // ไม่รู้จักประเทศ -> ยังสร้าง ID ให้ แต่เริ่มที่สาขา 1 เพราะหาข้อมูลเทียบไม่ได้
        $result['reason']  = 'unknown_country';
        $result['storeID'] = sprintf('%s-%s-%s-%04d', $slug, $cc, $type, 1);
        return $result;
    }

    $prefix = $slug . '-' . $cc . '-' . $type . '-';

    $fetched = storeIdFetchExisting($config[$cc]['board'], $config[$cc]['column']);
    if (!$fetched['ok']) {
        // ดึงข้อมูลไม่สำเร็จ -> ไม่เดาเลขสาขา ปล่อยว่างไว้ให้คนตรวจ
        // (ถ้าเดา 0001 ทั้งที่มีสาขาเดิมอยู่ จะได้ ID ซ้ำ)
        $result['reason'] = 'monday_unavailable';
        return $result;
    }

    $branch = storeIdNextBranchNo($fetched['values'], $prefix);

    $result['branch']    = $branch;
    $result['looked_up'] = true;
    $result['storeID']   = sprintf('%s-%s-%s-%04d', $slug, $cc, $type, $branch);

    return $result;
}
