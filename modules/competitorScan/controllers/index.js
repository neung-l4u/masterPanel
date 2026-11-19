/**
 * Lost Customer Tracking — client side. Filtering, sorting and CSV export run in the browser
 * over the latest results (~1,700 rows); the server only scans and stores.
 */
const API = '../models/api.php';
let shops = [];
let sort = { key: 'cancel_date', dir: 'desc' };

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const BADGE = { 'Confirmed': 'success', 'Likely': 'warning', 'Not Detected': 'secondary', 'Unknown': 'light text-dark border', 'Not in package': 'light text-muted border' };
const badge = (st) => `<span class="badge st text-bg-${BADGE[st] || 'light'}">${esc(st)}</span>`;
const providers = (s) => (s.system && s.system.providers) || [];
const channels = (s) => (s.marketing && s.marketing.channels) || [];

function hostLink(u) {
    if (!u) return '';
    let h = u;
    try { const x = new URL(u); h = x.host.replace(/^www\./, '') + (x.pathname.length > 1 ? x.pathname.replace(/\/$/, '').slice(0, 30) : ''); } catch (e) {}
    return `<a href="${esc(u)}" target="_blank" rel="noopener">${esc(h)}</a>`;
}

/* ---------- load ---------- */
function load() {
    $.getJSON(API, { act: 'list' }).done((res) => {
        const today = Date.parse(new Date().toISOString().slice(0, 10));
        shops = (res.shops || []).map((s) => ({ ...s,
            days_gone: s.cancel_date ? Math.round((today - Date.parse(s.cancel_date)) / 86400000) : null,   // negative = cancel date still ahead
            balance_total: Object.values(s.balance || {}).reduce((a, b) => a + b, 0) || null }));
        colorOf = null;   // new data: recompute competitor colours
        fillFilters();
        render();
        showStatus(res.status || {});
        const errs = res.errors || [];
        $('#errCard').toggleClass('d-none', !errs.length);
        $('#errList').html(errs.map((e) => `<li>${esc(e.name)}: ${esc(e.error)}</li>`).join(''));
        if (res.status && res.status.running) poll(false);
    }).fail(showError);
}

function showStatus(st) {
    $('#lastRun').text(st.last_finished_at
        ? `Last scan ${st.last_finished_at.slice(0, 16).replace('T', ' ')} · ${st.shops} shops · ${st.lfy_ids} LFY IDs excluded`
        : 'Not scanned yet — click Scan now');
}

function showError(xhr) {
    const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'โหลดข้อมูลไม่ได้';
    $('#errBanner').text(msg).removeClass('d-none');
}

/* ---------- scan (polls until the run completes) ---------- */
const PHASE = { monday: 'ดึงข้อมูลจาก monday', lfy: 'เก็บ ID ของ LFY จากเว็บลูกค้าปัจจุบัน', scan: 'Scan เว็บร้าน' };
function poll(start) {
    $('#btnScan').prop('disabled', true);
    $('#loadBanner').removeClass('d-none');
    $.getJSON(API, { act: 'step', start: start ? 1 : 0 }).done((st) => {
        if (st.error) $('#errBanner').text('monday / ระบบตอบผิดพลาด จะลองใหม่: ' + st.error).removeClass('d-none');
        else $('#errBanner').addClass('d-none');
        if (st.complete) {
            $('#loadBanner').addClass('d-none'); $('#btnScan').prop('disabled', false);
            load();
            return;
        }
        const pct = st.total ? Math.round(100 * st.loaded / st.total) : 0;
        $('#loadMsg').text(`${PHASE[st.phase] || 'กำลังทำงาน'}${st.total ? ` — ${st.loaded}/${st.total} ร้าน` : ''}${st.busy ? ' (cron กำลังทำอยู่)' : ''}`);
        $('#loadProg').css('width', pct + '%');
        setTimeout(() => poll(false), st.busy ? 10000 : 300);
    }).fail((xhr) => { showError(xhr); setTimeout(() => poll(false), 15000); });
}

/* ---------- filters ---------- */
function fillFilters() {
    const opts = (sel, vals) => {
        const cur = $(sel).val();
        $(sel).find('option:not(:first)').remove();
        [...new Set(vals)].filter(Boolean).sort().forEach((v) => $(sel).append(`<option>${esc(v)}</option>`));
        $(sel).val(cur);
    };
    opts('#fCc', shops.map((s) => s.cc));
    opts('#fType', shops.map((s) => s.type));
    opts('#fComp', shops.flatMap(providers).concat(shops.map((s) => s.agency && s.agency.name)));
    opts('#fSite', shops.map((s) => s.site_status));
}

function filtered() {
    const t = $('#fText').val().trim().toLowerCase(), cc = $('#fCc').val(), type = $('#fType').val(), pkg = $('#fPkg').val();
    const from = $('#fFrom').val(), to = $('#fTo').val(), mkt = $('#fMkt').val(), comp = $('#fComp').val(), site = $('#fSite').val();
    return shops.filter((s) => {
        if (cc && s.cc !== cc) return false;
        if (type && s.type !== type) return false;
        if (site && s.site_status !== site) return false;
        if (pkg === 'marketing' && !s.scope.marketing) return false;
        if (pkg === 'system' && !s.scope.system) return false;
        if (pkg === 'addon' && !s.addon_only) return false;
        if (pkg === 'unknown' && s.package !== 'Unknown') return false;
        if (pkg === 'partial' && !s.partial) return false;
        if (pkg === 'downgrade' && !/Downgrade|Pause/.test((s.mkt_log && s.mkt_log.status) || '')) return false;
        if (pkg === 'cbl' && (s.mkt_log && s.mkt_log.status) !== 'Cancel Before Live') return false;
        if (from && (s.cancel_date || '') < from) return false;
        if (to && (s.cancel_date || '9999') > to) return false;
        if (mkt === 'none' && channels(s).length) return false;
        if (mkt && mkt !== 'none' && !channels(s).includes(mkt)) return false;
        if (comp && !providers(s).includes(comp) && !(s.agency && s.agency.name === comp)) return false;
        if (t && ![s.name, s.domain, s.final_url, s.agency && s.agency.name, providers(s).join(' ')].join(' ').toLowerCase().includes(t)) return false;
        return true;
    });
}

