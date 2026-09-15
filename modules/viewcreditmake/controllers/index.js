/**
 * Make credit dashboard — client side.
 * All Make API access happens in models/api.php; the token never reaches here.
 */

const API = '../models/api.php';

/* ---------- state ---------- */
let usageData = null;   // last /usage response
let logData = null;     // last /logs response
let orgOffsetMin = 600; // UTC+10 until the server tells us otherwise
let sumSort = { key: 'totalCredits', dir: 'desc' };
let logSort = { key: 'timestamp', dir: 'desc' };
let pickedId = null;    // scenario row clicked
let logPage = 0;
let chartTop = null, chartDaily = null;

/* ---------- formatting ---------- */
const nf0 = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 0 });
const nf1 = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 1 });
const nf2 = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 });

function fmtBytes(n) {
    if (!n) return '0 B';
    const u = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.min(u.length - 1, Math.floor(Math.log(n) / Math.log(1024)));
    return nf1.format(n / Math.pow(1024, i)) + ' ' + u[i];
}

/**
 * Make stamps log timestamps in UTC ("...Z"), but every figure on this page is
 * a calendar day in the ORG timezone (UTC+10). So shift by the org offset and
 * format from the UTC fields — using the browser's local timezone (or the
 * panel's Asia/Bangkok default) would show a different day than Make bills.
 */
function orgTime(iso) {
    const t = Date.parse(iso);
    if (!isFinite(t)) return '—';
    const d = new Date(t + orgOffsetMin * 60000);
    const p = (n) => String(n).padStart(2, '0');
    return d.getUTCFullYear() + '-' + p(d.getUTCMonth() + 1) + '-' + p(d.getUTCDate())
        + ' ' + p(d.getUTCHours()) + ':' + p(d.getUTCMinutes()) + ':' + p(d.getUTCSeconds());
}

/** Today in the org timezone — not the browser's, not Asia/Bangkok. */
function orgToday() {
    const d = new Date(Date.now() + orgOffsetMin * 60000);
    return d.toISOString().slice(0, 10);
}

function shiftDate(date, days) {
    const d = new Date(Date.parse(date + 'T00:00:00Z') + days * 86400000);
    return d.toISOString().slice(0, 10);
}

/* ---------- banners ---------- */
function showLoading(msg, loaded, total, hint) {
    $('#loadMsg').text(msg);
    $('#loadBanner').removeClass('d-none');
    if (total) {
        const pct = Math.round((loaded / total) * 100);
        $('#loadProgWrap').removeClass('d-none');
        $('#loadProg').css('width', pct + '%');
        $('#loadHint').text(hint || ('โหลดแล้ว ' + nf0.format(loaded) + ' / ' + nf0.format(total) + ' scenario (' + pct + '%)'));
    } else {
        $('#loadProgWrap').addClass('d-none');
        $('#loadHint').text(hint || '');
    }
}
const hideLoading = () => $('#loadBanner').addClass('d-none');

function showError(msg) {
    $('#errBanner').removeClass('d-none').html('<i class="bi bi-exclamation-octagon"></i> ' + $('<div>').text(msg).html());
}
const clearError = () => $('#errBanner').addClass('d-none').empty();

/** A partial load means the totals are LOWER than reality — say so explicitly. */
function renderFailBanner(failed, target) {
    const $b = $(target);
    if (!failed || !failed.length) { $b.addClass('d-none').empty(); return; }
    const names = failed.slice(0, 12).map(f => $('<div>').text('· ' + f.name + ' (' + f.error + ')').html()).join('');
    const more = failed.length > 12 ? '<div class="fst-italic">…และอีก ' + nf0.format(failed.length - 12) + ' รายการ</div>' : '';
    $b.removeClass('d-none').html(
        '<div class="fw-semibold"><i class="bi bi-exclamation-triangle"></i> ' +
        'โหลดข้อมูลไม่สำเร็จ ' + nf0.format(failed.length) + ' scenario — ยอดรวมที่แสดงจึงต่ำกว่าความจริง</div>' +
        '<div class="small mt-2">' + names + more + '</div>'
    );
}

/* ---------- tab 1: load ---------- */

