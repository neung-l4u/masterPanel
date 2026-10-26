<?php
/**
 * Dynamic QR Code Management — admin UI.
 *
 * Rendered inside the master panel layout (sidebar/navbar come from main.php),
 * which also establishes the session this page relies on.
 *
 * controller.php is deliberately NOT included here: it is a POST-only endpoint
 * that exits on its own, so the page only borrows the CSRF token helper.
 */

require_once __DIR__ . '/../../modules/QRCodeDynamicLink/src/listing.php';
require_once __DIR__ . '/../../modules/QRCodeDynamicLink/src/schedule.php';
require_once __DIR__ . '/../../modules/QRCodeDynamicLink/src/analytics.php';

$qrModuleUrl = 'modules/QRCodeDynamicLink';

// main.php has already started the session; mirror the controller's token.
if (empty($_SESSION['qr_csrf'])) {
    $_SESSION['qr_csrf'] = bin2hex(random_bytes(32));
}
$qrCsrf = $_SESSION['qr_csrf'];

$qrError = null;
$qrStats = ['total' => 0, 'active' => 0, 'paused' => 0, 'expired' => 0,
            'archived' => 0, 'draft' => 0, 'total_scans' => 0];
$qrResult = ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 0, 'page_size' => 25];
$qrGroups = [];
$qrTags = [];
$qrDetail = null;
$qrVersions = [];
$qrSchedules = [];
$qrScanStats = null;
$qrSeries = [];
$qrRecent = [];

try {
    $qdb = qrDbConnect();

    // Housekeeping on page load rather than by cron alone, so the counters a
    // human is looking at are not stale. The redirect path never relies on it.
    try {
        qrApplyDueSchedules($qdb);
        qrMarkExpired($qdb);
    } catch (Throwable $e) {
        // Housekeeping must never take the page down.
    }

    $qrStats = qrDashboardStats($qdb);
    $qrGroups = qrGetAllGroups($qdb);
    $qrTags = qrGetAllTags($qdb);

    $view = isset($_GET['view']) ? $_GET['view'] : 'list';
    $detailId = isset($_GET['qr']) ? (int)$_GET['qr'] : 0;

    if ($detailId > 0) {
        $qrDetail = qrGetByIdWithVersion($qdb, $detailId);
        if (!empty($qrDetail)) {
            $qrVersions   = qrGetVersions($qdb, $detailId, 100);
            $qrSchedules  = qrGetSchedules($qdb, $detailId);
            $qrScanStats  = qrGetStats($qdb, $detailId);
            $qrSeries     = qrGetDailySeries($qdb, $detailId, 30);
            $qrRecent     = qrGetRecentScans($qdb, $detailId, 15);
            $qrDetail['tags']   = array_column(qrGetTags($qdb, $detailId), 'name');
            $qrDetail['groups'] = qrGetGroups($qdb, $detailId);
        }
    } else {
        $qrResult = qrSearch($qdb, [
            'q'                => isset($_GET['q']) ? $_GET['q'] : '',
            'status'           => isset($_GET['status']) ? $_GET['status'] : '',
            'group_id'         => isset($_GET['group_id']) ? $_GET['group_id'] : '',
            'tag'              => isset($_GET['tag']) ? $_GET['tag'] : '',
            'sort'             => isset($_GET['sort']) ? $_GET['sort'] : 'created_at',
            'dir'              => isset($_GET['dir']) ? $_GET['dir'] : 'desc',
            'page'             => isset($_GET['page']) ? (int)$_GET['page'] : 1,
            'include_archived' => !empty($_GET['include_archived']),
        ]);
    }
} catch (Throwable $e) {
    $qrError = $e->getMessage();
}

/** Shorthand for escaping output. */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function qrStatusBadge($status) {
    $map = [
        'active'   => ['bg-success', 'Active'],
        'paused'   => ['bg-warning', 'Paused'],
        'expired'  => ['bg-danger',  'Expired'],
        'draft'    => ['bg-secondary', 'Draft'],
        'archived' => ['bg-dark',    'Archived'],
    ];
    $m = isset($map[$status]) ? $map[$status] : ['bg-secondary', $status];
    return '<span class="badge ' . $m[0] . '">' . h($m[1]) . '</span>';
}

function qrTimeAgo($utc) {
    if (empty($utc)) { return 'Never'; }
    $diff = time() - strtotime($utc . ' UTC');
    if ($diff < 60)    { return 'Just now'; }
    if ($diff < 3600)  { return floor($diff / 60) . ' min ago'; }
    if ($diff < 86400) { return floor($diff / 3600) . ' hr ago'; }
    if ($diff < 2592000) { return floor($diff / 86400) . ' days ago'; }
    return qrUtcToLocal($utc, 'Y-m-d');
}
?>

<div class="content-header">
     <div class="container-fluid">
          <div class="row mb-2">
               <div class="col-sm-6">
                    <h4 class="m-0"><i class="bi bi-qr-code-scan mr-2"></i>QR Dynamic Link</h4>
               </div>
               <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                         <li class="breadcrumb-item"><a href="main.php?p=home">Home</a></li>
                         <li class="breadcrumb-item"><a href="main.php?p=userTools">User Tools</a></li>
                         <li class="breadcrumb-item active">Dynamic QR</li>
                    </ol>
               </div>
          </div>
     </div>