/* ---------- render ---------- */
// Quick views (Stripe-style presets) on top of the filters; outcome() is the same rule the dashboard uses.
let preset = 'all';
const PRESETS = {
    all: () => true,
    moved: (s) => outcome(s) === 'Moved to competitor',
    lfy: (s) => outcome(s) === 'Still on LFY system',
    none: (s) => outcome(s) === 'No competitor found',
    unknown: (s) => outcome(s) === 'Could not check',
    recent: (s) => s.days_gone !== null && s.days_gone <= 90,
};
let pageSize = 10;   // rows per page (picker under the table); 0 = all
let page = 0;
let shown = [];   // the rows on the current page (row / card click → details)
const ago = (s) => s.days_gone === null ? '' : s.days_gone < 0 ? `in ${-s.days_gone} days` : `${s.days_gone} days ago`;
// "Moved to": the competitor (+N), else what we know instead
const SHORT = { 'Still on LFY system': 'Still on LFY', 'No competitor found': 'No competitor found', 'Could not check': 'Could not check' };
// Marketing log says this was not a full cancel: show it next to the shop
const logTag = (s) => ({ 'Downgrade': 'Downgrade', 'Downgrade Before Live': 'Downgrade', 'Cancel Before Live': 'Cancelled before live', 'Pause': 'Paused' })[s.mkt_log && s.mkt_log.status] || '';
const movedTo = (s) => { const c = competitors(s); return c.length ? c[0] + (c.length > 1 ? ` +${c.length - 1}` : '') : ''; };

function render() {
    const rows = filtered().filter(PRESETS[preset]).sort((a, b) => {
        const x = a[sort.key] ?? '', y = b[sort.key] ?? '';
        return (x < y ? -1 : x > y ? 1 : 0) * (sort.dir === 'asc' ? 1 : -1);
    });
    renderKpis(rows);
    if (!$('#viewSummary').hasClass('d-none')) renderSummary(rows);
    const size = pageSize || rows.length || 1;
    const pages = Math.max(1, Math.ceil(rows.length / size));
    page = Math.min(page, pages - 1);
    shown = rows.slice(page * size, page * size + size);
    // page numbers: first, last, and the two around the current one; gaps become "…"
    const nums = [...new Set([0, page - 1, page, page + 1, pages - 1])].filter((n) => n >= 0 && n < pages).sort((a, b) => a - b);
    const btn = (to, label, on, aria) => `<button class="pg${on ? ' on' : ''}" data-to="${to}" ${to < 0 || to >= pages ? 'disabled' : ''} aria-label="${aria || 'Page ' + label}">${label}</button>`;
    $('#pager').html(`<div class="d-flex align-items-center gap-2 text-secondary small">
            <span>${rows.length ? `${page * size + 1}–${page * size + shown.length} of ${rows.length}` : 'No shops match'}</span>
            <select class="form-select form-select-sm w-auto" id="pageSize" aria-label="Rows per page">${[10, 25, 50, 100, 0].map((n) => `<option value="${n}" ${n === pageSize ? 'selected' : ''}>${n || 'All'}</option>`).join('')}</select>
            <span class="d-none d-sm-inline">per page</span></div>
        <div class="d-flex align-items-center gap-1">${btn(page - 1, '<i class="bi bi-chevron-left"></i>', false, 'Previous page')}${
            nums.map((n, i) => (i && n - nums[i - 1] > 1 ? '<span class="px-1 text-secondary">…</span>' : '') + btn(n, n + 1, n === page)).join('')}${
            btn(page + 1, '<i class="bi bi-chevron-right"></i>', false, 'Next page')}</div>`);
    $('#fCount').text(['#fCc', '#fType', '#fPkg', '#fFrom', '#fTo', '#fMkt', '#fComp', '#fSite'].filter((f) => $(f).val()).length || '');
    // phone: two-line list
    $('#cardList').html(shown.map((s, i) => `<div class="shop-row" data-i="${i}">
        <div class="flex-grow-1" style="min-width:0"><div class="nm text-truncate">${esc(s.name.replace(/\s+-\s+BR\./, ' · '))}</div>
            <div class="sub text-truncate">${esc([movedTo(s) || SHORT[outcome(s)], s.cc, ago(s), logTag(s)].filter(Boolean).join(' · '))}</div></div>
        <i class="bi bi-chevron-right text-secondary"></i></div>`).join('')
        || '<div class="text-secondary small py-3 text-center">No shops match the filter.</div>');
    // desktop: six columns, one line per row; the rest is in the details panel
    $('#tbl tbody').html(shown.map((s, i) => {
        const m = movedTo(s);
        return `<tr data-i="${i}">
            <td><div class="fw-semibold text-truncate" style="max-width:min(17rem,16vw)" title="${esc(s.name)}">${esc(s.name)}</div><div class="text-secondary small">${esc(s.cc)} · ${esc(s.type || '')}${logTag(s) ? ` <span class="badge text-bg-light border">${esc(logTag(s))}</span>` : ''}</div></td>
            <td><div class="text-truncate" style="max-width:min(11rem,10vw)" title="${esc(s.package)}">${esc(s.package || '—')}</div></td>
            <td>${esc(s.cancel_date)}<div class="text-secondary small">${esc(ago(s))}</div></td>
            <td>${m ? `<span class="dot" style="background:${compColor(competitors(s)[0]) || OTHER_COLOR}"></span>${esc(m)}` : `<span class="text-secondary">${esc(SHORT[outcome(s)])}</span>`}</td>
            <td>${s.agency.name ? `<div class="text-truncate" style="max-width:min(9rem,8vw)" title="${esc(s.agency.name)} (${esc(s.agency.status)})">${esc(s.agency.name)}</div>` : '<span class="text-secondary">—</span>'}</td>
            <td><div class="text-truncate" style="max-width:min(11rem,10vw)" title="${esc(s.reason)}">${esc(s.reason_group || '—')}</div></td>
            <td class="text-end">${esc(money(s.balance)) || '<span class="text-secondary">—</span>'}</td>
            <td class="text-end text-secondary"><i class="bi bi-three-dots"></i></td></tr>`;
    }).join('') || '<tr><td colspan="8" class="text-secondary text-center py-4">No shops match the filter.</td></tr>');
}