/** Poll the incremental snapshot until complete, then render. */
function loadUsage(refresh) {
    clearError();
    const mode = $('#sumMode').val();
    const to = $('#sumTo').val();
    const from = mode === 'single' ? to : $('#sumFrom').val();
    if (!from || !to) { showError('กรุณาเลือกวันที่'); return; }
    if (from > to) { showError('วันที่เริ่มต้องไม่เกินวันที่สิ้นสุด'); return; }

    showLoading('กำลังโหลดข้อมูลจาก Make…', 0, 0,
        'การโหลดครั้งแรกใช้เวลาหลายนาที เพราะต้องดึงข้อมูลทีละ scenario');

    const step = (isRefresh) => {
        $.ajax({
            url: API, method: 'GET', dataType: 'json', cache: false,
            data: { act: 'usage', from: from, to: to, refresh: isRefresh ? 1 : 0 }
        }).done((res) => {
            if (res.timezoneOffsetMinutes !== null && res.timezoneOffsetMinutes !== undefined) {
                orgOffsetMin = res.timezoneOffsetMinutes;
            }
            $('#tzBadge').text(res.timezone || 'ไม่ทราบ');
            usageData = res;
            renderUsage(res);              // render partial data as it arrives
            if (!res.complete) {
                showLoading('กำลังโหลดข้อมูลจาก Make…', res.loaded, res.total);
                setTimeout(() => step(false), 400);   // never re-send refresh=1
            } else {
                hideLoading();
                loadForecast();
            }
        }).fail((xhr) => {
            hideLoading();
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || ('HTTP ' + xhr.status);
            showError('โหลดข้อมูลไม่สำเร็จ: ' + msg);
        });
    };
    step(!!refresh);
}

function loadForecast() {
    const remaining = $('#fcRemaining').val();
    const renewal = $('#fcRenewal').val();
    const buffer = $('#fcBuffer').val();
    if (!renewal || remaining === '' || Number(remaining) < 0 || Number(buffer) <= 0) return;

    $.ajax({
        url: API, method: 'GET', dataType: 'json', cache: false,
        data: { act: 'forecast', remainingCredits: remaining, renewalDate: renewal, buffer: buffer }
    }).done(renderForecast).fail((xhr) => {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || ('HTTP ' + xhr.status);
        showError('คำนวณการคาดการณ์ไม่สำเร็จ: ' + msg);
    });
}

/* ---------- tab 1: render ---------- */
function renderUsage(res) {
    const days = Math.round((Date.parse(res.to + 'T00:00:00Z') - Date.parse(res.from + 'T00:00:00Z')) / 86400000) + 1;
    const t = res.totals;

    $('#kpiCredits').text(nf0.format(t.credits));
    $('#kpiCreditsNote').text(res.from === res.to ? res.from : (res.from + ' ถึง ' + res.to + ' (' + days + ' วัน)'));
    $('#kpiAvg').text(nf0.format(t.credits / days));
    $('#kpiAvgNote').text('หารด้วยจำนวนวันตามปฏิทิน (' + days + ' วัน)');

    $('#fetchedAt').html('ดึงข้อมูลล่าสุด: ' + orgTime(res.fetchedAt) +
        (res.complete ? '' : ' · <span class="text-warning-emphasis">กำลังโหลด ' +
            nf0.format(res.loaded) + '/' + nf0.format(res.total) + '</span>'));

    renderFailBanner(res.failed, '#failBanner');
    renderScenarioTable();
    renderTopChart();
    renderDailyChart();
}

function renderForecast(f) {
    $('#kpiRemaining').text(nf0.format(f.remainingCredits));
    $('#kpiRemainingNote').text('ณ วันที่ ' + f.today + ' (เขตเวลาองค์กร)');
    $('#kpiRequired').text(nf0.format(f.required));
    $('#kpiRequiredNote').text('เฉลี่ย 7 วัน ' + nf0.format(f.avgDaily7) + '/วัน × ' +
        f.daysUntilRenewal + ' วัน × buffer ' + f.buffer + ' (30 วัน: ' + nf0.format(f.avgDaily30) + '/วัน)');

    // Red when short — the whole point of the forecast.
    $('#kpiBalance').text((f.isDeficit ? '−' : '+') + nf0.format(Math.abs(f.balance)))
        .toggleClass('text-danger', !!f.isDeficit)
        .toggleClass('text-success', !f.isDeficit);
    $('#kpiBalanceNote').text(
        (f.isDeficit ? 'เครดิตไม่พอถึงวันต่ออายุ (' + f.renewalDate + ')'
                     : 'เครดิตเพียงพอถึงวันต่ออายุ (' + f.renewalDate + ')') +
        (f.runOutDate ? ' · คาดว่าจะหมดวันที่ ' + f.runOutDate : ' · ยังไม่มีการใช้งาน')
    );
}