</div>

<style>
/* Scoped to #qrApp so nothing here affects the rest of the panel. */
#qrApp .qr-stat {
     position: relative;
     overflow: hidden;
     padding: 1.25rem 1.5rem;
     border-radius: 16px;
     color: #fff;
     box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
     min-height: 104px;
}

#qrApp .qr-stat-value {
     font-size: 2rem;
     font-weight: 700;
     line-height: 1.1;
}

#qrApp .qr-stat-label {
     font-size: .875rem;
     opacity: .9;
     margin-top: .15rem;
}

#qrApp .qr-stat-icon {
     position: absolute;
     right: 1rem;
     bottom: .5rem;
     font-size: 3.25rem;
     opacity: .25;
     line-height: 1;
}

#qrApp .qr-stat-info {
     background: linear-gradient(135deg, #0ea5e9, #0284c7);
}

#qrApp .qr-stat-success {
     background: linear-gradient(135deg, #22c55e, #16a34a);
}

#qrApp .qr-stat-warning {
     background: linear-gradient(135deg, #f59e0b, #d97706);
}

#qrApp .qr-stat-primary {
     background: linear-gradient(135deg, #6366f1, #4f46e5);
}

/* master-panel.css pads every .card-body to 1.5rem, which is too much for a
   table that already has its own cell padding. */
#qrApp .qr-table-card .card-body {
     padding: 1rem 1.25rem !important;
}

#qrApp .table td,
#qrApp .table th {
     vertical-align: middle;
}

#qrApp .table thead th {
     border-top: 0;
     font-size: .8rem;
     text-transform: uppercase;
     letter-spacing: .03em;
     color: #64748b;
}

#qrApp .badge {
     font-weight: 500;
}

@media (max-width: 575.98px) {
     #qrApp .qr-stat {
          min-height: 88px;
          padding: 1rem;
     }

     #qrApp .qr-stat-value {
          font-size: 1.5rem;
     }

     #qrApp .qr-stat-icon {
          font-size: 2.25rem;
     }
}
</style>

<div class="content">
     <div class="container-fluid" id="qrApp" data-csrf="<?php echo h($qrCsrf); ?>"
          data-endpoint="<?php echo h($qrModuleUrl); ?>/src/controller.php">

          <?php
/*
 * base_url is baked into every generated symbol and cannot be corrected once a
 * code is printed, so both ways of getting it wrong are surfaced loudly:
 * a missing config file, and a config file still carrying a local address on a
 * server that is not local. The second case is the dangerous one, because
 * everything looks like it is working.
 */
$qrConfigFile = __DIR__ . '/../../modules/QRCodeDynamicLink/assets/config.php';
$qrBase = qrBaseUrl();
$qrBaseHost = (string)parse_url($qrBase, PHP_URL_HOST);
$qrServerHost = isset($_SERVER['HTTP_HOST'])
    ? strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']))
    : '';