function showSheet(s) {
    const f = (l, v) => `<div class="col-6 mb-3"><div class="lbl">${l}</div><div class="v">${v || '–'}</div></div>`;
    $('#sheetTitle').text(s.name);
    $('#sheetSub').html(`${esc(COUNTRY[s.cc] || s.cc)} · ${esc(s.type || '')}`);
    $('#sheetBody').html(`<div class="row">
        ${f('Package before cancel', esc(s.package))}${f('Total balance', esc(money(s.balance)))}
        ${f('Cancelled', `${esc(s.cancel_date)}<div class="small text-secondary fw-normal">${s.days_gone < 0 ? `in ${-s.days_gone} days` : `${s.days_gone} days ago`}</div>`)}
        ${f('Lifespan', s.lifespan === null || s.lifespan === undefined ? '' : `${s.lifespan.toLocaleString('en')} days`)}
        </div><hr class="mt-0">
        <div class="row">
        ${f('System', `${badge(s.system.status)}<div>${esc(providers(s).join(', '))}</div>`)}
        ${f('Marketing', `${badge(s.marketing.status)}<div>${esc(channels(s).join(', '))}</div>`)}
        ${f('Agency', `${badge(s.agency.status)}<div>${esc(s.agency.name)}</div>${s.agency.via ? `<div class="small text-secondary fw-normal">${esc(s.agency.via)}</div>` : ''}`)}
        ${f('Website', `${esc(s.site_status)}${(s.builder || []).length ? `<div class="small text-secondary fw-normal">Builder: ${esc(s.builder.join(', '))}</div>` : ''}`)}
        </div>
        ${s.reason ? `<hr class="mt-0"><div class="lbl">Reason${{ form: ' (cancel form)', 'marketing log': ' (Marketing log)' }[s.reason_src] || ''} · ${esc(s.reason_group)}</div><div class="mb-3" style="white-space:pre-wrap">${esc(s.reason)}</div>` : ''}
        ${s.mkt_log ? `<div class="lbl">Marketing log (Account Manager board)</div><div class="mb-3 small">
            <b>${esc(s.mkt_log.status || '–')}</b>${s.mkt_log.reason ? ` · ${esc(s.mkt_log.reason)}` : ''}${s.mkt_log.end_date ? ` · end ${esc(s.mkt_log.end_date)}` : ''}
            ${s.mkt_log.note ? `<div class="text-secondary">${esc(s.mkt_log.note)}</div>` : ''}
            ${s.mkt_log.end_date && s.cancel_date && s.mkt_log.end_date !== s.cancel_date ? `<div class="text-warning-emphasis">End date differs from the cancel date we use (${esc(s.cancel_date)})</div>` : ''}</div>` : ''}
        ${(s.evidence || []).length ? `<div class="lbl">Evidence</div><div class="mb-3 small">${s.evidence.map(hostLink).join('<br>')}</div>` : ''}
        ${(s.notes || []).length ? `<div class="mb-3 small text-warning-emphasis">${s.notes.map(esc).join('<br>')}</div>` : ''}
        <div class="small text-secondary mb-2"><i class="bi bi-check2"></i> Last scan ${esc(s.scanned_at)}</div>
        <div class="small text-secondary mb-3 border rounded p-2"><b>Confirmed</b> = clear evidence on their site · <b>Likely</b> = a trace (tag / pixel / link), not confirmed ·
            <b>Not Detected</b> = no evidence found — does <u>not</u> mean they stopped · <b>Unknown</b> = could not check · <b>Not in package</b> = they did not buy this from us</div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark flex-fill" id="sheetHist" data-id="${esc(s.id)}"><i class="bi bi-clock-history"></i> History</button>
            ${s.domain ? `<a class="btn btn-outline-dark flex-fill" target="_blank" rel="noopener" href="https://${esc(s.domain)}/"><i class="bi bi-box-arrow-up-right"></i> Website</a>` : ''}
        </div>`);
    bootstrap.Offcanvas.getOrCreateInstance('#shopSheet').show();
}

function renderKpis(rows) {
    const n = (f) => rows.filter(f).length;
    const k = [
        ['Cancelled Shops (filtered)', rows.length],
        ['Still Doing Marketing', n((s) => channels(s).length && s.marketing.status !== 'Not in package')],
        ['Moved to Competitor (Confirmed)', n((s) => s.system.status === 'Confirmed' && !providers(s).some((p) => p.includes('still LFY')))],
        ['Still Using LFY System', n((s) => providers(s).some((p) => p.includes('still LFY')))],
        ['Agency Identified', n((s) => s.agency.status === 'Confirmed')],
        ['Site Not Checkable', n((s) => s.site_status !== 'Online')],
    ];
    $('#kpis').html(k.map(([l, v]) => `<div class="col-6 col-md-2"><div class="card"><div class="card-body py-2">
        <div class="kpi-label">${l}</div><div class="kpi-value">${v}</div></div></div></div>`).join(''));
}


/* ---------- summary dashboard (Phase 2): same rows as the table, filters applied ---------- */
let withLikely = false;
let period = 0;            // months to show in the summary (0 = all)
let section = 'overview';  // phone: which section is shown (desktop shows all)
const isLfy = (p) => p.includes('still LFY');
// delivery apps sit beside a shop's own ordering (many had them while with us): listed, not counted as "moved"
const MARKETPLACES = ['Uber Eats', 'DoorDash', 'Menulog', 'Deliveroo', 'Just Eat', 'Grubhub'];
// competitor systems the shop now uses: named providers only (an unknown host behind a button or a LINE chat is not a competitor)
const named = (s) => {
    const ok = s.system.status === 'Confirmed' || (withLikely && s.system.status === 'Likely');
    return ok ? providers(s).filter((p) => !isLfy(p) && !p.includes('.') && !p.includes('(chat)')) : [];
};
const competitors = (s) => named(s).filter((p) => !MARKETPLACES.includes(p));
const marketplaces = (s) => named(s).filter((p) => MARKETPLACES.includes(p));
const outcome = (s) => competitors(s).length ? 'Moved to competitor'
    : providers(s).some(isLfy) ? 'Still on LFY system'
    : s.site_status === 'Online' ? 'No competitor found' : 'Could not check';
const OUTCOMES = [['Moved to competitor', '#e66767'], ['Still on LFY system', '#199e70'], ['No competitor found', '#8a8d93'], ['Could not check', '#4a4e55']];
const isBeauty = (s) => /massage|spa|beauty|nail|therap|lash|brow|wellness/i.test(s.type || '');
const median = (a) => { const x = a.filter((v) => v !== null && v !== undefined).sort((p, q) => p - q); return x.length ? x[Math.floor((x.length - 1) / 2)] : null; };
const sumBal = (rows) => rows.reduce((t, s) => { Object.entries(s.balance || {}).forEach(([c, v]) => { t[c] = (t[c] || 0) + v; }); return t; }, {});
const pct = (n, d) => d ? Math.round(100 * n / d) : 0;
const days = (d) => d === null ? '–' : `${d.toLocaleString('en')} d`;
const shopLink = (s) => `<span class="shop-link" data-name="${esc(s.name)}">${esc(s.name)}</span>`;
const tile = (title, body, cls = '') => `<div class="tile ${cls}"><h3>${title}</h3>${body}</div>`;
const legendOf = (items) => `<div class="small muted mb-2">${items.map(([l, c]) => `<span class="me-3 text-nowrap"><span class="legend-dot" style="background:${c}"></span> ${esc(l)}</span>`).join('')}</div>`;