/** Rows after search, sorted by the active column. */
function visibleScenarios() {
    if (!usageData) return [];
    const q = ($('#sumSearch').val() || '').toLowerCase().trim();
    const total = usageData.totals.credits || 0;
    let rows = usageData.scenarios.map(s => Object.assign({}, s, {
        pct: total ? (s.totalCredits / total) * 100 : 0
    }));
    if (q) rows = rows.filter(s => (s.name || '').toLowerCase().includes(q));
    const k = sumSort.key, dir = sumSort.dir === 'asc' ? 1 : -1;
    rows.sort((a, b) => {
        const x = a[k], y = b[k];
        if (typeof x === 'string' || typeof y === 'string') {
            return String(x).localeCompare(String(y), 'th') * dir;
        }
        return ((x || 0) - (y || 0)) * dir;
    });
    return rows;
}

function renderScenarioTable() {
    const rows = visibleScenarios();
    const $tb = $('#tblScenarios tbody').empty();

    rows.forEach(s => {
        const status = s.isActive
            ? '<span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle">ทำงาน</span>'
            : '<span class="badge text-bg-secondary-subtle text-secondary-emphasis border">ปิด</span>';
        $tb.append(
            '<tr data-id="' + s.id + '" class="' + (String(pickedId) === String(s.id) ? 'table-active' : '') + '">' +
            '<td>' + $('<div>').text(s.name).html() + '</td>' +
            '<td>' + status + '</td>' +
            '<td class="small text-secondary">' + $('<div>').text(s.schedulingSummary).html() + '</td>' +
            '<td class="num">' + nf0.format(s.totalCredits) + '</td>' +
            '<td class="num">' + nf0.format(s.totalOperations) + '</td>' +
            '<td class="num">' + nf1.format(s.avgCreditsPerDay) + '</td>' +
            '<td class="num">' + nf1.format(s.pct) + '%</td>' +
            '</tr>'
        );
    });

    if (!rows.length) {
        $tb.append('<tr><td colspan="7" class="text-center text-secondary py-4">ไม่พบ scenario ที่ตรงกับคำค้น</td></tr>');
    }

    // Credits Make billed to the org that no visible scenario accounts for:
    // deleted scenarios, or ones outside the teams this token can see.
    const t = usageData.totals;
    const un = t.unaccountedCredits || 0;
    $('#tblScenarios tfoot').html(
        '<tr class="unacc"><td colspan="3">เครดิตที่ระบุ scenario ไม่ได้ ' +
        '<i class="bi bi-question-circle" title="ยอดขององค์กรลบด้วยผลรวมของ scenario ที่มองเห็น — เช่น scenario ที่ถูกลบ หรืออยู่นอกทีมที่ token เข้าถึง"></i>' +
        '</td><td class="num">' + nf0.format(un) + '</td><td colspan="3" class="num">' +
        (un && t.orgTotal ? nf1.format((un / t.orgTotal) * 100) + '% ของยอดองค์กร' : '') + '</td></tr>' +
        '<tr><td colspan="3" class="fw-semibold">รวม (' + nf0.format(rows.length) + ' scenario' +
        (usageData.scenarios.length !== rows.length ? ' จาก ' + nf0.format(usageData.scenarios.length) : '') +
        ')</td><td class="num fw-semibold">' + nf0.format(t.credits) + '</td>' +
        '<td class="num fw-semibold">' + nf0.format(t.operations) + '</td><td colspan="2" class="num">' +
        'ยอดองค์กร ' + nf0.format(t.orgTotal) + '</td></tr>'
    );

    $('#sumCount').text('· ' + nf0.format(rows.length) + ' รายการ');
}

const PALETTE = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#6f42c1',
                 '#20c997', '#dc3545', '#0dcaf0', '#fd7e14', '#198754', '#6610f2',
                 '#d63384', '#0d6efd', '#adb5bd'];

