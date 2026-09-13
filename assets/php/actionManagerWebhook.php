<?php
global $db;
session_start();
include '../../assets/db/db.php';
include '../../assets/db/initDB.php';

if (empty($_SESSION['id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$act    = !empty($_POST['act']) ? $_POST['act'] : '';
$params = [];

// Guard: production may not have run assets/sql/monitor_templates.sql yet.
if (!$db->query("SHOW TABLES LIKE 'monitor_templates'")->fetchArray()) {
    echo json_encode(['status' => 'no_table']);
    exit;
}

if ($act === 'getList') {
    $params['data']   = $db->query("SELECT id, tpl_key, title, body, mention_all, is_active, update_at FROM monitor_templates ORDER BY id ASC")->fetchAll();
    $params['status'] = 'ok';

} elseif ($act === 'save') {
    $id      = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
    $title   = isset($_POST['title']) ? trim($_POST['title']) : '';
    $body    = isset($_POST['body'])  ? trim($_POST['body'])  : '';
    $mention = !empty($_POST['mention_all']) ? 1 : 0;
    $active  = !empty($_POST['is_active'])   ? 1 : 0;

    if ($id <= 0 || $title === '' || $body === '') {
        $params['status'] = 'invalid';
    } else {
        $db->query(
            "UPDATE monitor_templates SET title=?, body=?, mention_all=?, is_active=?, update_at=NOW() WHERE id=?",
            $title, $body, $mention, $active, $id
        );
        $params['status'] = 'ok';
    }

// Send the rendered template to Google Chat with sample data, so wording can be
// checked in the real space before a site actually goes down.
} elseif ($act === 'test') {
    $id  = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
    $tpl = $db->query("SELECT * FROM monitor_templates WHERE id=?", $id)->fetchArray();

    if (!$tpl) {
        $params['status'] = 'not_found';
    } else {
        define('MONITOR_FUNCTIONS_ONLY', true);
        require_once __DIR__ . '/check_monitor.php';

        $vars = [
            '{name}' => 'ตัวอย่างเว็บไซต์ (TEST)', '{url}' => 'https://example.com',
            '{httpCode}' => '500', '{errorMsg}' => 'WordPress critical error (HTTP 200)',
            '{responseMs}' => '812', '{sslExpiry}' => date('Y-m-d', strtotime('+15 days')),
            '{sslDaysLeft}' => '15', '{time}' => date('Y-m-d H:i:s'),
        ];
        sendNotifications(
            ['notify_email' => '', 'notify_line' => '', 'notify_webhook' => ''],
            strtr($tpl['title'], $vars),
            strtr($tpl['body'], $vars),
            (int)$tpl['mention_all'] === 1
        );
        $params['status'] = CHAT_WEBHOOK ? 'ok' : 'no_webhook';
    }
}

echo json_encode($params);