// categorical palette, dark-surface steps (validated: CVD-safe adjacent pairs, labels carry identity too).
// Colour follows the competitor, never its rank in a filtered view.
const PALETTE = ['#3987e5', '#d95926', '#199e70', '#c98500', '#d55181', '#008300', '#9085e9', '#e66767'];
const OTHER_COLOR = '#6f7279';
const COUNTRY = { AU: 'Australia', NZ: 'New Zealand', UK: 'United Kingdom', US: 'United States', CA: 'Canada', TH: 'Thailand' };
let colorOf = null;   // built once from all shops (not the filtered rows) so a filter never repaints a competitor
function compColor(c) {
    if (!colorOf) {
        const cnt = {};
        shops.forEach((s) => (s.system.status === 'Confirmed' || s.system.status === 'Likely') && providers(s)
            .filter((p) => !isLfy(p) && !p.includes('.') && !p.includes('(chat)') && !MARKETPLACES.includes(p)).forEach((p) => { cnt[p] = (cnt[p] || 0) + 1; }));
        colorOf = {};
        Object.entries(cnt).sort((a, b) => b[1] - a[1]).slice(0, PALETTE.length).forEach(([p], i) => { colorOf[p] = PALETTE[i]; });
    }
    return colorOf[c] || null;
}

/** Last 12 calendar months (YYYY-MM), oldest first, ending this month. */
function last12() {
    const d = new Date(); d.setDate(1);
    const out = [];
    for (let i = 11; i >= 0; i--) { const x = new Date(d.getFullYear(), d.getMonth() - i, 1); out.push(`${x.getFullYear()}-${String(x.getMonth() + 1).padStart(2, '0')}`); }
    return out;
}
const MON = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const monLabel = (k) => MON[+k.slice(5) - 1];

/** Small area sparkline; the last point gets a dot. */
function spark(vals, labels, fmt = (v) => v, col = '#3987e5') {
    const W = 220, H = 44, max = Math.max(1, ...vals), n = vals.length;
    const pts = vals.map((v, i) => [i * W / (n - 1), H - 4 - (H - 10) * v / max]);
    const line = pts.map(([x, y], i) => `${i ? 'L' : 'M'}${x.toFixed(1)},${y.toFixed(1)}`).join('');
    const [lx, ly] = pts[n - 1];
    return `<svg viewBox="0 0 ${W} ${H}" class="spark" preserveAspectRatio="none">
        <path d="${line}L${W},${H}L0,${H}Z" fill="${col}" fill-opacity=".18"/><path d="${line}" fill="none" stroke="${col}" stroke-width="2" vector-effect="non-scaling-stroke"/>
        <circle cx="${lx}" cy="${ly}" r="3" fill="${col}"/>
        ${pts.map(([x], i) => `<rect x="${x - W / n / 2}" y="0" width="${W / n}" height="${H}" fill="transparent"><title>${labels[i]}: ${fmt(vals[i])}</title></rect>`).join('')}</svg>`;
}

/** ▲ / ▼ against the previous month; for cancellations "up" is bad (red). */
function delta(cur, prev, upIsBad = true, unit = '') {
    if (prev === null || prev === undefined) return '';
    const d = cur - prev;
    if (!d) return `<span class="muted small">= last month</span>`;
    const bad = (d > 0) === upIsBad;
    return `<span class="small" style="color:${bad ? '#c0392b' : '#198754'}">${d > 0 ? '▲' : '▼'} ${Math.abs(d)}${unit}</span> <span class="muted small">vs last month</span>`;
}

/** Vertical stacked bars per month by outcome, with y grid + month axis; hover a segment for its count. */
function monthBars(keys, months) {
    const narrow = window.innerWidth < 576;
    if (narrow) keys = keys.slice(-6);
    const W = narrow ? 330 : 640, H = narrow ? 200 : 230, L = 30, B = 22, T = 8, plotH = H - B - T, bw = (W - L) / keys.length;
    const peak = Math.max(1, ...keys.map((k) => (months[k] || []).length));
    const step = Math.max(1, Math.ceil(peak / 4 / 5) * 5);
    const top = Math.ceil(peak / step) * step;
    const y = (v) => T + plotH - plotH * v / top;
    let g = '';
    for (let v = 0; v <= top; v += step) g += `<line x1="${L}" x2="${W}" y1="${y(v)}" y2="${y(v)}" stroke="#eceef1"/><text x="${L - 6}" y="${y(v) + 3}" text-anchor="end" class="ax">${v}</text>`;
    const bars = keys.map((k, i) => {
        const list = months[k] || [];
        let acc = 0;
        const x = L + i * bw + bw * .2, w = bw * .6;
        const segs = OUTCOMES.map(([o, col]) => {
            const n = list.filter((s) => outcome(s) === o).length;
            if (!n) return '';
            const y1 = y(acc + n), h = y(acc) - y1 - (acc ? 2 : 0);   // 2px gap between stacked segments
            acc += n;
            return `<rect x="${x}" y="${y1}" width="${w}" height="${Math.max(h, 1)}" fill="${col}" rx="2"><title>${k} · ${o}: ${n}</title></rect>`;
        }).join('');
        return segs + `<text x="${x + w / 2}" y="${H - 6}" text-anchor="middle" class="ax">${monLabel(k)}</text>`
            + (list.length ? `<text x="${x + w / 2}" y="${y(list.length) - 4}" text-anchor="middle" class="ax">${list.length}</text>` : '');
    }).join('');
    return `<svg viewBox="0 0 ${W} ${H}" class="w-100" role="img" aria-label="Cancellations by month">${g}${bars}</svg>`;
}

/** Horizontal bars: [label, value, colour, hoverText][] */
function hbars(items, fmt = (v) => v) {
    const max = Math.max(1, ...items.map((x) => x[1]));
    return `<div class="hbars">${items.map(([l, v, col, tip]) => `<div class="hb" title="${esc(tip || `${l}: ${v}`)}">
        <div class="hb-l">${esc(l)}</div><div class="hb-t"><div style="width:${pct(v, max)}%;background:${col}"></div></div><div class="hb-v">${fmt(v)}</div></div>`).join('')}</div>`;
}