function renderTopChart() {
    const rows = visibleScenarios().slice().sort((a, b) => b.totalCredits - a.totalCredits).slice(0, 15);
    const ctx = document.getElementById('chartTop').getContext('2d');
    if (chartTop) chartTop.destroy();
    chartTop = new Chart(ctx, {
        type: 'horizontalBar',   // Chart.js 2.x
        data: {
            labels: rows.map(s => s.name.length > 38 ? s.name.slice(0, 37) + '…' : s.name),
            datasets: [{
                data: rows.map(s => Math.round(s.totalCredits)),
                backgroundColor: rows.map((_, i) => PALETTE[i % PALETTE.length]),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            legend: { display: false },
            tooltips: {
                callbacks: {
                    title: (item) => rows[item[0].index].name,
                    label: (item) => 'เครดิต ' + nf0.format(item.xLabel)
                }
            },
            scales: {
                xAxes: [{ ticks: { beginAtZero: true, callback: (v) => nf0.format(v) } }],
                yAxes: [{ ticks: { fontSize: 10 } }]
            }
        }
    });
}

/** Daily stacked chart: one scenario if a row is picked, else top 5 + "อื่น ๆ". */
function renderDailyChart() {
    if (!usageData) return;
    const from = usageData.from, to = usageData.to;
    const dates = [];
    for (let d = from; d <= to; d = shiftDate(d, 1)) dates.push(d);

    let datasets;
    if (pickedId !== null) {
        const s = usageData.scenarios.find(x => String(x.id) === String(pickedId));
        if (!s) { pickedId = null; return renderDailyChart(); }
        const byDate = {};
        s.daily.forEach(d => { byDate[d.date] = d.credits; });
        datasets = [{
            label: s.name,
            data: dates.map(d => Math.round(byDate[d] || 0)),
            backgroundColor: PALETTE[0], borderWidth: 0
        }];
        $('#dailyTitle').text('เครดิตรายวัน · ' + s.name);
        $('#btnClearPick').removeClass('d-none');
    } else {
        const top = usageData.scenarios.slice(0, 5);
        const topIds = new Set(top.map(s => String(s.id)));
        datasets = top.map((s, i) => {
            const byDate = {};
            s.daily.forEach(d => { byDate[d.date] = d.credits; });
            return {
                label: s.name.length > 28 ? s.name.slice(0, 27) + '…' : s.name,
                data: dates.map(d => Math.round(byDate[d] || 0)),
                backgroundColor: PALETTE[i], borderWidth: 0
            };
        });
        const otherByDate = {};
        usageData.scenarios.filter(s => !topIds.has(String(s.id)))
            .forEach(s => s.daily.forEach(d => { otherByDate[d.date] = (otherByDate[d.date] || 0) + d.credits; }));
        datasets.push({
            label: 'อื่น ๆ',
            data: dates.map(d => Math.round(otherByDate[d] || 0)),
            backgroundColor: '#adb5bd', borderWidth: 0
        });
        $('#dailyTitle').text('เครดิตรายวัน · top 5 + อื่น ๆ');
        $('#btnClearPick').addClass('d-none');
    }

    const ctx = document.getElementById('chartDaily').getContext('2d');
    if (chartDaily) chartDaily.destroy();
    chartDaily = new Chart(ctx, {
        type: 'bar',
        data: { labels: dates.map(d => d.slice(5)), datasets: datasets },
        options: {
            responsive: true, maintainAspectRatio: false,
            legend: { position: 'bottom', labels: { boxWidth: 10, fontSize: 10 } },
            tooltips: {
                mode: 'index', intersect: false,
                callbacks: { label: (item, data) => data.datasets[item.datasetIndex].label + ': ' + nf0.format(item.yLabel) }
            },
            scales: {
                xAxes: [{ stacked: true, ticks: { fontSize: 10 } }],
                yAxes: [{ stacked: true, ticks: { beginAtZero: true, callback: (v) => nf0.format(v) } }]
            }
        }
    });
}

/* ---------- tab 2: credit usage log ---------- */
function loadLogs(refresh) {
    const mode = $('#logMode').val();
    const to = $('#logTo').val();
    const from = mode === 'single' ? to : $('#logFrom').val();
    if (!from || !to) { showError('กรุณาเลือกวันที่'); return; }
    if (from > to) { showError('วันที่เริ่มต้องไม่เกินวันที่สิ้นสุด'); return; }
    clearError();

    showLoading('กำลังดึง event log ทีละ scenario…', 0, 0,
        'Make ไม่มี endpoint รวมระดับองค์กร จึงต้องดึงทีละ scenario');

    const step = (isRefresh) => {
        $.ajax({
            url: API, method: 'GET', dataType: 'json', cache: false,
            data: { act: 'logs', from: from, to: to, refresh: isRefresh ? 1 : 0 }
        }).done((res) => {
            if (res.timezoneOffsetMinutes !== null && res.timezoneOffsetMinutes !== undefined) {
                orgOffsetMin = res.timezoneOffsetMinutes;
            }
            if (!res.complete) {
                showLoading('กำลังดึง event log ทีละ scenario…', res.loaded || 0, res.total || 0);
                setTimeout(() => step(false), 400);
                return;
            }
            hideLoading();
            logData = res;
            logPage = 0;
            renderLogs();
        }).fail((xhr) => {
            hideLoading();
            const msg = (xhr.responseJSON && xhr.responseJSON.error) || ('HTTP ' + xhr.status);
            showError('ดึง event log ไม่สำเร็จ: ' + msg);
        });
    };
    step(!!refresh);
}

function visibleLogRows() {
    if (!logData) return [];
    const q = ($('#logSearch').val() || '').toLowerCase().trim();
    let rows = q ? logData.rows.filter(r => (r.scenarioName || '').toLowerCase().includes(q)) : logData.rows.slice();
    const k = logSort.key, dir = logSort.dir === 'asc' ? 1 : -1;
    rows.sort((a, b) => {
        const x = a[k], y = b[k];
        if (k === 'timestamp' || typeof x === 'string' || typeof y === 'string') {
            return String(x).localeCompare(String(y), 'th') * dir;
        }
        return ((x || 0) - (y || 0)) * dir;
    });
    return rows;
}

function renderLogs() {
    const all = visibleLogRows();
    const size = Number($('#logPageSize').val());
    const pages = Math.max(1, Math.ceil(all.length / size));
    if (logPage >= pages) logPage = pages - 1;
    const rows = all.slice(logPage * size, logPage * size + size);

    const $tb = $('#tblLogs tbody').empty();
    rows.forEach(r => {
        $tb.append(
            '<tr>' +
            '<td>' + $('<div>').text(r.scenarioName).html() + '</td>' +
            '<td><span class="badge text-bg-light border text-secondary-emphasis">' +
                $('<div>').text(r.type || '—').html() + '</span></td>' +
            '<td class="num">' + nf2.format(r.credits) + '</td>' +
            '<td class="num">' + nf0.format(r.operations) + '</td>' +
            '<td class="num">' + fmtBytes(r.transfer) + '</td>' +
            '<td class="num">' + orgTime(r.timestamp) + '</td>' +
            '</tr>'
        );
    });
    if (!rows.length) {
        $tb.append('<tr><td colspan="6" class="text-center text-secondary py-4">ไม่พบ event ในช่วงที่เลือก</td></tr>');
    }

    // KPIs follow the current filter, so a search narrows the totals too.
    $('#logKpiEvents').text(nf0.format(all.length));
    $('#logKpiCredits').text(nf2.format(all.reduce((s, r) => s + r.credits, 0)));
    $('#logKpiOps').text(nf0.format(all.reduce((s, r) => s + r.operations, 0)));
    $('#logKpiScenarios').text(nf0.format(new Set(all.map(r => r.scenarioId)).size));

    $('#logPageInfo').text(all.length
        ? ('แสดง ' + nf0.format(logPage * size + 1) + '–' + nf0.format(logPage * size + rows.length) +
           ' จาก ' + nf0.format(all.length) + ' event · หน้า ' + (logPage + 1) + '/' + pages +
           ' · เวลาตามเขตเวลาองค์กร')
        : '');
    $('#logPrev').prop('disabled', logPage === 0);
    $('#logNext').prop('disabled', logPage >= pages - 1);

    renderFailBanner(logData.failed, '#logFailBanner');
    if (logData.truncated && logData.truncated.length) {
        $('#logTruncBanner').removeClass('d-none').html(
            '<i class="bi bi-scissors"></i> scenario ต่อไปนี้มี event เกิน 1,000 รายการในช่วงที่เลือก ' +
            'จึงแสดงไม่ครบ: <span class="fw-semibold">' +
            $('<div>').text(logData.truncated.map(t => t.name).join(', ')).html() + '</span>'
        );
    } else {
        $('#logTruncBanner').addClass('d-none').empty();
    }
}

/* ---------- sort header helper ---------- */
function bindSort($table, state, rerender) {
    $table.find('th.sortable').on('click', function () {
        const key = $(this).data('key');
        if (state.key === key) {
            state.dir = state.dir === 'asc' ? 'desc' : 'asc';
        } else {
            state.key = key;
            state.dir = (key === 'name' || key === 'scenarioName' || key === 'type' ||
                         key === 'schedulingSummary') ? 'asc' : 'desc';
        }
        $table.find('th.sortable').removeClass('active')
            .find('.bi').attr('class', 'bi bi-arrow-down-up');
        $(this).addClass('active').find('.bi')
            .attr('class', 'bi bi-arrow-' + (state.dir === 'asc' ? 'up' : 'down'));
        rerender();
    });
}

/* ---------- init ---------- */
$(() => {
    // Default range: last 7 days in the ORG timezone.
    const today = orgToday();
    $('#sumTo').val(today);
    $('#sumFrom').val(shiftDate(today, -6));
    $('#logTo').val(today);
    $('#logFrom').val(shiftDate(today, -6));
    // Renewal defaults to the 1st of next month — just a starting guess.
    const d = new Date(Date.parse(today + 'T00:00:00Z'));
    $('#fcRenewal').val(new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + 1, 1)).toISOString().slice(0, 10));

    $('#sumMode').on('change', function () {
        const single = $(this).val() === 'single';
        $('#sumFromWrap').toggleClass('d-none', single);
        $('#sumToLabel').text(single ? 'วันที่' : 'ถึง');
        if (!single && $('#sumFrom').val() >= $('#sumTo').val()) {
            // Switching to a range with both boxes on the same day is not a range —
            // back-date "ตั้งแต่" so the user gets an actual span.
            $('#sumFrom').val(shiftDate($('#sumTo').val(), -6));
        }
    });

    $('#logMode').on('change', function () {
        const single = $(this).val() === 'single';
        $('#logFromWrap').toggleClass('d-none', single);
        $('#logToLabel').text(single ? 'วันที่' : 'ถึง');
        if (!single && $('#logFrom').val() >= $('#logTo').val()) {
            $('#logFrom').val(shiftDate($('#logTo').val(), -6));
        }
    });

    $('#btnApply').on('click', () => loadUsage(false));
    $('#btnRefresh').on('click', () => loadUsage(true));
    $('#sumSearch').on('input', () => { renderScenarioTable(); renderTopChart(); });
    $('#btnClearPick').on('click', () => { pickedId = null; renderScenarioTable(); renderDailyChart(); });

    $('#tblScenarios tbody').on('click', 'tr', function () {
        const id = $(this).data('id');
        if (id === undefined) return;
        pickedId = (String(pickedId) === String(id)) ? null : id;  // click again to clear
        renderScenarioTable();
        renderDailyChart();
    });

    $('#btnLogLoad').on('click', () => loadLogs(false));
    $('#btnLogRefresh').on('click', () => loadLogs(true));
    $('#logSearch').on('input', () => { logPage = 0; renderLogs(); });
    $('#logPageSize').on('change', () => { logPage = 0; renderLogs(); });
    $('#logPrev').on('click', () => { if (logPage > 0) { logPage--; renderLogs(); } });
    $('#logNext').on('click', () => { logPage++; renderLogs(); });

    bindSort($('#tblScenarios'), sumSort, () => { renderScenarioTable(); renderTopChart(); });
    bindSort($('#tblLogs'), logSort, () => { logPage = 0; renderLogs(); });

    // Tab 2 is heavy, so it loads on first open rather than up front.
    $('#tabLogsBtn').one('shown.bs.tab', () => loadLogs(false));

    loadUsage(false);
});
