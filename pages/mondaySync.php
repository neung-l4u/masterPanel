<?php
/**
 * Manual Monday mirror sync.
 *
 * The customer-mode email lookup reads a local mirror, not Monday, so an edit
 * made in Monday is invisible to the form until the mirror is synced. This
 * page runs that sync without shell access.
 *
 * It syncs one country per request: a full run takes about three minutes, so a
 * single request would risk the time limit and would show no progress until it
 * finished. The subscriptions pass runs once, after the countries.
 */

global $db;

$moduleDir = __DIR__ . '/../modules/upgrade-downgrade';
require_once $moduleDir . '/sync/mondayClient.php';
require_once $moduleDir . '/models/shopLookup.php';
require_once $moduleDir . '/models/syncStatus.php';

$status    = upDownMirrorStatus($db);
$counts    = upDownMirrorCountryCounts($db);
$countries = array_keys(UP_DOWN_PROJECT_BOARDS);
?>

<div class="content-wrapper">
  <section class="content pt-3">
    <div class="container-fluid">

      <div class="card">
        <div class="card-header">
          <h3 class="card-title mb-0">
            <i class="bi bi-arrow-repeat"></i> Monday Mirror Sync
          </h3>
        </div>

        <div class="card-body">

          <p class="text-muted">
            The customer upgrade/downgrade form looks a shop up by email from a
            local mirror of Monday. Edits made in Monday appear there only after
            a sync.
          </p>

          <?php if ($status['lastSync'] === null): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Never synced.</strong>
              The mirror is empty, so no customer can be found by email.
            </div>
          <?php elseif ($status['stale']): ?>
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Mirror is stale.</strong>
              Oldest country last synced
              <strong><?php echo htmlspecialchars($status['lastSync']); ?></strong>
              &mdash; <?php echo number_format($status['ageHours'] / 24, 1); ?> days ago
              (limit <?php echo (int) UP_DOWN_STALE_AFTER_HOURS; ?> hours).
              Most recent sync: <?php echo htmlspecialchars((string) $status['newestSync']); ?>.
              Customers are being shown a "may be out of date" warning.
            </div>
          <?php else: ?>
            <div class="alert alert-success">
              <i class="bi bi-check-circle-fill"></i>
              Every country synced within <?php echo (int) UP_DOWN_STALE_AFTER_HOURS; ?> hours.
              Oldest country last synced
              <strong><?php echo htmlspecialchars($status['lastSync']); ?></strong>
              &mdash; <?php echo number_format($status['ageHours'], 1); ?> hours ago.
            </div>
          <?php endif; ?>

          <?php if (!empty($status['missingCountries'])): ?>
            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Never synced:</strong>
              <?php echo htmlspecialchars(implode(', ', $status['missingCountries'])); ?>.
              Customers in these countries cannot be found by email.
            </div>
          <?php endif; ?>

          <p class="text-muted small">
            Mirror holds <?php echo number_format($status['totalShops']); ?> shops
            and <?php echo number_format($status['totalSubs']); ?> active subscriptions.
          </p>

          <div class="row">
            <?php foreach ($counts as $row): ?>
              <div class="col-md-4 col-sm-6 mb-2">
                <label class="d-flex align-items-center gap-2 mb-0">
                  <input type="checkbox" class="ud-country"
                         value="<?php echo htmlspecialchars($row['country']); ?>">
                  <span>
                    <strong><?php echo htmlspecialchars($row['country']); ?></strong>
                    <small class="text-muted">
                      <?php echo number_format($row['shops']); ?> shops,
                      <?php echo number_format($row['withEmail']); ?> with email
                    </small>
                    <br>
                    <small class="text-muted">
                      <?php echo $row['lastSync'] === null
                          ? '<span class="text-danger">never synced</span>'
                          : 'synced ' . htmlspecialchars($row['lastSync']); ?>
                    </small>
                  </span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-3">
            <button id="udSyncSelected" class="btn btn-primary">
              <i class="bi bi-arrow-repeat"></i> Sync selected
            </button>
            <button id="udSyncAll" class="btn btn-outline-primary">
              <i class="bi bi-globe"></i> Sync all
            </button>
            <span id="udSyncNote" class="text-muted small ms-2"></span>
          </div>

          <hr>

          <h5>Results</h5>
          <table class="table table-sm">
            <thead>
              <tr>
                <th style="width:120px">Scope</th>
                <th>Result</th>
                <th style="width:90px" class="text-end">Time</th>
              </tr>
            </thead>
            <tbody id="udSyncResults">
              <tr><td colspan="3" class="text-muted">No run yet.</td></tr>
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </section>
</div>