/** Horizontal stacked bars: [label, shops][]; segments by outcome, right column = total · % moved to a competitor. */
function stackBars(items) {
    const max = Math.max(1, ...items.map(([, l]) => l.length));
    return `<div class="hbars">${items.map(([g, l]) => {
        const m = l.filter((s) => outcome(s) === 'Moved to competitor').length;
        const segs = OUTCOMES.map(([o, col]) => { const k = l.filter((s) => outcome(s) === o).length;
            return k ? `<div style="width:${100 * k / max}%;background:${col}" title="${esc(g)} · ${o}: ${k}"></div>` : ''; }).join('');
        return `<div class="hb hb-wide"><div class="hb-l" title="${esc(g)}">${esc(g)}</div><div class="hb-t stack">${segs}</div>
            <div class="hb-v">${l.length} <span class="muted">· ${pct(m, l.length)}%</span></div></div>`;
    }).join('')}</div>`;
}

/** One country's donut (SVG ring, one arc per competitor with a 2px gap) + its legend with counts. */
function donut(cc, list) {
    const cnt = {};
    list.forEach((s) => competitors(s).forEach((c) => { cnt[c] = (cnt[c] || 0) + 1; }));
    const moved = list.filter((s) => competitors(s).length).length;
    const ranked = Object.entries(cnt).sort((a, b) => b[1] - a[1]);
    // slices: coloured competitors, then everyone else folded into "Other"
    const slices = ranked.filter(([c]) => compColor(c)).map(([c, n]) => [c, n, compColor(c)]);
    const rest = ranked.filter(([c]) => !compColor(c));
    const restN = rest.reduce((t, [, n]) => t + n, 0);
    if (restN) slices.push([`Other (${rest.length})`, restN, OTHER_COLOR]);
    const total = slices.reduce((t, [, n]) => t + n, 0);
    const R = 40, C = 2 * Math.PI * R, GAP = slices.length > 1 ? 2 : 0;
    let at = 0;
    const arcs = slices.map(([c, n, col]) => {
        const len = C * n / total;
        const arc = `<circle r="${R}" cx="50" cy="50" fill="none" stroke="${col}" stroke-width="15"
            stroke-dasharray="${Math.max(len - GAP, 0.5)} ${C}" stroke-dashoffset="${-at}" transform="rotate(-90 50 50)"><title>${esc(c)}: ${n} (${pct(n, total)}%)</title></circle>`;
        at += len;
        return arc;
    }).join('');
    const row = ([c, n]) => `<li class="d-flex justify-content-between gap-2"><span class="text-truncate"><span class="legend-dot" style="background:${compColor(c) || OTHER_COLOR}"></span> ${esc(c)}</span>
        <span class="muted text-nowrap">${n} · ${pct(n, total)}%</span></li>`;
    const legend = ranked.slice(0, 5).map(row).join('')
        + (ranked.length > 5 ? `<li><details class="more"><summary class="muted">+ ${ranked.length - 5} more (${ranked.slice(5).reduce((t, [, n]) => t + n, 0)})</summary>
            <ul class="list-unstyled mb-0">${ranked.slice(5).map(row).join('')}</ul></details></li>` : '');
    return `<div class="col-12 col-md-6 col-xxl-3">${tile(`${esc(COUNTRY[cc] || cc)} <span class="muted fw-normal">· ${list.length} cancelled</span>`,
        `<div class="d-flex flex-column flex-sm-row gap-3 align-items-center align-items-sm-start">
            <svg viewBox="0 0 100 100" width="130" height="130" class="flex-shrink-0" role="img" aria-label="${esc(COUNTRY[cc] || cc)}: competitors">
                <circle r="${R}" cx="50" cy="50" fill="none" stroke="#eceef1" stroke-width="15"/>${arcs}
                <text x="50" y="50" text-anchor="middle" font-size="17" font-weight="700" fill="#1a1c20">${moved}</text>
                <text x="50" y="62" text-anchor="middle" font-size="7.5" fill="#6c757d">moved · ${pct(moved, list.length)}%</text></svg>
            <ul class="list-unstyled small mb-0 flex-grow-1 w-100" style="min-width:0">${legend}</ul></div>`)}</div>`;
}

