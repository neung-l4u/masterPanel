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
        shops = res.shops || [];
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
        ? `Scan เสร็จล่าสุด ${st.last_finished_at.slice(0, 16).replace('T', ' ')} · ${st.shops} ร้าน · ตัด ID ของ LFY ออก ${st.lfy_ids} รายการ`
        : 'ยังไม่เคย scan — กด Scan now');
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
function render() {
    const rows = filtered().sort((a, b) => {
        const x = a[sort.key] || '', y = b[sort.key] || '';
        return (x < y ? -1 : x > y ? 1 : 0) * (sort.dir === 'asc' ? 1 : -1);
    });
    renderKpis(rows);
    $('#countInfo').text(`${rows.length} จาก ${shops.length} ร้าน`);
    $('#tbl tbody').html(rows.map((s) => {
        const flags = [s.partial && `ยกเลิกบางส่วน: ${s.partial}`, s.rejoined && 'เคย Rejoin', s.addon_only && 'Add-on only', s.dup_rows > 1 && `${s.dup_rows} project`]
            .filter(Boolean).map((f) => `<span class="badge text-bg-info st">${esc(f)}</span>`).join(' ');
        const notInPkg = s.marketing.status === 'Not in package';
        return `<tr data-id="${esc(s.id)}">
            <td><div class="fw-semibold">${esc(s.name)}</div><div class="text-secondary">${esc(s.type)}</div>${flags}</td>
            <td>${esc(s.cc)}</td>
            <td>${esc(s.package)}</td>
            <td class="text-nowrap">${esc(s.cancel_date)}</td>
            <td>${(s.reason || '').length > REASON_MAX ? `${esc(s.reason.slice(0, REASON_MAX))}… <a href="#" class="small">Read more</a>` : esc(s.reason)}${s.reason_src === 'form' ? ' <span class="text-secondary">(ฟอร์มยกเลิก)</span>' : ''}</td>
            <td>${badge(s.marketing.status)}<div>${notInPkg ? '' : esc(channels(s).join(', '))}</div></td>
            <td>${s.agency.name ? `${badge(s.agency.status)}<div>${s.agency.url ? hostLink(s.agency.url).replace(/>[^<]*</, `>${esc(s.agency.name)}<`) : esc(s.agency.name)}</div>` : badge(s.agency.status)}</td>
            <td>${badge(s.system.status)}<div>${esc(providers(s).join(', '))}</div></td>
            <td><div class="text-secondary">${esc(s.site_status)}</div>${(s.evidence || []).slice(0, 2).map(hostLink).join('<br>')}
                ${(s.builder || []).length ? `<div class="text-secondary">เว็บ: ${esc(s.builder.join(', '))}</div>` : ''}
                ${(s.notes || []).map((n) => `<div class="text-warning-emphasis">${esc(n)}</div>`).join('')}</td>
            <td class="text-nowrap">${esc(s.scanned_at)}</td></tr>`;
    }).join(''));
}

function renderKpis(rows) {
    const n = (f) => rows.filter(f).length;
    const k = [
        ['ร้านที่ยกเลิก (ตาม filter)', rows.length],
        ['ยังมีร่องรอยทำ Marketing', n((s) => channels(s).length && s.marketing.status !== 'Not in package')],
        ['ย้ายไปใช้ระบบอื่น (Confirmed)', n((s) => s.system.status === 'Confirmed' && !providers(s).some((p) => p.includes('still LFY')))],
        ['ยังใช้ระบบ LFY อยู่', n((s) => providers(s).some((p) => p.includes('still LFY')))],
        ['ระบุ Agency ได้', n((s) => s.agency.status === 'Confirmed')],
        ['ตรวจเว็บไม่ได้', n((s) => s.site_status !== 'Online')],
    ];
    $('#kpis').html(k.map(([l, v]) => `<div class="col-6 col-md-2"><div class="card"><div class="card-body py-2">
        <div class="kpi-label">${l}</div><div class="kpi-value">${v}</div></div></div></div>`).join(''));
}

