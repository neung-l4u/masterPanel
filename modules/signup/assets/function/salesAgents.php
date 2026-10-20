<?php
/**
 * รายชื่อ Sales Agent สำหรับ dropdown #byAgent
 *
 * ดึงจากตาราง staffs โดยตรง (teamID = 3 = ทีมขาย) แทนการ hardcode ไว้ใน HTML
 * เพื่อให้คนเข้าใหม่โผล่เองและคนที่ลาออกแล้วหายไปเอง ไม่ต้องตามแก้โค้ด
 *
 * รูปแบบชื่อที่แสดง = sNickName + " " + คำแรกของ sName เช่น "Aon Pornnapa"
 * ค่านี้คือค่าที่ถูกส่งไป Monday/Salesforce มาแต่เดิม จึงต้องคงรูปแบบไว้
 *
 * ทุกคนจะมี monday_user_id ติดมาด้วยเพื่อส่งต่อให้ webhook ของ Make.com
 * ไปสร้าง item ใน monday.com — ถ้าในฐานข้อมูลยังไม่มีจะได้ค่าว่าง
 */

/**
 * พนักงานนอกทีมขายที่ต้องการให้อยู่ใน dropdown ด้วย (ระบุด้วย sID)
 * ใช้ sID แทนชื่อเพราะชื่อซ้ำกันได้และเปลี่ยนได้ แต่ sID ไม่เปลี่ยน
 * และยึดตาม sID ทำให้ยังอยู่ในรายการแม้ถูกย้ายทีม
 *
 * sID 31 = Nan (Chompunuch Julkraianisong) อยู่ teamID 8 แต่ต้องรับงานขายด้วย
 */
if (!defined('SALES_AGENT_EXTRA_SIDS')) {
    define('SALES_AGENT_EXTRA_SIDS', '31');
}

if (!function_exists('getSalesAgents')) {
    /**
     * @return array รายการ ['label' => 'Aon Pornnapa', 'monday_user_id' => '75101200']
     */
    function getSalesAgents($db)
    {
        $agents = [];

        if (!$db) {
            return $agents;
        }

        //ดึงทีมขาย (teamID 3) บวกกับคนนอกทีมที่ระบุไว้เป็นกรณีพิเศษ
        //ทั้งสองกลุ่มยังต้อง active และไม่ถูกลบเหมือนกัน
        $extraSids = array_filter(array_map('intval', explode(',', SALES_AGENT_EXTRA_SIDS)));
        $extraPlaceholders = $extraSids
            ? ' OR `sID` IN (' . implode(',', $extraSids) . ')'
            : '';

        $rows = $db->query(
            'SELECT `sNickName`, `sName`, `monday_user_id`
               FROM `staffs`
              WHERE (`teamID` = ?' . $extraPlaceholders . ')
                AND `sStatus` = ?
                AND `sDeleteAt` IS NULL
              ORDER BY `sNickName`',
            3, 1
        )->fetchAll();

        if (!is_array($rows)) {
            return $agents;
        }

        foreach ($rows as $row) {
            //ข้อมูลบางแถวมีช่องว่างหัวท้ายติดมา ต้อง trim ก่อนเสมอ
            $nickName = trim($row['sNickName'] ?? '');
            $fullName = trim($row['sName'] ?? '');

            if ($nickName === '') {
                continue;
            }

            //เอาเฉพาะคำแรกของชื่อจริง ตามรูปแบบเดิมของ dropdown
            $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
            $firstName = $nameParts[0] ?? '';

            $label = trim($nickName . ' ' . $firstName);

            $agents[] = [
                'label'          => $label,
                'monday_user_id' => trim((string) ($row['monday_user_id'] ?? '')),
            ];
        }

        return $agents;
    }
}

if (!function_exists('getSalesAgentMondayId')) {
    /**
     * หา monday_user_id จากชื่อที่เลือกใน dropdown
     * คืนค่าว่างเมื่อเลือก "Other", ไม่ได้เลือก, หรือคนนั้นยังไม่มี monday_user_id
     */
    function getSalesAgentMondayId($db, $label)
    {
        $label = trim((string) $label);

        if ($label === '' || $label === 'Other') {
            return '';
        }

        foreach (getSalesAgents($db) as $agent) {
            if (strcasecmp($agent['label'], $label) === 0) {
                return $agent['monday_user_id'];
            }
        }

        return '';
    }
}