function renderSummary(rows) {
    const all12 = last12();
    if (period) { const from = all12[12 - period] + '-01'; rows = rows.filter((s) => (s.cancel_date || '') >= from); }
    const moved = rows.filter((s) => competitors(s).length);
    const keys = period ? all12.slice(-period) : all12;
    const months = {};
    rows.forEach((s) => { if (s.cancel_date) (months[s.cancel_date.slice(0, 7)] = months[s.cancel_date.slice(0, 7)] || []).push(s); });
    const perMonth = keys.map((k) => (months[k] || []).length);
    const movedPct = keys.map((k) => { const l = months[k] || []; return pct(l.filter((s) => competitors(s).length).length, l.length); });
    const n = perMonth.length, cur = keys[n - 1];

    // row 1: headline tiles
    const lostBal = Object.entries(sumBal(moved)).sort((a, b) => b[1] - a[1]);
    const lfy = rows.filter((s) => outcome(s) === 'Still on LFY system' && s.days_gone > 0)   // cancel date passed: before it they still pay
        .sort((a, b) => (b.cancel_date || '').localeCompare(a.cancel_date || ''));
    const withSystem = rows.filter((s) => s.scope && s.scope.system && s.days_gone > 0).length;
    // marketing after cancelling: shops that had marketing with us and we could check; an ads tag / pixel on the site
    // is a trace (Likely), not proof the ads still run, so it is counted and labelled as such (not tied to the switch)
    const mktScope = rows.filter((s) => s.marketing.status !== 'Not in package' && s.marketing.status !== 'Unknown').length;
    const mkt = rows.filter((s) => channels(s).length && s.marketing.status !== 'Not in package');
    const kpis = `<div class="row g-3 mb-3 sec sec-overview">
        <div class="col-12 col-md-6 col-xl">${tile(`Cancellations · ${monLabel(cur)} ${cur.slice(0, 4)} <span class="muted fw-normal">(month to date)</span>`,
            `<div class="big">${perMonth[n - 1]}</div><div class="small muted">${monLabel(keys[n - 2])}: ${perMonth[n - 2]} · ${n - 1}-month avg ${Math.round(perMonth.slice(0, -1).reduce((a, b) => a + b, 0) / (n - 1))}</div>${spark(perMonth, keys)}`)}</div>
        <div class="col-12 col-md-6 col-xl">${tile('Moved to a competitor',
            `<div class="big">${pct(moved.length, rows.length)}<span class="unit">%</span></div><div class="small muted">${moved.length} of ${rows.length} shops · ${delta(movedPct[n - 1], movedPct[n - 2], true, ' pts')}</div>${spark(movedPct, keys, (v) => v + '%')}`)}</div>
        <div class="col-12 col-md-6 col-xl">${tile('Monthly value lost to competitors',
            lostBal.length ? `<div class="big">${esc(money({ [lostBal[0][0]]: Math.round(lostBal[0][1]) }))}</div>
                <div class="small muted">${esc(money(Object.fromEntries(lostBal.slice(1).map(([c, v]) => [c, Math.round(v)])))) || '&nbsp;'}</div>
                <div class="small muted mt-2">All cancellations: ${esc(money(Object.fromEntries(Object.entries(sumBal(rows)).map(([c, v]) => [c, Math.round(v)]))))}</div>` : '<div class="big">–</div>')}</div>
        <div class="col-12 col-md-6 col-xl">${tile('Median lifespan before cancelling',
            `<div class="big">${median(rows.map((s) => s.lifespan)) ?? '–'}<span class="unit"> days</span></div>
             <div class="small muted">Moved to a competitor: ${days(median(moved.map((s) => s.lifespan)))}</div>
             <div class="small muted">Still on our system: ${days(median(lfy.map((s) => s.lifespan)))}</div>`)}</div>
        <div class="col-12 col-md-6 col-xl">${tile('Still doing marketing',
            `<div class="big">${mkt.length}</div><div class="small muted">${pct(mkt.length, mktScope)}% of ${mktScope} checked shops that had marketing with us${mkt.every((s) => s.marketing.status === 'Likely') && mkt.length ? ' · all <b>Likely</b> (ads tag / pixel on their site)' : ''}</div>
             <div class="small mt-2">${['Google Ads', 'Meta (Facebook / Instagram)'].map((c) => `${esc(c.replace(' (Facebook / Instagram)', ''))} <b>${mkt.filter((s) => channels(s).includes(c)).length}</b>`).join(' · ')}</div>
             <div class="small muted mt-1">Ads still running ≠ another agency — they may run them themselves.</div>`)}</div>
    </div>`;

    // row 2: monthly bars + still on our system
    const row2 = `<div class="row g-3 mb-3">
        <div class="col-12 col-xl-8 sec sec-overview">${tile('Cancellations by month — what happened next', legendOf(OUTCOMES) + monthBars(keys, months))}</div>
        <div class="col-12 col-xl-4 sec sec-shops">${tile('Cancelled but still on our system',
            `<div class="d-flex align-items-end justify-content-between"><div class="big">${lfy.length}</div><div class="small muted text-end">value at stake<br><span class="fw-semibold text-body">${esc(money(Object.fromEntries(Object.entries(sumBal(lfy)).map(([c, v]) => [c, Math.round(v)])))) || '–'}</span>/mo</div></div>
             <div class="meter my-2"><div style="width:${pct(lfy.length, withSystem)}%"></div></div>
             <div class="small muted mb-2">${pct(lfy.length, withSystem)}% of the ${withSystem} cancelled shops that had our system still run it on their site — check billing or win them back.</div>
             <div class="scroll" style="max-height:9.5rem">${lfy.slice(0, 40).map((s) => `<div class="d-flex justify-content-between gap-2 small"><span class="text-truncate">${shopLink(s)}</span><span class="muted text-nowrap">${esc(s.cc)} · ${esc(s.cancel_date)}</span></div>`).join('')}</div>`)}</div>
    </div>`;

    // row 3: donut per country (countries with nobody moved get a one-line note instead)
    const byCc = {};
    rows.forEach((s) => { if (s.cc) (byCc[s.cc] = byCc[s.cc] || []).push(s); });
    const ccs = Object.entries(byCc).sort((a, b) => b[1].length - a[1].length);
    const withMoves = ccs.filter(([, l]) => l.some((s) => competitors(s).length));
    const quiet = ccs.filter(([, l]) => !l.some((s) => competitors(s).length)).map(([cc, l]) => `${COUNTRY[cc] || cc} (${l.length} cancelled)`);
    const row3 = `<div class="sec sec-countries"><div class="row g-3 mb-1 donuts">${withMoves.map(([cc, l]) => donut(cc, l)).join('')}</div>
        <div class="small muted mb-3"><span class="d-md-none">Swipe for more countries → </span>${quiet.length ? 'No competitor found yet: ' + esc(quiet.join(', ')) + '. ' : ''}Same competitor = same colour in every country; the 8 biggest overall get a colour, the rest are grey.</div></div>`;

    // row 4: top competitors + reasons
    const by = {};
    moved.forEach((s) => competitors(s).forEach((c) => { (by[c] = by[c] || []).push(s); }));
    const comp = Object.entries(by).sort((a, b) => b[1].length - a[1].length);
    const apps = {};
    rows.forEach((s) => marketplaces(s).forEach((m) => { apps[m] = (apps[m] || 0) + 1; }));
    const compItems = comp.slice(0, 10).map(([c, list]) => [c, list.length, compColor(c) || OTHER_COLOR,
        `${c}: ${list.length} shops (${list.filter((s) => !isBeauty(s)).length} restaurants, ${list.filter(isBeauty).length} massage/beauty) · lost ${money(sumBal(list))}/mo · median lifespan ${days(median(list.map((s) => s.lifespan)))}`]);
    // trend: top 5 competitors, shops moved per cancel month (own colour each)
    const trend = comp.slice(0, 5).map(([c]) => {
        const v = keys.map((k) => (months[k] || []).filter((s) => competitors(s).includes(c)).length);
        const last3 = v.slice(-3).reduce((a, b) => a + b, 0), prev3 = v.slice(-6, -3).reduce((a, b) => a + b, 0);
        const col = compColor(c) || OTHER_COLOR;
        return `<div class="d-flex align-items-center gap-3 mb-1"><div class="small text-truncate" style="width:9rem"><span class="legend-dot" style="background:${col}"></span> ${esc(c)}</div>
            <div class="flex-grow-1" style="min-width:0">${spark(v, keys, (x) => `${x} shops`, col)}</div>
            <div class="small text-nowrap text-end" style="width:7.5rem"><b>${last3}</b> <span class="muted">last 3 mo</span><br>${v.length >= 6 ? delta(last3, prev3, true).replace('vs last month', 'vs 3 mo before') : ''}</div></div>`;
    }).join('');
    const groups = {};
    rows.forEach((s) => { (groups[s.reason_group] = groups[s.reason_group] || []).push(s); });
    const initials = (c) => c.replace(/[^A-Za-z0-9 ]/g, '').split(' ').filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
    const compRows = `<div class="ios-list d-md-none">${comp.slice(0, 25).map(([c, list]) => `<details class="ios-row">
        <summary><span class="ios-ico" style="background:${compColor(c) || OTHER_COLOR}">${esc(initials(c))}</span>
            <span class="flex-grow-1 text-truncate"><span class="d-block text-truncate">${esc(c)}</span><span class="small muted">${list.length} shops · median ${days(median(list.map((s) => s.lifespan)))}</span></span>
            <span class="text-end small text-nowrap">${esc(money(Object.fromEntries(Object.entries(sumBal(list)).map(([k, v]) => [k, Math.round(v)])))).split(' + ').slice(0, 2).join('<br>') || '–'}</span><i class="bi bi-chevron-right muted ms-1"></i></summary>
        <div class="small ps-5 pb-2">${list.map(shopLink).join('<br>')}</div></details>`).join('')}</div>`;
    const row4 = `<div class="row g-3 mb-3 sec sec-competitors">
        <div class="col-12 col-xl-6">${tile('Top competitors (shops moved)', (compItems.length ? `<div class="d-none d-md-block">${hbars(compItems)}</div>${compRows}` : '<div class="muted small">None.</div>')
            + (Object.keys(apps).length ? `<div class="small muted mt-2">Also on delivery apps (not counted): ${Object.entries(apps).sort((a, b) => b[1] - a[1]).map(([a, k]) => `${esc(a)} ${k}`).join(' · ')}</div>` : ''))}</div>
        <div class="col-12 col-xl-6">${tile('Competitor trend <span class="muted fw-normal">· top 5, shops moved by cancel month</span>',
            trend || '<div class="muted small">None.</div>')}</div>
        <div class="col-12">${tile('Why they left vs what we found <span class="muted fw-normal">· % = moved to a competitor</span>',
            legendOf(OUTCOMES) + stackBars(Object.entries(groups).sort((a, b) => b[1].length - a[1].length))
            + '<div class="small muted mt-2">A reason that says one thing while the shop shows another (e.g. "Closing down" but now on a competitor) is worth a call.</div>')}</div>
    </div>`;

    // row 5: agencies
    // "Bright Design" = "The Bright Design" = "brightdesign.com.au": one agency
    const agKey = (x) => x.toLowerCase().replace(/^(the\s+|www\.)/, '').replace(/\.(com|net|co|org)(\.[a-z]{2})?$/, '').replace(/[^a-z0-9]/g, '');
    const ag = {};
    rows.forEach((s) => { if (s.agency && s.agency.name) (ag[agKey(s.agency.name)] = ag[agKey(s.agency.name)] || []).push(s); });
    const agRows = Object.values(ag).sort((a, b) => b.length - a.length || a[0].agency.name.localeCompare(b[0].agency.name));
    const row5 = `<div class="sec sec-shops">` + tile(`Agencies seen on their sites <span class="muted fw-normal">· ${agRows.length}</span>`, agRows.length
        ? `<div class="ios-list d-md-none">${agRows.map((list) => `<details class="ios-row"><summary>
            <span class="flex-grow-1 text-truncate"><span class="d-block text-truncate">${esc([...new Set(list.map((x) => x.agency.name))].join(' / '))}</span>
                <span class="small muted">${esc(list[0].agency.status)} · ${esc(list[0].agency.via || 'site credit')}</span></span>
            <span class="small text-nowrap">${list.length} shop${list.length > 1 ? 's' : ''}</span><i class="bi bi-chevron-right muted ms-1"></i></summary>
            <div class="small pb-2">${list.map(shopLink).join('<br>')}</div></details>`).join('')}</div>
          <div class="scroll d-none d-md-block" style="max-height:20rem"><table class="table table-sm mb-0"><thead><tr><th>Agency</th><th>Evidence</th><th class="text-end">Shops</th><th>Shops</th></tr></thead><tbody>${
            agRows.map((list) => `<tr><td>${esc([...new Set(list.map((s) => s.agency.name))].join(' / '))}</td>
                <td class="text-nowrap">${badge(list[0].agency.status)} <span class="small muted">${esc(list[0].agency.via || 'site credit')}</span></td>
                <td class="text-end">${list.length}</td><td class="small">${list.map(shopLink).join(', ')}</td></tr>`).join('')}</tbody></table></div>`
        : '<div class="muted small">No agency found in these shops.</div>') + '</div>';

    const seg = (id, opts, cur) => `<div class="seg" id="${id}">${opts.map(([v, l]) => `<button type="button" data-v="${v}" class="${String(v) === String(cur) ? 'on' : ''}">${l}</button>`).join('')}</div>`;
    $('#viewSummary').html(`<div class="dash" data-sec="${section}">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="fw-semibold">Lost Customer Dashboard <span class="muted fw-normal small">· ${rows.length} cancelled shops</span></div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                ${seg('sumPeriod', [[3, '3M'], [6, '6M'], [12, '12M'], [0, 'All']], period)}
                <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="sumLikely" ${withLikely ? 'checked' : ''}>
                    <label class="form-check-label small" for="sumLikely">Count <b>Likely</b></label></div>
            </div>
        </div>
${kpis}${row2}${row3}${row4}${row5}
        <div class="small muted mt-3">Value = the subscriptions a shop cancelled, per currency (not converted). Click a shop name to open it in the table.</div></div>`);
}

