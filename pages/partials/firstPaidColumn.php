<?php
/**
 * Shared "First Paid" column + invoice detail modal.
 *
 * Included by both signupLogs.php and viewLogs.php so the two tables stay in
 * sync. Requires jQuery, a DataTable whose rows contain .first-paid-cell
 * placeholders, and a drawCallback that calls loadFirstPaid().
 */
?>
        <!-- Invoice Detail Modal -->
        <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-receipt mr-2"></i>Invoice Detail</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="invoiceModalBody">
                        <div class="text-center text-muted py-5">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2">Loading from Stripe...</div>
                        </div>
                    </div>
                    <div class="modal-footer" id="invoiceModalFooter"></div>
                </div>
            </div>
        </div>

<script>
    // ---- First Paid column -------------------------------------------------
    // Statuses are fetched after each draw so a slow Stripe round-trip never
    // delays the table itself. Results are memoised per page load; the backend
    // additionally caches to disk.
    const firstPaidCache = {};

    function renderFirstPaid($cell, info) {
        if (!info) {
            $cell.html('<span class="text-muted">-</span>');
            return;
        }

        const badges = {
            paid:          ['badge-success',   'bi-check-circle-fill', 'Paid'],
            open:          ['badge-warning',   'bi-clock-fill',        'Unpaid'],
            draft:         ['badge-secondary', 'bi-file-earmark',      'Draft'],
            void:          ['badge-dark',      'bi-slash-circle',      'Void'],
            uncollectible: ['badge-danger',    'bi-exclamation-circle','Uncollectible'],
            none:          [null,              null,                   null],
            error:         ['badge-danger',    'bi-exclamation-triangle', 'Error'],
            unknown:       ['badge-light',     'bi-question-circle',   'Unknown']
        };

        const def = badges[info.status] || badges.unknown;
        if (!def[0]) {
            $cell.html('<span class="text-muted" title="No Stripe invoice for this signup">-</span>');
            return;
        }

        let title = info.status;
        if (info.number) title = 'Invoice ' + info.number;
        if (info.amount_due !== undefined && info.currency) {
            const amt = (info.status === 'paid' ? info.amount_paid : info.amount_due) / 100;
            title += ' - ' + amt.toFixed(2) + ' ' + info.currency;
        }
        if (info.error) title = info.error;

        let html = '<span class="badge ' + def[0] + '" title="' + $('<div>').text(title).html() + '">'
                 + '<i class="bi ' + def[1] + '"></i> ' + def[2] + '</span>';

        // The badge itself stays plain; the eye icon opens the detail modal.
        if (info.invoice_id) {
            html += ' <i class="bi bi-eye-fill invoice-eye" style="cursor:pointer;margin-left:6px;color:#6c757d;"'
                  + ' title="View invoice detail" data-log-id="' + $cell.data('log-id') + '"></i>';
        }
        $cell.html(html);
    }

    // Uncached rows each cost a Stripe round-trip (~0.3s), so fetch in small
    // batches and paint each batch as it lands rather than blocking on all of
    // them. Cached rows resolve instantly and never reach the network.
    const FIRST_PAID_BATCH = 3;

    function applyFirstPaid(ids) {
        $('.first-paid-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (firstPaidCache[id] !== undefined) {
                $cell.data('loading', false);
                renderFirstPaid($cell, firstPaidCache[id]);
            }
        });
    }

    function fetchFirstPaidBatch(queue) {
        if (!queue.length) return;
        const batch = queue.splice(0, FIRST_PAID_BATCH);

        $.ajax({
            url: 'pages/tableRendering/getFirstPaidStatus.php',
            type: 'POST',
            data: { ids: batch.join(',') },
            dataType: 'json'
        }).done(function(res) {
            const data = (res && res.data) || {};
            batch.forEach(function(id) { firstPaidCache[id] = data[id] || null; });
            applyFirstPaid(batch);
        }).fail(function() {
            batch.forEach(function(id) {
                $('.first-paid-cell[data-log-id="' + id + '"]')
                    .data('loading', false)
                    .html('<span class="text-muted" title="Could not reach Stripe">-</span>');
            });
        }).always(function() {
            fetchFirstPaidBatch(queue);
        });
    }

    function loadFirstPaid() {
        const pending = [];

        $('.first-paid-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (firstPaidCache[id] !== undefined) {
                renderFirstPaid($cell, firstPaidCache[id]);
            } else if (!$cell.data('loading')) {
                $cell.data('loading', true);
                pending.push(id);
            }
        });

        if (pending.length) fetchFirstPaidBatch(pending);
    }

    // ---- Invoice detail modal ----------------------------------------------
    function money(cents, cur) {
        if (cents === null || cents === undefined) return '-';
        return (cents / 100).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + (cur || '');
    }

    function ts(unix) {
        if (!unix) return '-';
        return new Date(unix * 1000).toLocaleString();
    }

    function esc(v) {
        return $('<div>').text(v === null || v === undefined ? '' : v).html();
    }

    // Delegated so it keeps working after DataTables redraws the rows.
    $(document).on('click', '.invoice-eye', function() {
        const id = $(this).data('log-id');

        $('#invoiceModalBody').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border" role="status"></div>' +
            '<div class="mt-2">Loading from Stripe...</div></div>');
        $('#invoiceModalFooter').html('');
        $('#invoiceModal').modal('show');

        $.ajax({
            url: 'pages/tableRendering/getInvoiceDetail.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function(res) {
            if (!res || !res.success) {
                $('#invoiceModalBody').html(
                    '<div class="alert alert-warning mb-0"><i class="bi bi-info-circle mr-1"></i>' +
                    esc((res && res.message) || 'Could not load invoice.') + '</div>');
                return;
            }
            renderInvoice(res.data);
        }).fail(function() {
            $('#invoiceModalBody').html(
                '<div class="alert alert-danger mb-0">Could not reach the server.</div>');
        });
    });

    function renderInvoice(d) {
        const statusBadge = {
            paid:          'badge-success',
            open:          'badge-warning',
            draft:         'badge-secondary',
            void:          'badge-dark',
            uncollectible: 'badge-danger'
        }[d.status] || 'badge-light';

        // Icon + colour per timeline entry, mirroring Stripe's activity feed.
        const actIcon = {
            'Payment successfully applied': ['bi-cash-coin', '#0e9f6e'],
            'Invoice finalized':            ['bi-envelope',  '#6772e5'],
            'Invoice voided':               ['bi-slash-circle', '#6b7280'],
            'Marked uncollectible':         ['bi-exclamation-circle', '#e02424']
        };

        let h = '';

        // ---- Header ----------------------------------------------------
        h += '<div class="d-flex align-items-center" style="gap:10px;">'
           + '<h3 class="mb-0" style="font-weight:700;letter-spacing:-.02em;">' + esc(d.number || d.invoice_id) + '</h3>'
           + '<span class="badge ' + statusBadge + '" style="text-transform:capitalize;font-size:12px;padding:5px 9px;">'
           + esc(d.status) + '</span></div>';
        h += '<div class="mt-1 mb-3" style="color:#6b7280;">Billed to <span style="color:#5469d4;font-weight:600;">'
           + esc(d.customer_name || d.shop_name) + '</span> &middot; '
           + esc(money(d.total, d.currency)) + '</div>';

        // ---- Recent activity (top, per Stripe) -------------------------
        h += '<hr style="margin:18px 0;">';
        h += '<h5 style="font-weight:700;margin-bottom:14px;">Recent activity</h5>';

        if (d.timeline && d.timeline.length) {
            h += '<div style="border-left:2px solid #e5e7eb;padding-left:14px;margin-left:6px;">';
            d.timeline.forEach(function(t, i) {
                const ic = actIcon[t.text] || ['bi-dot', '#6b7280'];
                const last = (i === d.timeline.length - 1);
                h += '<div style="position:relative;' + (last ? '' : 'margin-bottom:14px;') + '">'
                   + '<i class="bi ' + ic[0] + '" style="position:absolute;left:-24px;top:1px;'
                   + 'background:#fff;color:' + ic[1] + ';font-size:13px;padding:1px 2px;"></i>'
                   + '<div style="font-size:14px;color:#1f2937;">' + esc(t.text) + '</div>'
                   + '<div style="font-size:12.5px;color:#6b7280;">' + esc(ts(t.at)) + '</div>'
                   + '</div>';
            });
            h += '</div>';
        } else {
            h += '<div style="color:#6b7280;font-size:13.5px;">No activity available.</div>';
        }
        // Stripe's Events API only keeps ~30 days, so the email lines it shows
        // for recent invoices cannot be recovered for older ones.
        h += '<div style="font-size:12px;color:#9ca3af;margin-top:10px;">'
           + '<i class="bi bi-info-circle"></i> Email history older than 30 days is not retained by Stripe.</div>';

        // ---- Summary ---------------------------------------------------
        h += '<hr style="margin:18px 0;">';
        h += '<h5 style="font-weight:700;margin-bottom:14px;">Summary</h5>';

        const lbl = 'font-size:12px;color:#6b7280;font-weight:600;margin-bottom:2px;';
        const val = 'font-size:14px;color:#1f2937;margin-bottom:12px;';

        h += '<div class="row">';
        h += '<div class="col-md-6">'
           + '<div style="' + lbl + '">Billed to</div>'
           + '<div style="font-size:14px;color:#5469d4;font-weight:600;">' + esc(d.customer_name || '-') + '</div>'
           + '<div style="' + val + '">' + esc(d.customer_email || '-') + '</div>'
           + '<div style="' + lbl + '">Customer ID</div>'
           + '<div style="' + val + '"><code style="font-size:12.5px;">' + esc(d.customer_id) + '</code></div>'
           + '<div style="' + lbl + '">Stripe account</div>'
           + '<div style="' + val + '">' + esc(d.account) + ' (' + esc(d.country) + ')</div>'
           + '</div>';
        h += '<div class="col-md-6">'
           + '<div style="' + lbl + '">Invoice number</div><div style="' + val + '">' + esc(d.number || '-') + '</div>'
           + '<div style="' + lbl + '">Currency</div><div style="' + val + '">' + esc(d.currency) + '</div>'
           + '<div style="' + lbl + '">Created</div><div style="' + val + '">' + esc(ts(d.created)) + '</div>'
           + '<div style="' + lbl + '">Due date</div><div style="' + val + '">' + esc(ts(d.due_date)) + '</div>'
           + '<div style="' + lbl + '">Billing method</div><div style="' + val + '">' + esc(d.collection || '-') + '</div>'
           + '</div>';
        h += '</div>';

        // ---- Line items ------------------------------------------------
        const th = 'font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;padding:8px 0;';
        const td = 'font-size:14px;padding:12px 0;border-bottom:1px solid #f3f4f6;';

        h += '<div class="table-responsive mt-3"><table style="width:100%;">'
           + '<thead><tr>'
           + '<th style="' + th + '">Description</th>'
           + '<th style="' + th + 'text-align:right;width:60px;">Qty</th>'
           + '<th style="' + th + 'text-align:right;width:120px;">Unit price</th>'
           + '<th style="' + th + 'text-align:right;width:130px;">Amount</th>'
           + '</tr></thead><tbody>';
        (d.lines || []).forEach(function(l) {
            h += '<tr>'
               + '<td style="' + td + 'font-weight:600;color:#1f2937;">' + esc(l.description) + '</td>'
               + '<td style="' + td + 'text-align:right;color:#6b7280;">' + esc(l.quantity) + '</td>'
               + '<td style="' + td + 'text-align:right;color:#6b7280;">' + esc(money(l.unit_amount, d.currency)) + '</td>'
               + '<td style="' + td + 'text-align:right;color:#6b7280;">' + esc(money(l.amount, d.currency)) + '</td>'
               + '</tr>';
        });
        h += '</tbody></table></div>';

        // ---- Totals ----------------------------------------------------
        function totalRow(label, value, opts) {
            opts = opts || {};
            return '<div class="d-flex justify-content-between" style="padding:7px 0;'
                 + (opts.border ? 'border-top:1px solid #e5e7eb;' : '') + '">'
                 + '<span style="font-size:14px;' + (opts.bold ? 'font-weight:700;color:#1f2937;' : 'color:#6b7280;') + '">'
                 + label + '</span>'
                 + '<span style="font-size:14px;' + (opts.bold ? 'font-weight:700;' : '') + 'color:#1f2937;">'
                 + value + '</span></div>';
        }

        h += '<div class="ml-auto mt-2" style="max-width:340px;">';
        h += totalRow('Subtotal', esc(money(d.subtotal, d.currency)));
        h += totalRow('Tax', d.tax ? esc(money(d.tax, d.currency)) : '-');
        h += totalRow('Total', esc(money(d.total, d.currency)), {bold: true, border: true});
        h += totalRow('Amount paid',
                      (d.amount_paid ? '-' : '') + esc(money(d.amount_paid, d.currency)), {border: true});
        h += totalRow('Amount remaining',
                      esc(money(d.amount_due - d.amount_paid, d.currency)), {bold: true});
        h += '</div>';

        $('#invoiceModalBody').html(h);

        $('#invoiceModalFooter').html(
            '<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>');
    }


    // This partial loads after the table's own script block, so the first draw
    // may have been skipped by the guard there. Fill in whatever is on screen.
    $(function() { loadFirstPaid(); });
</script>
