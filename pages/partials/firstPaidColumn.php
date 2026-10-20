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
// jQuery arrives from main.php below the page content, so wait for it instead
// of running against an undefined $.
(function bootPaidColumns() {
    if (typeof window.jQuery === 'undefined') return setTimeout(bootPaidColumns, 50);
    var $ = window.jQuery;

    // ---- First Paid column -------------------------------------------------
    // Statuses are fetched after each draw so a slow Stripe round-trip never
    // delays the table itself. Results are memoised per page load; the backend
    // additionally caches to disk.
    // Resolved from the header text so this partial works on any page
    // regardless of where these columns sit.

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

    window.loadFirstPaid = function() {
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
        // Pin the locale so thousands/decimal separators do not shift with the
        // viewer's device settings the way the timestamps did.
        return (cents / 100).toLocaleString('en-US', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        }) + ' ' + (cur || '');
    }

    function ts(unix) {
        if (!unix) return '-';
        // Format explicitly rather than via toLocaleString(): a Thai-locale
        // device renders Buddhist years and D/M order, so the same invoice read
        // differently on desktop and mobile. Match the table's own
        // "YYYY-MM-DD HH:MM:SS" style, in the company's Bangkok time.
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Bangkok',
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: false
        }).formatToParts(new Date(unix * 1000)).reduce(function(acc, p) {
            acc[p.type] = p.value; return acc;
        }, {});
        return parts.year + '-' + parts.month + '-' + parts.day +
               ' ' + parts.hour + ':' + parts.minute + ':' + parts.second;
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

    // ---- Sub Paid column ---------------------------------------------------
    // Recurring subscription invoices. The badge shows paid/total; clicking it
    // opens a card list, and a card opens the same detail view First Paid uses.
    const subPaidCache = {};
    const SUB_PAID_BATCH = 3;
    let subListLogId = null;   // row whose card list is currently open

    function renderSubPaid($cell, info) {
        if (!info || !info.success) {
            $cell.html('<span class="text-muted" title="' + esc((info && info.message) || 'No data') + '">-</span>');
            return;
        }
        const d = info.data;
        if (!d.total) {
            $cell.html('<span class="text-muted" title="No subscription invoices yet">-</span>');
            return;
        }

        // All paid reads as healthy; anything outstanding deserves attention.
        const allPaid = d.paid === d.total;
        const cls = allPaid ? 'badge-success' : 'badge-warning';
        const icon = allPaid ? 'bi-arrow-repeat' : 'bi-exclamation-circle';

        $cell.html(
            '<span class="badge ' + cls + ' sub-paid-badge" style="cursor:pointer;" ' +
            'data-log-id="' + $cell.data('log-id') + '" ' +
            'title="Subscription invoices - click to view">' +
            '<i class="bi ' + icon + '"></i> ' + d.paid + '/' + d.total + '</span>');
    }

    function applySubPaid() {
        $('.sub-paid-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (subPaidCache[id] !== undefined) {
                $cell.data('loading', false);
                renderSubPaid($cell, subPaidCache[id]);
            }
        });
    }

    // One request per row, so walk them a few at a time rather than firing
    // dozens of Stripe round-trips at once.
    function fetchSubPaidBatch(queue) {
        if (!queue.length) return;
        const batch = queue.splice(0, SUB_PAID_BATCH);
        let pending = batch.length;

        batch.forEach(function(id) {
            $.ajax({
                url: 'pages/tableRendering/getSubInvoices.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json'
            }).done(function(res) {
                subPaidCache[id] = res;
            }).fail(function() {
                subPaidCache[id] = { success: false, message: 'Could not reach Stripe' };
            }).always(function() {
                if (--pending === 0) { applySubPaid(); fetchSubPaidBatch(queue); }
            });
        });
    }

    window.loadSubPaid = function() {
        const pending = [];
        $('.sub-paid-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (subPaidCache[id] !== undefined) {
                renderSubPaid($cell, subPaidCache[id]);
            } else if (!$cell.data('loading')) {
                $cell.data('loading', true);
                pending.push(id);
            }
        });
        if (pending.length) fetchSubPaidBatch(pending);
    }

    // Card list of subscription invoices.
    $(document).on('click', '.sub-paid-badge', function() {
        const id = $(this).data('log-id');
        const info = subPaidCache[id];
        if (!info || !info.success) return;
        renderSubList(info.data, id);
        $('#invoiceModal').modal('show');
    });

    function renderSubList(d, logId) {
        subListLogId = logId;
        const statusColor = {
            paid: '#0e9f6e', open: '#d97706', draft: '#6b7280',
            void: '#374151', uncollectible: '#e02424'
        };

        let h = '<div class="d-flex align-items-center" style="gap:10px;">'
              + '<h4 class="mb-0" style="font-weight:700;">Subscription invoices</h4>'
              + '<span class="badge badge-light">' + d.paid + ' / ' + d.total + ' paid</span></div>';
        h += '<div class="mt-1 mb-3" style="color:#6b7280;">' + esc(d.shop_name)
           + ' &middot; ' + esc(d.account) + ' (' + esc(d.country) + ')</div>';
        h += '<hr style="margin:16px 0;">';

        d.items.forEach(function(it) {
            const c = statusColor[it.status] || '#6b7280';
            h += '<div class="sub-invoice-card" data-invoice-id="' + esc(it.invoice_id) + '" data-log-id="' + logId + '" '
               + 'style="display:flex;align-items:center;gap:12px;padding:12px 14px;margin-bottom:8px;'
               + 'border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;transition:background .15s;" '
               + 'onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'transparent\'">'
               + '<div style="width:8px;height:38px;border-radius:4px;background:' + c + ';flex-shrink:0;"></div>'
               + '<div style="flex:1;min-width:0;">'
               + '<div style="font-weight:600;font-size:14px;color:#1f2937;">' + esc(it.number || it.invoice_id) + '</div>'
               + '<div style="font-size:12.5px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
               + esc(it.description || '-') + '</div>'
               + '<div style="font-size:12px;color:#9ca3af;">' + esc(ts(it.created)) + '</div>'
               + '</div>'
               + '<div style="text-align:right;flex-shrink:0;">'
               + '<div style="font-weight:600;font-size:14px;">' + esc(money(it.total, it.currency)) + '</div>'
               + '<div style="font-size:12px;text-transform:capitalize;color:' + c + ';">' + esc(it.status) + '</div>'
               + '</div>'
               + '<i class="bi bi-chevron-right" style="color:#9ca3af;flex-shrink:0;"></i>'
               + '</div>';
        });

        $('#invoiceModalBody').html(h);
        $('#invoiceModalFooter').html(
            '<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>');
    }

    // Card -> detail, with a way back to the list.
    $(document).on('click', '.sub-invoice-card', function() {
        const invoiceId = $(this).data('invoice-id');
        const logId = $(this).data('log-id');

        $('#invoiceModalBody').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border" role="status"></div>' +
            '<div class="mt-2">Loading from Stripe...</div></div>');

        $.ajax({
            url: 'pages/tableRendering/getInvoiceDetail.php',
            type: 'POST',
            data: { id: logId, invoice_id: invoiceId },
            dataType: 'json'
        }).done(function(res) {
            if (!res || !res.success) {
                $('#invoiceModalBody').html('<div class="alert alert-warning mb-0">'
                    + esc((res && res.message) || 'Could not load invoice.') + '</div>');
                return;
            }
            renderInvoice(res.data);
            $('#invoiceModalFooter').prepend(
                '<button type="button" class="btn btn-sm btn-outline-secondary sub-back mr-auto">'
                + '<i class="bi bi-arrow-left mr-1"></i>Back</button>');
        }).fail(function() {
            $('#invoiceModalBody').html('<div class="alert alert-danger mb-0">Could not reach the server.</div>');
        });
    });

    $(document).on('click', '.sub-back', function() {
        const info = subPaidCache[subListLogId];
        if (info && info.success) renderSubList(info.data, subListLogId);
    });


    // ---- History Payment column --------------------------------------------
    // One column covering every invoice Stripe holds: the signup charge and all
    // recurring ones. The badge shows paid/total; clicking opens the card list,
    // and a card opens the same detail view.
    const payHistoryCache = {};
    const PAY_HISTORY_BATCH = 3;
    let historyLogId = null;   // row whose card list is currently open

    function renderPayHistory($cell, info) {
        if (!info || !info.success) {
            $cell.html('<span class="text-muted" title="' + esc((info && info.message) || 'No data') + '">-</span>');
            return;
        }
        const d = info.data;
        if (!d.total) {
            $cell.html('<span class="text-muted" title="No invoices yet">-</span>');
            return;
        }

        // All paid reads as healthy; anything outstanding deserves attention.
        const allPaid = d.paid === d.total;
        const cls = allPaid ? 'badge-success' : 'badge-warning';
        const icon = allPaid ? 'bi-receipt' : 'bi-exclamation-circle';

        $cell.html(
            '<span class="badge ' + cls + ' pay-history-badge" style="cursor:pointer;" ' +
            'data-log-id="' + $cell.data('log-id') + '" ' +
            'title="Invoice history - click to view">' +
            '<i class="bi ' + icon + '"></i> ' + d.paid + '/' + d.total + '</span>');
    }

    function applyPayHistory() {
        $('.pay-history-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (payHistoryCache[id] !== undefined) {
                $cell.data('loading', false);
                renderPayHistory($cell, payHistoryCache[id]);
            }
        });
    }

    // One request per row, so walk them a few at a time rather than firing
    // dozens of Stripe round-trips at once.
    function fetchPayHistoryBatch(queue) {
        if (!queue.length) return;
        const batch = queue.splice(0, PAY_HISTORY_BATCH);
        let pending = batch.length;

        batch.forEach(function(id) {
            $.ajax({
                url: 'pages/tableRendering/getPaymentHistory.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json'
            }).done(function(res) {
                payHistoryCache[id] = res;
            }).fail(function() {
                payHistoryCache[id] = { success: false, message: 'Could not reach Stripe' };
            }).always(function() {
                if (--pending === 0) { applyPayHistory(); fetchPayHistoryBatch(queue); }
            });
        });
    }

    window.loadPayHistory = function() {
        const pending = [];
        $('.pay-history-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (payHistoryCache[id] !== undefined) {
                renderPayHistory($cell, payHistoryCache[id]);
            } else if (!$cell.data('loading')) {
                $cell.data('loading', true);
                pending.push(id);
            }
        });
        if (pending.length) fetchPayHistoryBatch(pending);
    };

    $(document).on('click', '.pay-history-badge', function() {
        const id = $(this).data('log-id');
        const info = payHistoryCache[id];
        if (!info || !info.success) return;
        renderHistoryList(info.data, id);
        $('#invoiceModal').modal('show');
    });

    function renderHistoryList(d, logId) {
        historyLogId = logId;
        const statusColor = {
            paid: '#0e9f6e', open: '#d97706', draft: '#6b7280',
            void: '#374151', uncollectible: '#e02424'
        };

        let h = '<div class="d-flex align-items-center" style="gap:10px;">'
              + '<h4 class="mb-0" style="font-weight:700;">Payment history</h4>'
              + '<span class="badge badge-light">' + d.paid + ' / ' + d.total + ' paid</span></div>';
        h += '<div class="mt-1 mb-3" style="color:#6b7280;">' + esc(d.shop_name)
           + ' &middot; ' + esc(d.account) + ' (' + esc(d.country) + ')</div>';
        h += '<hr style="margin:16px 0;">';

        d.items.forEach(function(it) {
            const c = statusColor[it.status] || '#6b7280';
            h += '<div class="history-invoice-card" data-invoice-id="' + esc(it.invoice_id) + '" data-log-id="' + logId + '" '
               + 'style="display:flex;align-items:center;gap:12px;padding:12px 14px;margin-bottom:8px;'
               + 'border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;transition:background .15s;" '
               + 'onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'transparent\'">'
               + '<div style="width:8px;height:38px;border-radius:4px;background:' + c + ';flex-shrink:0;"></div>'
               + '<div style="flex:1;min-width:0;">'
               + '<div style="font-weight:600;font-size:14px;color:#1f2937;">' + esc(it.number || it.invoice_id)
               // Mark the signup charge so it reads apart from the recurring ones.
               + (it.is_first
                    ? ' <span style="font-size:9.5px;color:#5b21b6;background:#ede9fe;'
                      + 'padding:1px 6px;border-radius:9px;font-weight:600;">First</span>'
                    : '')
               + '</div>'
               + '<div style="font-size:12.5px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'
               + esc(it.description || '-') + '</div>'
               + '<div style="font-size:12px;color:#9ca3af;">' + esc(ts(it.created)) + '</div>'
               + '</div>'
               + '<div style="text-align:right;flex-shrink:0;">'
               + '<div style="font-weight:600;font-size:14px;">' + esc(money(it.total, it.currency)) + '</div>'
               + '<div style="font-size:12px;text-transform:capitalize;color:' + c + ';">' + esc(it.status) + '</div>'
               + '</div>'
               + '<i class="bi bi-chevron-right" style="color:#9ca3af;flex-shrink:0;"></i>'
               + '</div>';
        });

        $('#invoiceModalBody').html(h);
        $('#invoiceModalFooter').html(
            '<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>');
    }

    // Card -> detail, with a way back to the list.
    $(document).on('click', '.history-invoice-card', function() {
        const invoiceId = $(this).data('invoice-id');
        const logId = $(this).data('log-id');

        $('#invoiceModalBody').html(
            '<div class="text-center text-muted py-5">' +
            '<div class="spinner-border" role="status"></div>' +
            '<div class="mt-2">Loading from Stripe...</div></div>');

        $.ajax({
            url: 'pages/tableRendering/getInvoiceDetail.php',
            type: 'POST',
            data: { id: logId, invoice_id: invoiceId },
            dataType: 'json'
        }).done(function(res) {
            if (!res || !res.success) {
                $('#invoiceModalBody').html('<div class="alert alert-warning mb-0">'
                    + esc((res && res.message) || 'Could not load invoice.') + '</div>');
                return;
            }
            renderInvoice(res.data);
            $('#invoiceModalFooter').prepend(
                '<button type="button" class="btn btn-sm btn-outline-secondary history-back mr-auto">'
                + '<i class="bi bi-arrow-left mr-1"></i>Back</button>');
        }).fail(function() {
            $('#invoiceModalBody').html('<div class="alert alert-danger mb-0">Could not reach the server.</div>');
        });
    });

    $(document).on('click', '.history-back', function() {
        const info = payHistoryCache[historyLogId];
        if (info && info.success) renderHistoryList(info.data, historyLogId);
    });

    // ---- Payment Methods column --------------------------------------------
    // Mirrors Stripe's own presentation: brand mark, dotted mask + last four,
    // and a "Default" pill on the method invoices actually charge.
    const payMethodCache = {};
    const PAY_METHOD_BATCH = 3;

    // Brand colours taken from each network's own mark so the chip reads at a
    // glance the way it does in the Stripe dashboard.
    const CARD_BRANDS = {
        visa:       ['VISA', '#1434CB'],
        mastercard: ['MC',   '#EB001B'],
        amex:       ['AMEX', '#006FCF'],
        discover:   ['DISC', '#FF6000'],
        jcb:        ['JCB',  '#0B4EA2'],
        unionpay:   ['UP',   '#E21836'],
        diners:     ['DC',   '#0079BE']
    };

    function brandChip(m) {
        const b = CARD_BRANDS[(m.brand || '').toLowerCase()];
        if (b) {
            return '<span style="display:inline-block;background:' + b[1] + ';color:#fff;'
                 + 'font-size:9px;font-weight:700;padding:2px 4px;border-radius:3px;'
                 + 'line-height:1;letter-spacing:.02em;">' + b[0] + '</span>';
        }
        // Bank debits have no card mark; use a neutral bank glyph instead.
        return '<i class="bi bi-bank" style="color:#6b7280;font-size:12px;"></i>';
    }

    function methodLabel(m) {
        const name = (m.brand || m.type || '').replace(/\b\w/g, c => c.toUpperCase());
        return name + (m.last4 ? ' •••• ' + m.last4 : '');
    }

    function renderPayMethod($cell, info) {
        if (!info || !info.success) {
            $cell.html('<span class="text-muted" title="' + esc((info && info.message) || 'No data') + '">-</span>');
            return;
        }
        const d = info.data;
        if (!d.total) {
            $cell.html('<span class="text-muted" title="No saved payment method">-</span>');
            return;
        }

        const primary = d.methods[0];
        // The list is ordered default-first, so the cell already shows the
        // method that gets charged - the "Default" pill only added noise here.
        // It stays on the hover text and in the multi-method modal.
        const tip = methodLabel(primary)
                  + (primary.is_default ? ' (Default)' : '')
                  + (primary.exp ? '  exp ' + primary.exp : '');

        let html = '<span class="pay-method-chip" style="cursor:' + (d.total > 1 ? 'pointer' : 'default') + ';'
                 + 'display:inline-flex;align-items:center;gap:5px;" '
                 + 'data-log-id="' + $cell.data('log-id') + '" '
                 + 'title="' + esc(tip) + '">'
                 + brandChip(primary)
                 + '<span style="font-size:10px;color:#1f2937;">' + esc(methodLabel(primary)) + '</span>';

        // More than one saved method: show the extra count, clickable for detail.
        if (d.total > 1) {
            html += '<span style="font-size:10px;color:#6b7280;">+' + (d.total - 1) + '</span>';
        }
        html += '</span>';
        $cell.html(html);
    }

    function applyPayMethod() {
        $('.pay-method-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (payMethodCache[id] !== undefined) {
                $cell.data('loading', false);
                renderPayMethod($cell, payMethodCache[id]);
            }
        });
    }

    function fetchPayMethodBatch(queue) {
        if (!queue.length) return;
        const batch = queue.splice(0, PAY_METHOD_BATCH);
        let pending = batch.length;

        batch.forEach(function(id) {
            $.ajax({
                url: 'pages/tableRendering/getPaymentMethod.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json'
            }).done(function(res) {
                payMethodCache[id] = res;
            }).fail(function() {
                payMethodCache[id] = { success: false, message: 'Could not reach Stripe' };
            }).always(function() {
                if (--pending === 0) { applyPayMethod(); fetchPayMethodBatch(queue); }
            });
        });
    }

    window.loadPayMethod = function() {
        const pending = [];
        $('.pay-method-cell').each(function() {
            const $cell = $(this);
            const id = $cell.data('log-id');
            if (payMethodCache[id] !== undefined) {
                renderPayMethod($cell, payMethodCache[id]);
            } else if (!$cell.data('loading')) {
                $cell.data('loading', true);
                pending.push(id);
            }
        });
        if (pending.length) fetchPayMethodBatch(pending);
    };

    // Several saved methods: list them all the way Stripe does.
    $(document).on('click', '.pay-method-chip', function() {
        const id = $(this).data('log-id');
        const info = payMethodCache[id];
        if (!info || !info.success || info.data.total < 2) return;
        const d = info.data;

        let h = '<h4 class="mb-1" style="font-weight:700;">Payment methods</h4>';
        h += '<div class="mb-3" style="color:#6b7280;">' + esc(d.account) + ' (' + esc(d.country) + ')</div>';
        h += '<hr style="margin:16px 0;">';

        d.methods.forEach(function(m) {
            h += '<div style="display:flex;align-items:center;gap:10px;padding:12px 14px;'
               + 'margin-bottom:8px;border:1px solid #e5e7eb;border-radius:8px;">'
               + brandChip(m)
               + '<div style="flex:1;min-width:0;">'
               + '<div style="font-weight:600;font-size:14px;">' + esc(methodLabel(m)) + '</div>'
               + (m.exp ? '<div style="font-size:12px;color:#6b7280;">Expires ' + esc(m.exp) + '</div>' : '')
               + '</div>'
               + (m.is_default
                    ? '<span style="font-size:10.5px;color:#0369a1;background:#e0f2fe;padding:2px 8px;border-radius:10px;">Default</span>'
                    : '')
               + '</div>';
        });

        $('#invoiceModalBody').html(h);
        $('#invoiceModalFooter').html(
            '<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>');
        $('#invoiceModal').modal('show');
    });

    $(function() { loadPayHistory(); loadPayMethod(); });

})();
</script>