/* ---------- history ---------- */
function showHistory(id) {
    const s = shops.find((x) => x.id === id);
    $('#histTitle').text(s ? s.name : id);
    $('#histReason').html(s && s.reason ? `<div class="fw-semibold">Reason${{ form: ' (cancel form)', 'marketing log': ' (Marketing log)' }[s.reason_src] || ''}</div><div style="white-space:pre-wrap">${esc(s.reason)}</div>` : '');
    $('#histBody').text('กำลังโหลด…');
    bootstrap.Modal.getOrCreateInstance('#histModal').show();
    $.getJSON(API, { act: 'history', id }).done((h) => {
        if (!h.length) { $('#histBody').text('ยังไม่มีประวัติ'); return; }
        $('#histBody').html(`<table class="table table-sm"><thead><tr><th>Date</th><th>Website</th><th>Marketing</th><th>Agency</th><th>System</th><th>Evidence</th></tr></thead><tbody>${
            h.reverse().map((x) => `<tr><td class="text-nowrap">${esc(x.date)}</td><td>${esc(x.site_status)}</td>
                <td>${esc(x.marketing && x.marketing.status)} ${esc(((x.marketing && x.marketing.channels) || []).join(', '))}</td>
                <td>${esc(x.agency && x.agency.name || x.agency && x.agency.status)}</td>
                <td>${esc(x.system && x.system.status)} ${esc(((x.system && x.system.providers) || []).join(', '))}</td>
                <td>${(x.evidence || []).map(hostLink).join('<br>')}</td></tr>`).join('')}</tbody></table>`);
    }).fail(showError);
}