$qrLocalHosts = ['localhost', '127.0.0.1', '::1'];
$qrBaseIsLocal   = in_array(strtolower($qrBaseHost), $qrLocalHosts, true);
$qrServerIsLocal = in_array($qrServerHost, $qrLocalHosts, true) || $qrServerHost === '';
?>

          <?php if (!is_file($qrConfigFile)): ?>
          <div class="alert alert-danger">
               <i class="bi bi-exclamation-octagon mr-1"></i>
               <strong>Base URL is not configured.</strong>
               Copy <code>modules/QRCodeDynamicLink/assets/config.example.php</code> to
               <code>config.php</code> and set <code>base_url</code> before printing any QR code.
               The detected fallback is <code><?php echo h($qrBase); ?></code>, which may be wrong
               and cannot be corrected once a code is printed.
          </div>
          <?php elseif ($qrBaseIsLocal && !$qrServerIsLocal): ?>
          <div class="alert alert-danger">
               <i class="bi bi-exclamation-octagon mr-1"></i>
               <strong>Base URL still points at a local address.</strong>
               Every QR code created now encodes
               <code><?php echo h($qrBase); ?>/qr/&hellip;</code>, which nobody outside this
               machine can open — and a printed code can never be corrected.
               <div class="small mt-2 mb-0">
                    Set <code>base_url</code> in
                    <code>modules/QRCodeDynamicLink/assets/config.php</code> to this
                    installation's public address, then delete any codes already created.
               </div>
          </div>
          <?php endif; ?>

          <?php if ($qrError): ?>
          <div class="alert alert-danger">
               <i class="bi bi-exclamation-triangle mr-1"></i>
               <strong>Error:</strong> <?php echo h($qrError); ?>
               <div class="small mt-2">
                    If the tables are missing, load
                    <code>modules/QRCodeDynamicLink/sql/schema.sql</code> first.
               </div>
          </div>
          <?php endif; ?>

          <?php if ($qrDetail): /* ------------------------------ DETAIL VIEW ----- */ ?>

          <?php
    $dynUrl = qrDynamicUrl($qrDetail['code']);
    $pending = array_values(array_filter($qrSchedules, function ($s) {
        return $s['status'] === 'pending';
    }));
    ?>

          <div class="mb-3">
               <a href="main.php?p=qrDynamicLink" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left mr-1"></i>Back to list
               </a>
          </div>

          <?php if (!empty($pending)): ?>
          <div class="alert alert-warning">
               <strong><i class="bi bi-clock-history mr-1"></i>
                    <?php echo count($pending); ?> scheduled change<?php echo count($pending) > 1 ? 's' : ''; ?> pending
               </strong>
               <ul class="mb-0 mt-2 pl-3">
                    <?php foreach ($pending as $p): ?>
                    <li>
                         <?php echo h(qrUtcToLocal($p['scheduled_at'], 'd M Y H:i')); ?>
                         (Bangkok) &rarr; <code><?php echo h($p['destination_url']); ?></code>
                         <button class="btn btn-xs btn-link text-danger qr-cancel-schedule"
                              data-id="<?php echo (int)$p['id']; ?>">Cancel</button>
                    </li>
                    <?php endforeach; ?>
               </ul>
               <div class="small mt-2 mb-0">
                    A scheduled change is permanent and will overwrite the current destination when it fires.
               </div>
          </div>
          <?php endif; ?>

          <div class="row">
               <div class="col-lg-8">
                    <div class="card">
                         <div class="card-header">
                              <h3 class="card-title"><?php echo h($qrDetail['name']); ?></h3>
                              <div class="card-tools"><?php echo qrStatusBadge($qrDetail['status']); ?></div>
                         </div>
                         <div class="card-body">
                              <table class="table table-sm mb-0">
                                   <tr>
                                        <th style="width:170px">QR ID</th>
                                        <td><?php echo h(qrDisplayId($qrDetail['id'])); ?></td>
                                   </tr>
                                   <tr>
                                        <th>Code <small class="text-muted">(permanent)</small></th>
                                        <td><code class="h6"><?php echo h($qrDetail['code']); ?></code></td>
                                   </tr>
                                   <tr>
                                        <th>Dynamic URL</th>
                                        <td>
                                             <code><?php echo h($dynUrl); ?></code>
                                             <button class="btn btn-xs btn-outline-secondary ml-2 qr-copy"
                                                  data-copy="<?php echo h($dynUrl); ?>">Copy</button>
                                        </td>
                                   </tr>
                                   <tr>
                                        <th>Current destination</th>
                                        <td>
                                             <a href="<?php echo h($qrDetail['destination_url']); ?>" target="_blank"
                                                  rel="noopener noreferrer">
                                                  <?php echo h($qrDetail['destination_url']); ?>
                                             </a>
                                             <span
                                                  class="badge bg-info ml-2">v<?php echo (int)$qrDetail['version_number']; ?></span>
                                        </td>
                                   </tr>
                                   <tr>
                                        <th>Fallback URL</th>
                                        <td>
                                             <?php echo $qrDetail['fallback_url']
                                    ? h($qrDetail['fallback_url'])
                                    : '<span class="text-muted">Not set</span>'; ?>
                                        </td>
                                   </tr>
                                   <tr>
                                        <th>Expires</th>
                                        <td>
                                             <?php echo $qrDetail['expires_at']
                                    ? h(qrUtcToLocal($qrDetail['expires_at'], 'd M Y H:i')) . ' <small class="text-muted">(Bangkok)</small>'
                                    : '<span class="text-muted">Never</span>'; ?>
                                        </td>
                                   </tr>
                                   <tr>
                                        <th>Groups</th>
                                        <td>
                                             <?php if (empty($qrDetail['groups'])): ?>
                                             <span class="text-muted">None</span>
                                             <?php else: foreach ($qrDetail['groups'] as $g): ?>
                                             <span class="badge bg-primary mr-1"><?php echo h($g['name']); ?></span>
                                             <?php endforeach; endif; ?>
                                        </td>
                                   </tr>
                                   <tr>
                                        <th>Tags</th>
                                        <td>
                                             <?php if (empty($qrDetail['tags'])): ?>
                                             <span class="text-muted">None</span>
                                             <?php else: foreach ($qrDetail['tags'] as $t): ?>
                                             <span class="badge bg-secondary mr-1">#<?php echo h($t); ?></span>
                                             <?php endforeach; endif; ?>
                                        </td>
                                   </tr>
                              </table>
                         </div>
                         <div class="card-footer">
                              <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#qrEditModal">
                                   <i class="bi bi-pencil mr-1"></i>Edit destination
                              </button>
                              <button class="btn btn-outline-secondary btn-sm" data-toggle="modal"
                                   data-target="#qrScheduleModal">
                                   <i class="bi bi-clock mr-1"></i>Schedule change
                              </button>
                              <?php if ($qrDetail['status'] === 'active'): ?>
                              <button class="btn btn-outline-warning btn-sm qr-set-status" data-status="paused">
                                   <i class="bi bi-pause mr-1"></i>Pause
                              </button>
                              <?php else: ?>
                              <button class="btn btn-outline-success btn-sm qr-set-status" data-status="active">
                                   <i class="bi bi-play mr-1"></i>Activate
                              </button>
                              <?php endif; ?>
                              <button class="btn btn-outline-dark btn-sm qr-set-status" data-status="archived"
                                   data-confirm="Archive this QR Code? Scans will stop resolving.">
                                   <i class="bi bi-archive mr-1"></i>Archive
                              </button>
                         </div>
                    </div>

                    <div class="card">
                         <div class="card-header">
                              <h3 class="card-title">Version history</h3>
                         </div>
                         <div class="card-body p-0">
                              <table class="table table-sm table-striped mb-0">
                                   <thead>
                                        <tr>
                                             <th style="width:60px">Ver</th>
                                             <th>Destination</th>
                                             <th style="width:150px">Changed</th>
                                             <th style="width:110px">By</th>
                                             <th style="width:90px"></th>
                                        </tr>
                                   </thead>
                                   <tbody>
                                        <?php foreach ($qrVersions as $v): ?>
                                        <tr>
                                             <td>
                                                  v<?php echo (int)$v['version_number']; ?>
                                                  <?php if (!empty($v['is_current'])): ?>
                                                  <span class="badge bg-success">Active</span>
                                                  <?php endif; ?>
                                             </td>
                                             <td>
                                                  <div class="text-truncate" style="max-width:320px">
                                                       <?php echo h($v['destination_url']); ?>
                                                  </div>
                                                  <?php if (!empty($v['change_note'])): ?>
                                                  <small class="text-muted"><?php echo h($v['change_note']); ?></small>
                                                  <?php endif; ?>
                                                  <?php if ($v['source'] !== 'manual'): ?>
                                                  <span class="badge bg-info"><?php echo h($v['source']); ?></span>
                                                  <?php endif; ?>
                                             </td>
                                             <td><small><?php echo h(qrUtcToLocal($v['created_at'], 'd M Y H:i')); ?></small>
                                             </td>
                                             <td><small><?php echo h($v['created_by_name'] ?: '—'); ?></small></td>
                                             <td>
                                                  <?php if (empty($v['is_current'])): ?>
                                                  <button class="btn btn-xs btn-outline-primary qr-restore"
                                                       data-version="<?php echo (int)$v['id']; ?>"
                                                       data-num="<?php echo (int)$v['version_number']; ?>">
                                                       Restore
                                                  </button>
                                                  <?php endif; ?>
                                             </td>
                                        </tr>
                                        <?php endforeach; ?>
                                   </tbody>
                              </table>
                         </div>
                         <div class="card-footer text-muted small">
                              Restoring copies an old destination into a new version. History is never rewritten.
                         </div>
                    </div>
               </div>

               <div class="col-lg-4">
                    <div class="card">
                         <div class="card-header">
                              <h3 class="card-title">QR Code</h3>
                         </div>
                         <div class="card-body text-center">
                              <img src="<?php echo h($qrModuleUrl); ?>/public/download.php?code=<?php echo h($qrDetail['code']); ?>&format=png&size=8&inline=1"
                                   alt="QR code <?php echo h($qrDetail['code']); ?>" class="img-fluid"
                                   style="max-width:240px">
                              <div class="mt-3">
                                   <a class="btn btn-sm btn-outline-primary"
                                        href="<?php echo h($qrModuleUrl); ?>/public/download.php?code=<?php echo h($qrDetail['code']); ?>&format=png&size=20">PNG</a>
                                   <a class="btn btn-sm btn-outline-primary"
                                        href="<?php echo h($qrModuleUrl); ?>/public/download.php?code=<?php echo h($qrDetail['code']); ?>&format=svg">SVG</a>
                                   <a class="btn btn-sm btn-outline-primary"
                                        href="<?php echo h($qrModuleUrl); ?>/public/download.php?code=<?php echo h($qrDetail['code']); ?>&format=pdf">PDF</a>
                              </div>
                              <div class="small text-muted mt-2">
                                   This image never changes, whatever the destination becomes.
                              </div>
                         </div>
                    </div>

                    <div class="card">
                         <div class="card-header">
                              <h3 class="card-title">Analytics</h3>
                         </div>
                         <div class="card-body">
                              <div class="row text-center">
                                   <div class="col-6 mb-3">
                                        <div class="h4 mb-0"><?php echo number_format($qrScanStats['total']); ?></div>
                                        <small class="text-muted">Total scans</small>
                                   </div>
                                   <div class="col-6 mb-3">
                                        <div class="h4 mb-0"><?php echo number_format($qrScanStats['unique']); ?></div>
                                        <small class="text-muted">Unique</small>
                                   </div>
                                   <div class="col-6">
                                        <div class="h5 mb-0"><?php echo number_format($qrScanStats['today']); ?></div>
                                        <small class="text-muted">Today</small>
                                   </div>
                                   <div class="col-6">
                                        <div class="h5 mb-0"><?php echo number_format($qrScanStats['this_month']); ?>
                                        </div>
                                        <small class="text-muted">This month</small>
                                   </div>
                              </div>
                              <hr>
                              <div class="small text-muted">
                                   Last scan: <?php echo h(qrTimeAgo($qrScanStats['last_scan'])); ?>
                              </div>
                              <?php if (!empty($qrSeries)): ?>
                              <canvas id="qrChart" height="140" class="mt-3"
                                   data-series='<?php echo h(json_encode($qrSeries)); ?>'></canvas>
                              <?php endif; ?>
                         </div>
                    </div>

                    <?php if (!empty($qrRecent)): ?>
                    <div class="card">
                         <div class="card-header">
                              <h3 class="card-title">Recent scans</h3>
                         </div>
                         <div class="card-body p-0">
                              <table class="table table-sm mb-0">
                                   <tbody>
                                        <?php foreach ($qrRecent as $s): ?>
                                        <tr>
                                             <td><small><?php echo h(qrTimeAgo($s['created_at'])); ?></small></td>
                                             <td><small><?php echo h($s['device']); ?> /
                                                       <?php echo h($s['os']); ?></small></td>
                                             <td>
                                                  <?php if ($s['resolved_from'] === 'fallback'): ?>
                                                  <span class="badge bg-warning">fallback</span>
                                                  <?php endif; ?>
                                             </td>
                                        </tr>
                                        <?php endforeach; ?>
                                   </tbody>
                              </table>
                         </div>
                    </div>
                    <?php endif; ?>
               </div>
          </div>

          <!-- Edit destination -->
          <div class="modal fade" id="qrEditModal" tabindex="-1">
               <div class="modal-dialog">
                    <div class="modal-content">
                         <form class="qr-ajax" data-action="change_destination"
                              data-qr="<?php echo (int)$qrDetail['id']; ?>">
                              <div class="modal-header">
                                   <h5 class="modal-title">Change destination</h5>
                                   <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                   <div class="form-group">
                                        <label class="small text-muted">Current destination</label>
                                        <input type="text" class="form-control" readonly
                                             value="<?php echo h($qrDetail['destination_url']); ?>">
                                   </div>
                                   <div class="form-group">
                                        <label>New destination URL <span class="text-danger">*</span></label>
                                        <input type="url" name="destination_url" class="form-control"
                                             placeholder="https://example.com/new-page" required>
                                   </div>
                                   <div class="form-group mb-0">
                                        <label>Change note</label>
                                        <input type="text" name="change_note" class="form-control"
                                             placeholder="Why is this changing?">
                                   </div>
                                   <div class="alert alert-info mt-3 mb-0 small">
                                        The QR code and its printed image stay exactly the same.
                                        A new version is recorded automatically.
                                   </div>
                              </div>
                              <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                   <button type="submit" class="btn btn-primary">Update destination</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>

          <!-- Schedule a change -->
          <div class="modal fade" id="qrScheduleModal" tabindex="-1">
               <div class="modal-dialog">
                    <div class="modal-content">
                         <form class="qr-ajax" data-action="create_schedule"
                              data-qr="<?php echo (int)$qrDetail['id']; ?>">
                              <div class="modal-header">
                                   <h5 class="modal-title">Schedule a destination change</h5>
                                   <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                   <div class="form-group">
                                        <label>New destination URL <span class="text-danger">*</span></label>
                                        <input type="url" name="destination_url" class="form-control" required>
                                   </div>
                                   <div class="form-group">
                                        <label>When <span class="text-danger">*</span>
                                             <small class="text-muted">(Bangkok time)</small></label>
                                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                                   </div>
                                   <div class="form-group mb-0">
                                        <label>Change note</label>
                                        <input type="text" name="change_note" class="form-control">
                                   </div>
                                   <div class="alert alert-warning mt-3 mb-0 small">
                                        This is a permanent change, not a temporary one. At the scheduled
                                        time the destination changes and stays changed.
                                   </div>
                              </div>
                              <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                   <button type="submit" class="btn btn-primary">Schedule</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>

          <?php else: /* --------------------------------------- LIST VIEW ------- */ ?>

          <div class="row">
               <?php
        // These use the module's own .qr-stat card rather than AdminLTE's
        // .small-box: assets/css/master-panel.css forces .small-box to a white
        // background with !important, which leaves AdminLTE's white figures
        // invisible on it.
        $cards = [
            ['Total QR Codes', $qrStats['total'],       'qr-stat-info',    'bi-qr-code'],
            ['Active',         $qrStats['active'],      'qr-stat-success', 'bi-check-circle'],
            ['Paused',         $qrStats['paused'],      'qr-stat-warning', 'bi-pause-circle'],
            ['Total Scans',    $qrStats['total_scans'], 'qr-stat-primary', 'bi-graph-up'],
        ];
        foreach ($cards as $c): ?>
               <div class="col-lg-3 col-6 mb-3">
                    <div class="qr-stat <?php echo $c[2]; ?>">
                         <div class="qr-stat-value"><?php echo number_format($c[1]); ?></div>
                         <div class="qr-stat-label"><?php echo h($c[0]); ?></div>
                         <i class="bi <?php echo $c[3]; ?> qr-stat-icon"></i>
                    </div>
               </div>
               <?php endforeach; ?>
          </div>

          <div class="card qr-table-card">
               <div class="card-header">
                    <h3 class="card-title">QR Codes</h3>
                    <div class="card-tools">
                         <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#qrCreateModal">
                              <i class="bi bi-plus-lg mr-1"></i>Create QR Code
                         </button>
                    </div>
               </div>

               <div class="card-body">
                    <form method="get" class="form-row mb-3">
                         <input type="hidden" name="p" value="qrDynamicLink">
                         <div class="col-md-4 mb-2">
                              <input type="text" name="q" class="form-control form-control-sm"
                                   placeholder="Search code, name, URL, tag, group…"
                                   value="<?php echo h(isset($_GET['q']) ? $_GET['q'] : ''); ?>">
                         </div>
                         <div class="col-md-2 mb-2">
                              <select name="status" class="form-control form-control-sm">
                                   <option value="">All statuses</option>
                                   <?php foreach (qrValidStatuses() as $s): ?>
                                   <option value="<?php echo h($s); ?>"
                                        <?php echo (isset($_GET['status']) && $_GET['status'] === $s) ? 'selected' : ''; ?>>
                                        <?php echo h(ucfirst($s)); ?>
                                   </option>
                                   <?php endforeach; ?>
                              </select>
                         </div>
                         <div class="col-md-2 mb-2">
                              <select name="group_id" class="form-control form-control-sm">
                                   <option value="">All groups</option>
                                   <?php foreach ($qrGroups as $g): ?>
                                   <option value="<?php echo (int)$g['id']; ?>"
                                        <?php echo (isset($_GET['group_id']) && (int)$_GET['group_id'] === (int)$g['id']) ? 'selected' : ''; ?>>
                                        <?php echo h($g['name']); ?> (<?php echo (int)$g['qr_count']; ?>)
                                   </option>
                                   <?php endforeach; ?>
                              </select>
                         </div>
                         <div class="col-md-2 mb-2">
                              <select name="tag" class="form-control form-control-sm">
                                   <option value="">All tags</option>
                                   <?php foreach ($qrTags as $t): ?>
                                   <option value="<?php echo h($t['name']); ?>"
                                        <?php echo (isset($_GET['tag']) && $_GET['tag'] === $t['name']) ? 'selected' : ''; ?>>
                                        #<?php echo h($t['name']); ?> (<?php echo (int)$t['qr_count']; ?>)
                                   </option>
                                   <?php endforeach; ?>
                              </select>
                         </div>
                         <div class="col-md-2 mb-2">
                              <button class="btn btn-sm btn-outline-primary btn-block">
                                   <i class="bi bi-funnel mr-1"></i>Filter
                              </button>
                         </div>
                    </form>

                    <div class="mb-2 qr-bulk-bar" style="display:none">
                         <span class="mr-2"><strong class="qr-selected-count">0</strong> selected</span>
                         <button class="btn btn-xs btn-outline-success qr-bulk" data-bulk="activate">Activate</button>
                         <button class="btn btn-xs btn-outline-warning qr-bulk" data-bulk="pause">Pause</button>
                         <button class="btn btn-xs btn-outline-dark qr-bulk" data-bulk="archive">Archive</button>
                         <button class="btn btn-xs btn-outline-primary qr-bulk-tags">Add tags</button>
                         <span class="text-muted small ml-2">Max <?php echo QR_BULK_MAX; ?> per operation</span>
                    </div>

                    <div class="table-responsive">
                         <table class="table table-sm table-hover">
                              <thead>
                                   <tr>
                                        <th style="width:28px"><input type="checkbox" class="qr-check-all"></th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Destination</th>
                                        <th>Tags</th>
                                        <th style="width:80px">Status</th>
                                        <th style="width:50px">Ver</th>
                                        <th style="width:70px">Scans</th>
                                        <th style="width:110px">Updated</th>
                                        <th style="width:90px"></th>
                                   </tr>
                              </thead>
                              <tbody>
                                   <?php if (empty($qrResult['rows'])): ?>
                                   <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                             No QR codes found.
                                        </td>
                                   </tr>
                                   <?php else: foreach ($qrResult['rows'] as $r): ?>
                                   <tr>
                                        <td><input type="checkbox" class="qr-check"
                                                  value="<?php echo (int)$r['id']; ?>"></td>
                                        <td><code><?php echo h($r['code']); ?></code></td>
                                        <td>
                                             <a href="main.php?p=qrDynamicLink&qr=<?php echo (int)$r['id']; ?>">
                                                  <?php echo h($r['name']); ?>
                                             </a>
                                             <?php if ((int)$r['pending_schedules'] > 0): ?>
                                             <i class="bi bi-clock-history text-warning ml-1"
                                                  title="<?php echo (int)$r['pending_schedules']; ?> scheduled change(s) pending"></i>
                                             <?php endif; ?>
                                        </td>
                                        <td>
                                             <div class="text-truncate" style="max-width:220px">
                                                  <small><?php echo h($r['destination_url']); ?></small>
                                             </div>
                                        </td>
                                        <td>
                                             <?php foreach (array_slice($r['tags'], 0, 3) as $t): ?>
                                             <span class="badge bg-secondary">#<?php echo h($t); ?></span>
                                             <?php endforeach; ?>
                                        </td>
                                        <td><?php echo qrStatusBadge($r['status']); ?></td>
                                        <td><small>v<?php echo (int)$r['version_number']; ?></small></td>
                                        <td><?php echo number_format((int)$r['scan_total']); ?></td>
                                        <td><small><?php echo h(qrTimeAgo($r['updated_at'] ?: $r['created_at'])); ?></small>
                                        </td>
                                        <td>
                                             <a class="btn btn-xs btn-outline-secondary"
                                                  href="main.php?p=qrDynamicLink&qr=<?php echo (int)$r['id']; ?>">View</a>
                                        </td>
                                   </tr>
                                   <?php endforeach; endif; ?>
                              </tbody>
                         </table>
                    </div>

                    <?php if ($qrResult['pages'] > 1): ?>
                    <nav>
                         <ul class="pagination pagination-sm">
                              <?php
                    $qs = $_GET;
                    for ($i = 1; $i <= min($qrResult['pages'], 20); $i++):
                        $qs['page'] = $i;
                        $qs['p'] = 'qrDynamicLink';
                    ?>
                              <li class="page-item <?php echo $i === $qrResult['page'] ? 'active' : ''; ?>">
                                   <a class="page-link" href="main.php?<?php echo h(http_build_query($qs)); ?>">
                                        <?php echo $i; ?>
                                   </a>
                              </li>
                              <?php endfor; ?>
                         </ul>
                    </nav>
                    <?php endif; ?>

                    <div class="text-muted small">
                         Showing <?php echo count($qrResult['rows']); ?>
                         of <?php echo number_format($qrResult['total']); ?> QR codes
                    </div>
               </div>
          </div>

          <!-- Create -->
          <div class="modal fade" id="qrCreateModal" tabindex="-1">
               <div class="modal-dialog">
                    <div class="modal-content">
                         <form class="qr-ajax" data-action="create">
                              <div class="modal-header">
                                   <h5 class="modal-title">Create QR Code</h5>
                                   <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                   <div class="form-group">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required
                                             placeholder="e.g. Restaurant A Menu">
                                   </div>
                                   <div class="form-group">
                                        <label>Destination URL <span class="text-danger">*</span></label>
                                        <input type="url" name="destination_url" class="form-control" required
                                             placeholder="https://example.com/menu">
                                   </div>
                                   <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" class="form-control" rows="2"></textarea>
                                   </div>
                                   <div class="form-row">
                                        <div class="form-group col-6">
                                             <label>Status</label>
                                             <select name="status" class="form-control">
                                                  <option value="active">Active</option>
                                                  <option value="draft">Draft</option>
                                             </select>
                                        </div>
                                        <div class="form-group col-6">
                                             <label>Group</label>
                                             <select name="groups" class="form-control">
                                                  <option value="">None</option>
                                                  <?php foreach ($qrGroups as $g): ?>
                                                  <option value="<?php echo (int)$g['id']; ?>">
                                                       <?php echo h($g['name']); ?></option>
                                                  <?php endforeach; ?>
                                             </select>
                                        </div>
                                   </div>
                                   <div class="form-group">
                                        <label>Tags <small class="text-muted">(comma separated)</small></label>
                                        <input type="text" name="tags" class="form-control"
                                             placeholder="menu, bangkok, 2026">
                                   </div>
                                   <div class="form-group mb-0">
                                        <label>Fallback URL <small class="text-muted">(optional)</small></label>
                                        <input type="url" name="fallback_url" class="form-control"
                                             placeholder="Used when paused or expired">
                                   </div>
                              </div>
                              <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                   <button type="submit" class="btn btn-primary">Create</button>
                              </div>
                         </form>
                    </div>
               </div>
          </div>

          <?php endif; ?>

     </div>
