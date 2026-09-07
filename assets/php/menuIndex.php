<?php
/**
 * Search index for the sidebar menu.
 *
 * The visibility rules here are a mirror of the ones in sideBar.php - if a menu
 * item is wrapped in a condition there, the same condition must gate it here,
 * otherwise the quick search would offer people links they cannot open.
 * Whenever sideBar.php gains/loses an item or changes a permission check,
 * update this file in the same commit.
 *
 * Each entry: label (shown), group (parent menu), url, icon (bootstrap-icons
 * class or plain text), external (opens in a new tab), keywords (extra terms
 * that should match, incl. Thai).
 */

if (!function_exists('buildMenuIndex')) {
function buildMenuIndex()
{
    $userLevel = (int)($_SESSION['level'] ?? 99);
    $teamID    = $_SESSION['teamID'] ?? 0;
    $staffType = $_SESSION['staffType'] ?? 'fullTime';
    $myID      = $_SESSION['id'] ?? 0;

    $items = [];
    $add = function ($label, $group, $url, $icon, $external = false, $keywords = '') use (&$items) {
        $items[] = [
            'label'    => $label,
            'group'    => $group,
            'url'      => $url,
            'icon'     => $icon,
            'external' => (bool)$external,
            'keywords' => $keywords,
        ];
    };

    if ($staffType !== 'guest') {

        $add('Home', '', 'main.php?p=home', 'bi bi-house', false, 'หน้าแรก dashboard');
        $add('My Profile', '', 'main.php?p=myProfile', 'bi bi-person', false, 'โปรไฟล์ account me');

        // Website Management
        if (in_array($teamID, [1, 2, 8, 10, 11])) {
            $add('Website Lists', 'Website Management', 'https://report.localforyou.com/modules/websiteList/views/websiteList.php#', 'bi bi-list-check', true, 'เว็บไซต์ site');
        }
        $add('Website Template', 'Website Management', 'main.php?p=websiteTemplate', 'bi bi-browser-chrome', false, 'เทมเพลต theme');

        // System
        if ($teamID == 3) {
            $add('L4U Booking', 'System', 'modules/L4UBooking', 'bi bi-bookmarks', true, 'จอง booking');
        }

        // Form Management
        $add('Signup Form', 'Form Management', 'modules/signup/index.php', 'bi bi-file-earmark-person', true, 'สมัคร ฟอร์ม register');
        $add('Unsubscribe Form', 'Form Management', 'modules/unsub2/views/index.php?id=123', 'bi bi-file-earmark-excel', true, 'ยกเลิก cancel unsub');
        $add('Template Submissions', 'Form Management', 'modules/templates/views/main.php', 'bi bi-file-earmark-break', true, 'เทมเพลต');
        $add('AI Management', 'Form Management', 'main.php?p=aiManagement', 'bi bi-robot', false, 'ai araya');
        if ($userLevel < 4 || $myID == 83) {
            $add('Customer Thailand', 'Form Management', 'main.php?p=invoiceThailand', 'bi bi-receipt', false, 'invoice ไทย บิล billing th ลูกค้า');
            $add('Data Customer', 'Form Management', 'main.php?p=dataCustomer', 'bi bi-person-vcard', false, 'ข้อมูลลูกค้า customer data');
        }
        $add('ส่งหลักฐานชำระเงิน', 'Form Management', 'main.php?p=slipSubmission', 'bi bi-upload', false, 'slip submission payment สลิป โอนเงิน');

        // Rewards & Coins
        if ($userLevel <= 3) {
            $add('Reward Coin', 'Rewards & Coins', 'main.php?p=rewardCoin', 'bi bi-gem', false, 'รางวัล เหรียญ');
        }
        $add('L4U Coin', 'Rewards & Coins', 'main.php?p=coin', 'bi bi-coin', false, 'เหรียญ coin');
        $add('L4U Coin Request', 'Rewards & Coins', 'https://forms.monday.com/forms/da9ca9feccd4e43b4d264a3b45ba38ed?r=apse2', 'bi bi-file-plus', true, 'ขอเหรียญ request');

        // Reports & Analytics
        if ($userLevel <= 3) {
            $add('Subscription Report', 'Reports & Analytics', 'main.php?p=reportWeekly', 'bi bi-clipboard-data', false, 'รายงาน weekly monthly yearly');
            $add('Revenue', 'Reports & Analytics', 'main.php?p=reportRevenue', 'bi bi-cash-stack', false, 'รายได้ ยอดขาย sales');
            $add('Google Analytics', 'Reports & Analytics', 'main.php?p=reportGA', 'bi bi-google', false, 'ga analytics');
            $add('Deliveries Report', 'Reports & Analytics', 'main.php?p=reportDeliveries', 'bi bi-truck', false, 'ส่งของ delivery');
            $add('Dreamscape Report', 'Reports & Analytics', 'main.php?p=reportDreamscape', 'bi bi-cloud', false, 'hosting domain');
            $add('Life Span Report', 'Reports & Analytics', 'main.php?p=reportLifeSpan', 'bi bi-hourglass-split', false, 'lifespan อายุ');
        }
        $add('Monday Report', 'Reports & Analytics', 'modules/mondayReport/views/index.php?id=' . $myID, 'bi bi-kanban', true, 'monday.com task');

        // Logs
        $add('Feedback Monday', 'Logs', 'main.php?p=feedbackMonday', 'bi bi-chat-left-text', false, 'ฟีดแบค comment');
        $add('Printers Log', 'Logs', 'main.php?p=printersLog', 'bi bi-printer', false, 'เครื่องพิมพ์ printer');
        $add('SignUp Logs (Staff)', 'Logs', 'main.php?p=signupLogs', 'bi bi-person-plus', false, 'สมัคร log');
        $add('Upgrade to New System', 'Logs', 'main.php?p=formASAPLogs', 'bi bi-calendar-check', false, 'asap booking changes อัปเกรด');
        $add('AI Araya Logs', 'Logs', 'https://report.localforyou.com/modules/aiAraya/views/entries.php', 'AI', true, 'ai araya');
        $add('Change Logs', 'Logs', 'modules/changeLog/changelog.php', 'bi bi-clock-history', true, 'changelog version อัปเดต');

        // User Management
        if ($userLevel <= 3) {
            $add('Staffs', 'User Management', 'main.php?p=setStaff', 'bi bi-person-gear', false, 'พนักงาน staff user');
        }
        $add('Check-in Logs', 'User Management', 'main.php?p=checkinLogs', 'bi bi-clock-history', false, 'เช็คอิน attendance');

        // System Settings
        $add('Password', 'System Settings', 'main.php?p=l4uPassword', 'bi bi-key', false, 'รหัสผ่าน pass credential login');

        // Tools
        $add('All Tools', 'Tools', 'main.php?p=userTools', 'bi bi-grid-3x3-gap', false, 'เครื่องมือ tool');

        if (in_array($staffType, ['partTime', 'intern']) || $userLevel <= 2) {
            $add('Check-in', '', 'https://report.localforyou.com/modules/checkin/views/main.php', 'bi bi-clock-history', true, 'เช็คอิน เข้างาน');
        }

        // Navbar-only entry, same rule as navBar.php
        if ($teamID == 5) {
            $add('Tools (IT)', '', 'main.php?p=tools', 'bi bi-tools', false, 'it เครื่องมือ admin');
        }

    } else {
        // Guest sees only this one
        $add('Feedback Monday', 'Logs', 'main.php?p=feedbackMonday', 'bi bi-chat-left-text', false, 'ฟีดแบค comment');
    }

    return $items;
}
}