<script>
(function () {
    // Every country the page offers, in board order, so "Sync all" and the
    // checkbox list cannot disagree about what "all" means.
    var ALL_COUNTRIES = <?php echo json_encode($countries); ?>;

    var $results = $('#udSyncResults');
    var $note    = $('#udSyncNote');
    var $buttons = $('#udSyncSelected, #udSyncAll');
    var running  = false;

    // Returns the row so its pending state can be replaced in place when the
    // request finishes.
    function addRow(scope) {
        var $tr = $('<tr>').append(
            $('<td>').text(scope),
            $('<td>').html('<span class="spinner-border spinner-border-sm"></span> syncing…'),
            $('<td>').addClass('text-end').text('')
        );
        $results.append($tr);
        return $tr;
    }

    // Values are written with .text(), never .html(): every one of them comes
    // from Monday or from a PHP error message, and neither is trusted markup.
    function finishRow($tr, data) {
        var $cell = $tr.children().eq(1).empty();
        var $time = $tr.children().eq(2);

        $time.text(data.elapsed ? data.elapsed + 's' : '');

        if (!data.ok) {
            $tr.addClass('table-danger');
            $cell.append($('<strong>').text('Failed: '))
                 .append(document.createTextNode(data.message || 'Unknown error.'));
            return;
        }

        var parts = [];

        (data.countries || []).forEach(function (c) {
            if (c.error) {
                // The sync leaves a failed country's rows in place; saying so
                // stops anyone reading this as "the country was emptied".
                $tr.addClass('table-danger');
                parts.push(c.country + ': ' + c.error + ' (previous data kept)');
            } else {
                parts.push(
                    c.shops + ' shops (' + c.inserted + ' new, ' + c.updated +
                    ' updated, ' + c.deleted + ' removed), ' + c.withEmail + ' with email'
                );
            }
        });

        var s = data.subscriptions;
        if (s) {
            if (s.error) {
                $tr.addClass('table-danger');
                parts.push('subscriptions: ' + s.error + ' (previous data kept)');
            } else {
                parts.push(
                    s.subscriptions + ' active (' + s.matched.relation + ' by relation, ' +
                    s.matched.name + ' by name, ' + s.matched.none + ' unmatched)'
                );
            }
        }

        if (!$tr.hasClass('table-danger')) { $tr.addClass('table-success'); }
        $cell.text(parts.join(' · ') || 'Done.');
    }

    function postSync(payload) {
        return $.ajax({
            url: 'pages/api/mondaySyncRun.php',
            method: 'POST',
            data: payload,
            dataType: 'json'
        });
    }

    // The proxy answers failures (403, 400, 405) with a JSON body too, so use
    // it when present; only a non-JSON reply gets the generic message.
    function failed($tr, xhr) {
        var body = xhr && xhr.responseJSON;
        if (body && body.message) {
            finishRow($tr, { ok: false, message: body.message });
            return;
        }
        finishRow($tr, {
            ok: false,
            message: 'Request failed. The sync may still be running — check ' +
                     'mirror-sync.log before retrying.'
        });
    }

    // Sequential, not parallel: each country is a multi-second Monday run, and
    // firing six at once would multiply the API load for no gain, since the
    // user waits on the slowest either way.
    function runQueue(countries) {
        if (running) { return; }
        running = true;
        $buttons.prop('disabled', true);
        $results.empty();

        var i = 0;

        function next() {
            if (i >= countries.length) { return runSubscriptions(); }

            var country = countries[i++];
            var $tr     = addRow(country);

            postSync({ country: country })
                .done(function (data) { finishRow($tr, data || { ok: false }); })
                .fail(function (xhr) { failed($tr, xhr); })
                .always(next);
        }

        // Skipped on every country request and run once here: it reads the
        // whole Active Subscriptions board, so running it per country would
        // repeat the same work six times.
        function runSubscriptions() {
            var $tr = addRow('subscriptions');

            postSync({ subs: '1' })
                .done(function (data) { finishRow($tr, data || { ok: false }); })
                .fail(function (xhr) { failed($tr, xhr); })
                .always(function () {
                    running = false;
                    $buttons.prop('disabled', false);
                    $note.text('Done. Reload the page to update the counts above.');
                });
        }

        $note.text('Syncing… this can take a few minutes. Leave this page open.');
        next();
    }

    $('#udSyncSelected').on('click', function () {
        var picked = $('.ud-country:checked').map(function () {
            return $(this).val();
        }).get();

        if (!picked.length) {
            $note.text('Pick at least one country first.');
            return;
        }
        runQueue(picked);
    });

    $('#udSyncAll').on('click', function () {
        runQueue(ALL_COUNTRIES);
    });
})();
</script>