</div>

<script>
(function() {
     var app = document.getElementById('qrApp');
     if (!app) {
          return;
     }

     var CSRF = app.dataset.csrf;
     var ENDPOINT = app.dataset.endpoint;

     function post(fields, onDone) {
          var fd = new FormData();
          fd.append('csrf', CSRF);
          Object.keys(fields).forEach(function(k) {
               if (Array.isArray(fields[k])) {
                    fields[k].forEach(function(v) {
                         fd.append(k + '[]', v);
                    });
               } else if (fields[k] !== null && fields[k] !== undefined) {
                    fd.append(k, fields[k]);
               }
          });

          fetch(ENDPOINT, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
               })
               .then(function(r) {
                    return r.json().catch(function() {
                         return {
                              ok: false,
                              error: 'Server returned an unreadable response'
                         };
                    });
               })
               .then(onDone)
               .catch(function(e) {
                    onDone({
                         ok: false,
                         error: e.message
                    });
               });
     }

     function handle(res, successMessage) {
          if (res && res.ok) {
               location.reload();
          } else {
               alert((res && res.error) ? res.error : 'Request failed');
          }
     }

     // Forms posting through the JSON endpoint.
     document.querySelectorAll('form.qr-ajax').forEach(function(form) {
          form.addEventListener('submit', function(e) {
               e.preventDefault();
               var fields = {
                    action: form.dataset.action
               };
               if (form.dataset.qr) {
                    fields.qr_id = form.dataset.qr;
               }

               new FormData(form).forEach(function(v, k) {
                    fields[k] = v;
               });

               var btn = form.querySelector('button[type=submit]');
               if (btn) {
                    btn.disabled = true;
               }

               post(fields, function(res) {
                    if (btn) {
                         btn.disabled = false;
                    }
                    handle(res);
               });
          });
     });

     document.querySelectorAll('.qr-set-status').forEach(function(btn) {
          btn.addEventListener('click', function() {
               var msg = btn.dataset.confirm;
               if (msg && !confirm(msg)) {
                    return;
               }
               post({
                    action: 'set_status',
                    qr_id: new URLSearchParams(location.search).get('qr'),
                    status: btn.dataset.status
               }, handle);
          });
     });

     document.querySelectorAll('.qr-restore').forEach(function(btn) {
          btn.addEventListener('click', function() {
               if (!confirm('Restore version ' + btn.dataset.num +
                         '? This creates a new version with that destination.')) {
                    return;
               }
               post({
                    action: 'restore_version',
                    qr_id: new URLSearchParams(location.search).get('qr'),
                    version_id: btn.dataset.version
               }, handle);
          });
     });

     document.querySelectorAll('.qr-cancel-schedule').forEach(function(btn) {
          btn.addEventListener('click', function() {
               if (!confirm('Cancel this scheduled change?')) {
                    return;
               }
               post({
                    action: 'cancel_schedule',
                    schedule_id: btn.dataset.id
               }, handle);
          });
     });

     document.querySelectorAll('.qr-copy').forEach(function(btn) {
          btn.addEventListener('click', function() {
               navigator.clipboard.writeText(btn.dataset.copy).then(function() {
                    var t = btn.textContent;
                    btn.textContent = 'Copied';
                    setTimeout(function() {
                         btn.textContent = t;
                    }, 1200);
               });
          });
     });

     // Bulk selection.
     var checkAll = document.querySelector('.qr-check-all');
     var boxes = Array.prototype.slice.call(document.querySelectorAll('.qr-check'));
     var bar = document.querySelector('.qr-bulk-bar');
     var counter = document.querySelector('.qr-selected-count');

     function selected() {
          return boxes.filter(function(b) {
                    return b.checked;
               })
               .map(function(b) {
                    return b.value;
               });
     }

     function refresh() {
          var n = selected().length;
          if (counter) {
               counter.textContent = n;
          }
          if (bar) {
               bar.style.display = n > 0 ? '' : 'none';
          }
     }

     if (checkAll) {
          checkAll.addEventListener('change', function() {
               boxes.forEach(function(b) {
                    b.checked = checkAll.checked;
               });
               refresh();
          });
     }
     boxes.forEach(function(b) {
          b.addEventListener('change', refresh);
     });

     document.querySelectorAll('.qr-bulk').forEach(function(btn) {
          btn.addEventListener('click', function() {
               var ids = selected();
               if (!ids.length) {
                    return;
               }
               var act = btn.dataset.bulk;
               if (act === 'archive' || ids.length > 50) {
                    if (!confirm(act + ' ' + ids.length + ' QR code(s)?')) {
                         return;
                    }
               }
               post({
                    action: 'bulk',
                    bulk_action: act,
                    ids: ids
               }, function(res) {
                    if (res && res.ok) {
                         if (res.failed > 0) {
                              alert(res.ok + ' succeeded, ' + res.failed +
                                   ' failed.');
                         }
                         location.reload();
                    } else {
                         alert((res && res.error) ? res.error :
                              'Bulk operation failed');
                    }
               });
          });
     });

     var bulkTags = document.querySelector('.qr-bulk-tags');
     if (bulkTags) {
          bulkTags.addEventListener('click', function() {
               var ids = selected();
               if (!ids.length) {
                    return;
               }
               var tags = prompt('Tags to add (comma separated):');
               if (!tags) {
                    return;
               }
               post({
                    action: 'bulk',
                    bulk_action: 'add_tags',
                    ids: ids,
                    tags: tags
               }, handle);
          });
     }

     // Scan chart. The panel layout does not load Chart.js, so it is fetched
     // on demand and only on the detail page, which is the only place a chart
     // appears. The chart is optional: if the CDN is unreachable the rest of
     // the page still works.
     var canvas = document.getElementById('qrChart');
     if (canvas) {
          if (typeof Chart === 'undefined') {
               var chartScript = document.createElement('script');
               chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
               chartScript.onload = drawChart;
               document.head.appendChild(chartScript);
          } else {
               drawChart();
          }
     }

     function drawChart() {
          var series = JSON.parse(canvas.dataset.series || '[]');
          new Chart(canvas.getContext('2d'), {
               type: 'line',
               data: {
                    labels: series.map(function(s) {
                         return s.date;
                    }),
                    datasets: [{
                         label: 'Scans',
                         data: series.map(function(s) {
                              return s.scan_count;
                         }),
                         borderColor: '#3c8dbc',
                         backgroundColor: 'rgba(60,141,188,0.15)',
                         fill: true,
                         tension: 0.3
                    }]
               },
               options: {
                    responsive: true,
                    plugins: {
                         legend: {
                              display: false
                         }
                    },
                    scales: {
                         y: {
                              beginAtZero: true,
                              ticks: {
                                   precision: 0
                              }
                         }
                    }
               }
          });
     }
})();
</script>