const REASON_MAX = 40;

/* ---------- history ---------- */
function showHistory(id) {
    const s = shops.find((x) => x.id === id);
    $('#histTitle').text(s ? s.name : id);
    $('#histReason').html(s && s.reason ? `<div class="fw-semibold">Reason${s.reason_src === 'form' ? ' (ฟอร์มยกเลิก)' : ''}</div><div style="white-space:pre-wrap">${esc(s.reason)}</div>` : '');
    $('#histBody').text('กำลังโหลด…');
    bootstrap.Modal.getOrCreateInstance('#histModal').show();
    $.getJSON(API, { act: 'history', id }).done((h) => {
        if (!h.length) { $('#histBody').text('ยังไม่มีประวัติ'); return; }
        $('#histBody').html(`<table class="table table-sm"><thead><tr><th>วันที่</th><th>เว็บ</th><th>Marketing</th><th>Agency</th><th>System</th><th>Evidence</th></tr></thead><tbody>${
            h.reverse().map((x) => `<tr><td class="text-nowrap">${esc(x.date)}</td><td>${esc(x.site_status)}</td>
                <td>${esc(x.marketing && x.marketing.status)} ${esc(((x.marketing && x.marketing.channels) || []).join(', '))}</td>
                <td>${esc(x.agency && x.agency.name || x.agency && x.agency.status)}</td>
                <td>${esc(x.system && x.system.status)} ${esc(((x.system && x.system.providers) || []).join(', '))}</td>
                <td>${(x.evidence || []).map(hostLink).join('<br>')}</td></tr>`).join('')}</tbody></table>`);
    }).fail(showError);
}

/* ---------- CSV (what is on screen, filters applied) ---------- */
function exportCsv() {
    const head = ['Customer Name', 'Country', 'Shop Type', 'Previous Package', 'Cancellation Date', 'Cancellation Reason', 'Partial Cancel', 'Rejoined',
        'Marketing Status', 'Marketing Channels', 'Agency Status', 'Agency', 'System Status', 'System Provider', 'Website Builder',
        'Site Status', 'Evidence URL', 'Notes', 'Last Scan Date'];
    const q = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
    const lines = filtered().map((s) => [s.name, s.cc, s.type, s.package, s.cancel_date, s.reason, s.partial, s.rejoined ? 'Yes' : '',
        s.marketing.status, channels(s).join(' / '), s.agency.status, s.agency.name, s.system.status, providers(s).join(' / '),
        (s.builder || []).join(' / '), s.site_status, (s.evidence || []).join(' '), (s.notes || []).join(' / '), s.scanned_at].map(q).join(','));
    const blob = new Blob(['﻿' + [head.map(q).join(','), ...lines].join('\n')], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `lost-customers-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
}

/* ---------- events ---------- */
$(function () {
    load();
    $('#fText').on('input', render);
    $('#fCc,#fType,#fPkg,#fFrom,#fTo,#fMkt,#fComp,#fSite').on('change', render);
    $('#btnScan').on('click', () => { if (confirm('เริ่ม scan ใหม่ทั้งหมด? (ใช้เวลาประมาณ 20–40 นาที)')) poll(true); });
    $('#btnCsv').on('click', exportCsv);
    $('#tbl').on('click', 'th.sortable', function () {
        const key = $(this).data('key');
        sort = { key, dir: sort.key === key && sort.dir === 'desc' ? 'asc' : 'desc' };
        $('#tbl th.sortable').removeClass('active').find('.bi').attr('class', 'bi bi-arrow-down-up');
        $(this).addClass('active').find('.bi').attr('class', 'bi bi-arrow-' + (sort.dir === 'asc' ? 'up' : 'down'));
        render();
    });
    $('#tbl').on('click', 'tbody tr', function (e) { if ($(e.target).closest('a').attr('href') === '#') e.preventDefault(); showHistory(String($(this).data('id'))); });
});
