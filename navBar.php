<?php
$userLevel = $_SESSION['level'];
$myID = $_SESSION['id'];
$teamID = $_SESSION['teamID'];
$navNickName = $_SESSION['nickName'] ?? '';
$navFirstName = explode(' ', $_SESSION['name'])[0];
$navDisplayName = $navNickName ? $navNickName . ' ' . $navFirstName : $navFirstName;
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav" id="navLeftLinks">
        <li class="nav-item pl-5">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>

        <!-- Quick menu search: filters the same menu list the sidebar builds,
             so it can never surface a page this user is not allowed to see. -->
        <?php
        include_once __DIR__ . '/assets/php/menuIndex.php';
        $quickMenuItems = function_exists('buildMenuIndex') ? buildMenuIndex() : [];
        ?>
        <li class="nav-item d-none d-md-block" id="menuQuickSearchWrap">
            <div class="mqs-box">
                <i class="bi bi-search mqs-icon"></i>
                <input type="text" id="menuQuickSearch" class="mqs-input" autocomplete="off"
                       placeholder="Search menu..." aria-label="Search menu">
                <kbd class="mqs-kbd">/</kbd>
            </div>
            <div id="menuQuickResults" class="mqs-results"></div>
        </li>

            <!-- <li class="nav-item d-none d-sm-inline-block">
                <a href="https://forms.monday.com/forms/da9ca9feccd4e43b4d264a3b45ba38ed?r=apse2" target="_blank" class="nav-link">Coin Request <i class="bi bi-box-arrow-up-right"></i></a>
            </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="https://app.respond.io/user/login" target="_blank" class="nav-link">Respond.io <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="https://app.trainual.com/local-for-you/users/sign_in" target="_blank" class="nav-link">Trainual <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->
        <!-- <li class="nav-item d-none d-sm-inline-block">
            <a href="modules/changeLog/changelog.php" target="_blank" class="nav-link">Change Log <i class="bi bi-box-arrow-up-right"></i></a>
        </li> -->


    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <!-- Navbar Search -->
        <?php if ($teamID == 5){ ?>
        <li class="nav-item">
            <a class="nav-link" href="main.php?p=tools" role="button">
                <i class="bi bi-tools"></i>
            </a>
        </li>
        <?php } ?>


        <!-- Activity Dropdown -->
        <?php
        global $db;
        // Get all recent activities for display (max 5)
        $navActivities = $db->query('SELECT CL.`id`, CT.`name` AS "coin", CL.`amount`, ST.`sNickName` AS "nick", ST.`sPic` AS "pic", CL.`reason`, CL.`giveOn`, CA.`aName` 
                FROM `CoinLogs` CL, `staffs` ST, `CoinType` CT, `CoinActivities` CA 
                WHERE CL.`ownerID`= ? AND CL.`status` = ? AND CL.`giveBy` = ST.`sID` AND CL.`coinType` = `CT`.`id` AND CL.`activityID` = CA.`aID`
                ORDER BY CL.`giveOn` DESC LIMIT 5;', $myID, 1)->fetchAll();
        
        // Count only unread activities for badge (using is_read column)
        $unreadCount = $db->query('SELECT COUNT(*) as count FROM CoinLogs WHERE ownerID = ? AND is_read = 0', $myID)->fetchAll();
        $navActivityCount = $unreadCount[0]['count'];
        
        // Team notifications (e.g. signups that never got a Stripe result).
        // Kept in their own table so Coin activity stays untouched; read state
        // is per person via notification_reads.
        include_once __DIR__ . '/assets/php/notify.php';
        $navNotis = [];
        $notiUnread = 0;
        $myTeamID = $_SESSION['teamID'] ?? 0;
        // The notifications tables may not exist yet on an environment that has
        // not been migrated. The bell is on every page, so a missing table must
        // never take the whole site down - check for it first, and note that
        // db::error() calls exit(), so a try/catch alone would not be enough.
        $hasNotiTable = false;
        try {
            $t = $db->query("SHOW TABLES LIKE 'notifications'")->fetchAll();
            $hasNotiTable = !empty($t);
        } catch (\Throwable $e) {
            $hasNotiTable = false;
        }

        try {
        if (!empty($myTeamID) && $hasNotiTable) {
            $navNotis = $db->query(
                'SELECT n.id, n.title, n.message, n.link, n.createAt,
                        (nr.staffID IS NOT NULL) AS isRead
                 FROM notifications n
                 LEFT JOIN notification_reads nr ON nr.notificationID = n.id AND nr.staffID = ?
                 WHERE n.teamID = ?
                 ORDER BY n.createAt DESC LIMIT 5', $myID, $myTeamID
            )->fetchAll();

            $notiUnreadRow = $db->query(
                'SELECT COUNT(*) AS count FROM notifications n
                 LEFT JOIN notification_reads nr ON nr.notificationID = n.id AND nr.staffID = ?
                 WHERE n.teamID = ? AND nr.staffID IS NULL', $myID, $myTeamID
            )->fetchAll();
            $notiUnread = (int)($notiUnreadRow[0]['count'] ?? 0);
        }
        } catch (\Throwable $e) {
            error_log('navBar notifications unavailable: ' . $e->getMessage());
            $navNotis = [];
            $notiUnread = 0;
        }

        $navActivityCount += $notiUnread;

        // Show badge if there are any unread activities
        $showBadge = ($navActivityCount > 0);
        $latestActivityId = count($navActivities) > 0 ? $navActivities[0]['id'] : 0;
        ?>
        <li class="nav-item dropdown" id="activityDropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" style="position:relative;" onclick="markActivityRead()">
                <i class="bi bi-bell" style="font-size:1.1rem;"></i>
                <?php if($showBadge){ ?>
                    <span id="activityBadge" class="badge badge-danger navbar-badge" style="font-size:0.6rem;padding:2px 5px;border-radius:10px;"><?php echo $navActivityCount; ?></span>
                <?php } ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="min-width:340px;max-width:380px;border:none;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:0;overflow:hidden;">
                <div style="padding:0.85rem 1.25rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-weight:700;font-size:0.9rem;color:#0f172a;">Activity</span>
                    <span style="font-size:0.7rem;color:#94a3b8;background:#f1f5f9;padding:0.15rem 0.5rem;border-radius:8px;"><?php echo $navActivityCount; ?> unread</span>
                </div>
                <div style="max-height:320px;overflow-y:auto;">
                    <?php foreach($navNotis as $noti){ ?>
                        <a href="<?php echo htmlspecialchars($noti['link'] ?: '#'); ?>" style="display:flex;align-items:flex-start;gap:0.5rem;padding:0.75rem 1.25rem;border-bottom:1px solid #f8fafc;text-decoration:none;background:<?php echo $noti['isRead'] ? 'transparent' : '#fff7ed'; ?>;">
                            <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b;font-size:1rem;flex-shrink:0;margin-top:2px;"></i>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:0.8rem;color:#0f172a;"><?php echo htmlspecialchars($noti['title']); ?></div>
                                <div style="font-size:0.72rem;color:#64748b;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                    <?php echo htmlspecialchars($noti['message']); ?>
                                </div>
                                <div style="font-size:0.68rem;color:#94a3b8;margin-top:2px;">
                                    <?php echo date("d M H:i", strtotime($noti['createAt'])); ?>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                    <?php if(count($navActivities) >= 1){ foreach($navActivities as $act){ ?>
                        <div class="d-flex align-items-start gap-2" style="padding:0.75rem 1.25rem;border-bottom:1px solid #f8fafc;transition:background 0.2s;cursor:pointer;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <img src="dist/img/crews/<?php echo $act['pic']; ?>" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;flex-shrink:0;margin-top:2px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:0.8rem;color:#0f172a;"><?php echo htmlspecialchars($act['aName']); ?></div>
                                <div style="font-size:0.72rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                    <?php echo htmlspecialchars($act['reason']); ?>
                                </div>
                                <div style="font-size:0.68rem;color:#94a3b8;margin-top:2px;">
                                    <i class="fas fa-coins" style="color:#fbbf24;font-size:0.6rem;"></i>
                                    <?php echo $act['coin']; ?> · by <?php echo htmlspecialchars($act['nick']); ?> · <?php echo date("d M H:i", strtotime($act['giveOn'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php } } elseif(count($navNotis) === 0){ ?>
                        <div style="padding:2rem;text-align:center;color:#94a3b8;">
                            <i class="bi bi-bell-slash" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>
                            <span style="font-size:0.8rem;">No activity yet</span>
                        </div>
                    <?php } ?>
                </div>
                <a href="main.php?p=myProfile" style="display:block;padding:0.7rem;text-align:center;font-size:0.8rem;font-weight:600;color:#0619B6;border-top:1px solid #f1f5f9;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    View All Activity
                </a>
            </div>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li> -->
        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 px-3" data-toggle="dropdown" href="#" style="cursor:pointer;">
                <img src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" 
                     alt="User" 
                     class="rounded-circle" 
                     style="width:32px;height:32px;object-fit:cover;border:2px solid #e2e8f0;">
                <span class="d-none d-md-inline-block" style="font-weight:600;color:#0f172a;font-size:0.85rem;">
                    <?php echo $navDisplayName; ?>
                </span>
                <i class="fas fa-chevron-down" style="font-size:0.6rem;color:#94a3b8;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="min-width:260px;border:none;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);padding:0;overflow:hidden;">
                <!-- User Info -->
                <div style="padding:1rem 1.25rem;background:linear-gradient(135deg,#0619B6,#0361D1);color:#fff;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="dist/img/crews/<?php echo $_SESSION['userPic']; ?>" 
                             alt="User" class="rounded-circle" 
                             style="width:40px;height:40px;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;"><?php echo $_SESSION['name']; ?></div>
                            <div style="font-size:0.75rem;opacity:0.8;"><?php echo $_SESSION['levelName']; ?></div>
                        </div>
                    </div>
                    <!-- Coins -->
                    <div class="d-flex gap-2 mt-2">
                        <div style="background:rgba(255,255,255,0.15);border-radius:8px;padding:0.3rem 0.6rem;font-size:0.75rem;display:flex;align-items:center;gap:0.3rem;">
                            <i class="fas fa-coins" style="color:#fbbf24;"></i>
                            L4U: <b><?php echo number_format($_SESSION['L4UCoin'],2); ?></b>
                        </div>
                        <div style="background:rgba(255,255,255,0.15);border-radius:8px;padding:0.3rem 0.6rem;font-size:0.75rem;display:flex;align-items:center;gap:0.3rem;">
                            <i class="fas fa-coins" style="color:#fbbf24;"></i>
                            GM: <b><?php echo number_format($_SESSION['CEOCoin'],2); ?></b>
                        </div>
                    </div>
                </div>
                <!-- Menu Items -->
                <div style="padding:0.5rem 0;">
                    <a href="main.php?p=myProfile" class="dropdown-item d-flex align-items-center gap-2" style="padding:0.6rem 1.25rem;font-size:0.85rem;">
                        <i class="bi bi-person" style="font-size:1rem;color:#64748b;"></i>
                        My Profile
                    </a>
                    <div class="dropdown-divider" style="margin:0.25rem 0;"></div>
                    <a href="chkLogin.php?act=logout" class="dropdown-item d-flex align-items-center gap-2" style="padding:0.6rem 1.25rem;font-size:0.85rem;color:#ef4444;">
                        <i class="bi bi-box-arrow-right" style="font-size:1rem;"></i>
                        Sign Out
                    </a>
                </div>
            </div>
        </li>
    </ul>
</nav>

<!-- Quick menu search styles + behaviour -->
<style>
    /* The left link list takes the free width so the search box can stretch to
       fill whatever room is left between the hamburger and the right icons. */
    /* The left link list takes the free width so the search box can sit centred
       in it, at 62% of that space. */
    #navLeftLinks { flex: 1 1 auto; min-width: 0; }
    #menuQuickSearchWrap { position: relative; flex: 0 1 62%; min-width: 0; margin: 0 auto; }
    .mqs-box {
        display: flex; align-items: center; gap: .4rem;
        background: #e2e8f0; border: 1px solid #cbd5e1; border-radius: 8px;
        padding: .25rem .6rem;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .mqs-box:hover { background: #dbe2ea; border-color: #b6c2d1; }
    .mqs-box:focus-within {
        background: #fff; border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }
    .mqs-icon { color: #64748b; font-size: .85rem; }
    .mqs-input {
        border: 0; outline: 0; background: transparent;
        font-size: .82rem; color: #0f172a; flex: 1; min-width: 0; padding: .15rem 0;
    }
    .mqs-input::placeholder { color: #64748b; }
    .mqs-kbd {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 4px;
        font-size: .65rem; color: #94a3b8; padding: 0 .3rem; line-height: 1.4;
    }
    .mqs-box:focus-within .mqs-kbd { display: none; }
    .mqs-results {
        display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
        min-width: 320px; max-height: 360px; overflow-y: auto;
        background: #fff; border-radius: 12px; z-index: 1050;
        box-shadow: 0 8px 24px rgba(0,0,0,.14); padding: .35rem 0;
    }
    .mqs-results.show { display: block; }
    .mqs-item {
        display: flex; align-items: center; gap: .6rem;
        padding: .5rem .9rem .5rem 1.4rem; text-decoration: none; color: #0f172a; font-size: .82rem;
    }
    .mqs-item:hover, .mqs-item.active { background: #f1f5f9; text-decoration: none; color: #0f172a; }
    .mqs-item .mqs-ic {
        width: 1.1rem; text-align: center; color: #64748b; font-size: .95rem;
        font-weight: 700; flex: 0 0 auto;
    }
    .mqs-item .mqs-label { font-weight: 500; }
    .mqs-head {
        padding: .5rem .9rem .25rem; color: #94a3b8;
        font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
    }
    .mqs-head:not(:first-child) { border-top: 1px solid #f1f5f9; margin-top: .25rem; }
    .mqs-item mark { background: #fef08a; padding: 0; border-radius: 2px; }
    .mqs-empty { padding: .75rem .9rem; color: #94a3b8; font-size: .8rem; }
</style>
<script>
(function () {
    var MENU_ITEMS = <?php echo json_encode(array_values($quickMenuItems), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    var input   = document.getElementById('menuQuickSearch');
    var results = document.getElementById('menuQuickResults');
    if (!input || !results) return;

    var current = [];
    var cursor  = -1;

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function highlight(text, q) {
        var i = text.toLowerCase().indexOf(q);
        if (q === '' || i < 0) return esc(text);
        return esc(text.slice(0, i)) + '<mark>' + esc(text.slice(i, i + q.length)) + '</mark>' + esc(text.slice(i + q.length));
    }

    // Rank: label prefix > label contains > group contains > keyword contains.
    function search(q) {
        q = q.trim().toLowerCase();
        if (!q) return [];
        var out = [];
        MENU_ITEMS.forEach(function (it) {
            var label = (it.label || '').toLowerCase();
            var group = (it.group || '').toLowerCase();
            var kw    = (it.keywords || '').toLowerCase();
            var score = -1;
            if (label.indexOf(q) === 0) score = 0;
            else if (label.indexOf(q) > 0) score = 1;
            else if (group.indexOf(q) >= 0) score = 2;
            else if (kw.indexOf(q) >= 0) score = 3;
            if (score >= 0) out.push({ item: it, score: score });
        });
        out.sort(function (a, b) { return a.score - b.score; });
        out = out.slice(0, 10);

        // Cluster by group, keeping groups in the order their best match
        // appeared, so each group heading is rendered exactly once.
        var order = [], buckets = {};
        out.forEach(function (r) {
            var g = r.item.group || '';
            if (!buckets[g]) { buckets[g] = []; order.push(g); }
            buckets[g].push(r.item);
        });
        var grouped = [];
        order.forEach(function (g) { grouped = grouped.concat(buckets[g]); });
        return grouped;
    }

    function render(q) {
        current = search(q);
        cursor = current.length ? 0 : -1;
        if (!q.trim()) { results.classList.remove('show'); results.innerHTML = ''; return; }
        if (!current.length) {
            results.innerHTML = '<div class="mqs-empty">No menu found for "' + esc(q) + '"</div>';
            results.classList.add('show');
            return;
        }
        var ql = q.trim().toLowerCase();
        var html = '';
        var lastGroup = null;
        current.forEach(function (it, i) {
            // The group name is a heading printed once per run of items that
            // share it, not repeated on every row.
            var g = it.group || '';
            if (g !== lastGroup) {
                if (g !== '') html += '<div class="mqs-head">' + esc(g) + '</div>';
                lastGroup = g;
            }
            var icon = /^bi /.test(it.icon)
                ? '<i class="mqs-ic ' + esc(it.icon) + '"></i>'
                : '<span class="mqs-ic">' + esc(it.icon) + '</span>';
            var ext = it.external ? ' <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;color:#94a3b8;"></i>' : '';
            html += '<a class="mqs-item' + (i === 0 ? ' active' : '') + '" data-i="' + i + '" href="' + esc(it.url) + '"' +
                    (it.external ? ' target="_blank" rel="noopener"' : '') + '>' +
                    icon +
                    '<span class="mqs-label">' + highlight(it.label, ql) + ext + '</span>' +
                    '</a>';
        });
        results.innerHTML = html;
        results.classList.add('show');
    }

    function move(delta) {
        if (!current.length) return;
        var nodes = results.querySelectorAll('.mqs-item');
        if (cursor >= 0 && nodes[cursor]) nodes[cursor].classList.remove('active');
        cursor = (cursor + delta + current.length) % current.length;
        nodes[cursor].classList.add('active');
        nodes[cursor].scrollIntoView({ block: 'nearest' });
    }

    function go() {
        if (cursor < 0 || !current[cursor]) return;
        var it = current[cursor];
        if (it.external) window.open(it.url, '_blank', 'noopener');
        else window.location.href = it.url;
    }

    input.addEventListener('input', function () { render(this.value); });
    input.addEventListener('focus', function () { if (this.value.trim()) render(this.value); });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter') { e.preventDefault(); go(); }
        else if (e.key === 'Escape') { this.value = ''; render(''); this.blur(); }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#menuQuickSearchWrap')) results.classList.remove('show');
    });

    // "/" focuses the box, as long as the user is not already typing somewhere.
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
        var t = e.target;
        if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.tagName === 'SELECT' || t.isContentEditable)) return;
        e.preventDefault();
        input.focus();
        input.select();
    });
})();
</script>