const CUR = { AUD: 'A$', NZD: 'NZ$', USD: 'US$', CAD: 'C$', GBP: '£', THB: '฿' };
const money = (b) => Object.entries(b || {}).map(([c, v]) => `${CUR[c] || c + ' '}${v.toLocaleString('en', { maximumFractionDigits: 2 })}`).join(' + ');

/* ---------- CSV (what is on screen, filters applied) ---------- */
function exportCsv() {
    const head = ['Customer Name', 'Country', 'Shop Type', 'Previous Package', 'Cancellation Date', 'Days Since Cancel', 'Lifespan (Days)', 'Total Balance', 'Cancellation Reason', 'Reason Group', 'Partial Cancel', 'Rejoined',
        'Marketing Status', 'Marketing Channels', 'Agency Status', 'Agency', 'System Status', 'System Provider', 'Website Builder',
        'Site Status', 'Evidence URL', 'Notes', 'Last Scan Date'];
    const q = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
    const lines = filtered().map((s) => [s.name, s.cc, s.type, s.package, s.cancel_date, s.days_gone, s.lifespan, money(s.balance), s.reason, s.reason_group, s.partial, s.rejoined ? 'Yes' : '',
        s.marketing.status, channels(s).join(' / '), s.agency.status, s.agency.name, s.system.status, providers(s).join(' / '),
        (s.builder || []).join(' / '), s.site_status, (s.evidence || []).join(' '), (s.notes || []).join(' / '), s.scanned_at].map(q).join(','));
    const blob = new Blob(['﻿' + [head.map(q).join(','), ...lines].join('\n')], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `lost-customers-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
}

function setPreset(p) {
    preset = p; page = 0;
    $('#presets button').removeClass('on').filter(`[data-p=${p}]`).addClass('on');
    $('#presetSel').val(p);
    render();
}

/* ---------- events ---------- */
$(function () {
    load();
    $('#fText').on('input', render);
    $('#fCc,#fType,#fPkg,#fFrom,#fTo,#fMkt,#fComp,#fSite').on('change', render);
    $('#btnScan').on('click', () => { if (confirm('เริ่ม scan ใหม่ทั้งหมด? (ใช้เวลาประมาณ 20–40 นาที)')) poll(true); });
    $('#btnCsv').on('click', exportCsv);
    $('#viewTabs').on('click', 'button[data-view]', function () {
        const v = $(this).data('view');
        $('#viewTabs .nav-link').removeClass('active'); $(this).addClass('active');
        $('#viewTable').toggleClass('d-none', v !== 'Table'); $('#viewSummary').toggleClass('d-none', v !== 'Summary');
        $('#kpis').toggleClass('d-none', v === 'Summary');   // the dashboard has its own headline numbers
        $('#bottomNav button').removeClass('on').filter(`[data-go=${v === 'Table' ? 'table' : section}]`).addClass('on');
        render();
    });
    $('#viewSummary').on('change', '#sumLikely', function () { withLikely = this.checked; render(); });
    $('#viewSummary').on('click', '#sumPeriod button', function () { period = +$(this).data('v'); render(); });
    // phone: bottom navigation picks the Table or one dashboard section; filters live in a bottom sheet
    $('#bottomNav').on('click', 'button', function () {
        const go = String($(this).data('go'));
        $('#bottomNav button').removeClass('on'); $(this).addClass('on');
        if (go !== 'table') section = go;
        $(`#viewTabs [data-view=${go === 'table' ? 'Table' : 'Summary'}]`).trigger('click');
        window.scrollTo(0, Math.max(0, $(go === 'table' ? '#viewTable' : '#viewSummary').offset().top - 60));
    });
    // The phone / desktop layout follows the screen width live (rotating a tablet, resizing, DevTools device mode),
    // not just the width at load: filters move in / out of the bottom sheet and the bottom bar matches the open view.
    const phone = window.matchMedia('(max-width: 767.98px)');
    const applyLayout = () => {
        bootstrap.Offcanvas.getInstance('#shopSheet')?.hide();
        $('#shopSheet').toggleClass('offcanvas-bottom h-auto', phone.matches).toggleClass('offcanvas-end', !phone.matches);
        if (phone.matches) $('#filterCard').appendTo('#filterSheet .offcanvas-body').removeClass('mb-3').addClass('show');
        else { $('#filterCard').addClass('mb-3').insertBefore('#viewTabs'); bootstrap.Offcanvas.getInstance('#filterSheet')?.hide(); }
        $('#bottomNav button').removeClass('on').filter(`[data-go=${$('#viewSummary').hasClass('d-none') ? 'table' : section}]`).addClass('on');
        render();
    };
    phone.addEventListener('change', applyLayout);
    if (phone.matches) $('#viewTabs [data-view=Summary]').trigger('click');   // phones open on the dashboard, not the 1,600-row table
    applyLayout();
    // a shop name in the summary opens it in the table
    $('#viewSummary').on('click', '.shop-link', function () { $('#fText').val($(this).data('name')); $('#viewTabs [data-view=Table]').trigger('click'); });
    $('#tbl').on('click', 'th.sortable', function () {
        const key = $(this).data('key');
        sort = { key, dir: sort.key === key && sort.dir === 'desc' ? 'asc' : 'desc' }; page = 0;
        $('#tbl th.sortable').removeClass('active').find('.bi').attr('class', 'bi bi-arrow-down-up');
        $(this).addClass('active').find('.bi').attr('class', 'bi bi-arrow-' + (sort.dir === 'asc' ? 'up' : 'down'));
        render();
    });
    $('#cardList').on('click', '.shop-row', function () { showSheet(shown[+$(this).data('i')]); });
    $('#pager').on('click', 'button[data-to]', function () { page = +$(this).data('to'); render(); window.scrollTo(0, Math.max(0, $('#viewTable').offset().top - 60)); });
    $('#pager').on('change', '#pageSize', function () { pageSize = +this.value; page = 0; render(); });
    $('#presets').on('click', 'button', function () { setPreset($(this).data('p')); });
    $('#presetSel').on('change', function () { setPreset(this.value); });
    $('#btnFilter').on('click', () => phone.matches ? bootstrap.Offcanvas.getOrCreateInstance('#filterSheet').show() : bootstrap.Collapse.getOrCreateInstance('#filterCard').toggle());
    $('#sheetBody').on('click', '#sheetHist', function () { bootstrap.Offcanvas.getInstance('#shopSheet').hide(); showHistory(String($(this).data('id'))); });
    $('#fText,#fCc,#fType,#fPkg,#fFrom,#fTo,#fMkt,#fComp,#fSite').on('input change', () => { page = 0; });
    $('#tbl').on('click', 'tbody tr[data-i]', function () { showSheet(shown[+$(this).data('i')]); });
});
