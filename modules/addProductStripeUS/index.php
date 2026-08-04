<?php
/**
 * addProductStripeUS — Temporary UI to create a Stripe Product + its Prices.
 * Standalone page (like other modules). Backend lives in ./api.php.
 *
 * Model: one Product = one contract term (e.g. MAIVB02M00 / M06 / M12).
 * Each product can have many prices (currency × type × interval).
 */
$currentDate = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Stripe Product &amp; Prices</title>
<style>
  :root{
    --bg:#0f1115; --panel:#181b22; --panel2:#1f232c; --line:#2a2f3a;
    --txt:#e6e9ef; --muted:#9aa3b2; --accent:#635bff; --accent2:#8a84ff;
    --ok:#2ecc71; --err:#ff5c6c; --chip:#242a35;
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--txt);
       font:14px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}
  .wrap{max-width:920px;margin:0 auto;padding:28px 18px 80px;}
  h1{font-size:20px;margin:0 0 2px}
  .sub{color:var(--muted);font-size:13px;margin-bottom:22px}
  .card{background:var(--panel);border:1px solid var(--line);border-radius:14px;
        padding:20px;margin-bottom:18px;}
  .card h2{font-size:14px;text-transform:uppercase;letter-spacing:.06em;
           color:var(--muted);margin:0 0 14px;font-weight:600}
  label{display:block;font-size:12px;color:var(--muted);margin:0 0 5px}
  input,select{width:100%;background:var(--panel2);border:1px solid var(--line);
    color:var(--txt);border-radius:9px;padding:10px 12px;font-size:14px;outline:none}
  input:focus,select:focus{border-color:var(--accent)}
  .row{display:grid;gap:12px}
  .g2{grid-template-columns:1fr 1fr}
  .g3{grid-template-columns:1.4fr 1fr 1fr}
  .g5{grid-template-columns:1.4fr .9fr 1.1fr 1fr .9fr 34px;align-items:end}
  .mb{margin-bottom:12px}
  .priceRow{display:grid;gap:10px;margin-bottom:10px}
  .btn{background:var(--accent);color:#fff;border:0;border-radius:9px;
    padding:11px 18px;font-size:14px;font-weight:600;cursor:pointer}
  .btn:hover{background:var(--accent2)}
  .btn:disabled{opacity:.5;cursor:not-allowed}
  .btn-ghost{background:transparent;border:1px dashed var(--line);color:var(--muted);
    padding:9px 14px;font-weight:500}
  .btn-ghost:hover{border-color:var(--accent);color:var(--txt)}
  .del{background:transparent;border:1px solid var(--line);color:var(--err);
    border-radius:8px;height:40px;cursor:pointer;font-size:16px;line-height:1}
  .hint{font-size:11px;color:var(--muted);margin-top:4px}
  .pill{display:inline-block;background:var(--chip);border:1px solid var(--line);
    border-radius:20px;padding:2px 10px;font-size:11px;color:var(--muted);margin-left:6px}
  .interval{display:none}
  /* preview */
  #preview{display:none}
  .pv-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
  .dot{width:10px;height:10px;border-radius:50%;background:var(--muted)}
  .dot.ok{background:var(--ok)} .dot.err{background:var(--err)}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{text-align:left;padding:9px 10px;border-bottom:1px solid var(--line)}
  th{color:var(--muted);font-weight:600;font-size:11px;text-transform:uppercase}
  td code{background:var(--panel2);padding:2px 7px;border-radius:6px;font-size:12px}
  .tag{font-size:11px;padding:2px 8px;border-radius:6px;background:var(--chip)}
  .tag.sub{color:#8a84ff} .tag.one{color:#f0b429}
  .copy{background:var(--chip);border:1px solid var(--line);color:var(--muted);
    border-radius:6px;padding:2px 9px;font-size:11px;cursor:pointer;margin-left:4px}
  .copy:hover{border-color:var(--accent);color:var(--txt)}
  .copy.copied{color:var(--ok);border-color:var(--ok)}
  .rowok td:first-child{border-left:2px solid var(--ok)}
  .rowerr{color:var(--err)}
  .msg{padding:11px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;display:none}
  .msg.show{display:block}
  .msg.ok{background:rgba(46,204,113,.12);border:1px solid var(--ok);color:#7ff0b0}
  .msg.err{background:rgba(255,92,108,.12);border:1px solid var(--err);color:#ffb0b8}
  .spin{display:inline-block;width:14px;height:14px;border:2px solid #fff;
    border-top-color:transparent;border-radius:50%;animation:s .7s linear infinite;
    vertical-align:-2px;margin-right:7px}
  @keyframes s{to{transform:rotate(360deg)}}
  .foot{color:var(--muted);font-size:12px;margin-top:6px}
</style>
</head>
<body>
<div class="wrap">
  <h1>Add Stripe Product &amp; Prices <span class="pill">temporary tool</span></h1>
  <div class="sub">Create one product, then all its prices in one go &middot; <?= htmlspecialchars($currentDate) ?></div>

  <!-- Secret key -->
  <div class="card">
    <h2>Stripe Secret Key</h2>
    <label>Secret key (not stored — used for this request only)</label>
    <input type="password" id="secretKey" placeholder="sk_live_... or sk_test_..." autocomplete="off">
    <div class="hint">US account product example: <code>prod_TcJeLZeufWMSIw</code>. Key stays in your browser; it is posted once to create the product.</div>
  </div>

  <!-- Product -->
  <div class="card">
    <h2>1 &middot; Product</h2>
    <div class="row g2">
      <div>
        <label>Product name</label>
        <input type="text" id="prodName"
          placeholder="MAIVB02M00 Massage AI + Visibility Boost - No Contract">
      </div>
      <div>
        <label>Code (metadata)</label>
        <input type="text" id="prodCode" placeholder="MAIVB02M00">
      </div>
    </div>
    <div class="hint">Created once &rarr; returns <code>stripe_product_id</code>.</div>
  </div>

  <!-- Prices -->
  <div class="card">
    <h2>2 &middot; Prices</h2>
    <div id="priceList"></div>
    <button class="btn btn-ghost" id="addPrice" type="button">+ Add price</button>
    <div class="hint">One row per currency &times; type. Subscription needs an interval (month &times;1, &times;6, year &times;1).</div>
  </div>

  <div id="topMsg" class="msg"></div>
  <button class="btn" id="submitBtn" type="button">Create in Stripe</button>

  <!-- Preview -->
  <div class="card" id="preview">
    <div class="pv-head">
      <span class="dot" id="pvDot"></span>
      <h2 style="margin:0">Result</h2>
    </div>
    <div id="pvProduct" class="mb"></div>
    <div style="overflow-x:auto">
    <table>
      <thead><tr><th>Lookup key</th><th>Currency</th><th>Type</th><th>Price ID</th></tr></thead>
      <tbody id="pvBody"></tbody>
    </table>
    </div>
    <div class="foot" id="pvJsonToggle" style="cursor:pointer;text-decoration:underline">Show raw JSON</div>
    <pre id="pvJson" style="display:none;background:var(--panel2);padding:12px;border-radius:9px;overflow:auto;font-size:12px"></pre>
  </div>
</div>

<script>
const $ = s => document.querySelector(s);
const priceList = $('#priceList');

// Prefill example rows matching the brief (US + CA, onetime + subscription×3 intervals).
const CURRENCIES = ['usd','cad'];
const EXAMPLE = [
  {lk:'USMAIVB02M00O-27', cur:'usd', type:'onetime',      amt:'449.00'},
  {lk:'USMAIVB02M00S-27', cur:'usd', type:'subscription', amt:'449.00', iv:'month', ic:'1'},
  {lk:'CAMAIVB02M00O-27', cur:'cad', type:'onetime',      amt:'449.00'},
  {lk:'CAMAIVB02M00S-27', cur:'cad', type:'subscription', amt:'449.00', iv:'month', ic:'1'},
];

function priceRow(d = {}){
  const wrap = document.createElement('div');
  wrap.className = 'priceRow row g5';
  wrap.innerHTML = `
    <div><label>Lookup key</label><input class="p-lk" value="${d.lk||''}" placeholder="USMAIVB02M00O-27"></div>
    <div><label>Currency</label>
      <select class="p-cur">${CURRENCIES.map(c=>`<option ${d.cur===c?'selected':''}>${c}</option>`).join('')}</select>
    </div>
    <div><label>Type</label>
      <select class="p-type">
        <option value="onetime" ${d.type==='onetime'?'selected':''}>One-time</option>
        <option value="subscription" ${d.type==='subscription'?'selected':''}>Subscription</option>
      </select>
    </div>
    <div><label>Amount</label><input class="p-amt" type="number" step="0.01" value="${d.amt||''}" placeholder="449.00"></div>
    <div class="p-interval interval">
      <label>Interval</label>
      <select class="p-iv">
        <option value="month" data-c="1"  ${d.iv==='month'&&d.ic==='1'?'selected':''}>Monthly</option>
        <option value="month" data-c="6"  ${d.iv==='month'&&d.ic==='6'?'selected':''}>Every 6 months</option>
        <option value="year"  data-c="1"  ${d.iv==='year'?'selected':''}>Yearly</option>
      </select>
    </div>
    <button class="del" type="button" title="Remove">&times;</button>`;
  const typeSel = wrap.querySelector('.p-type');
  const ivBox   = wrap.querySelector('.p-interval');
  const sync = () => ivBox.style.display = typeSel.value === 'subscription' ? 'block' : 'none';
  typeSel.addEventListener('change', sync); sync();
  wrap.querySelector('.del').addEventListener('click', () => wrap.remove());
  return wrap;
}

EXAMPLE.forEach(d => priceList.appendChild(priceRow(d)));
$('#addPrice').addEventListener('click', () => priceList.appendChild(priceRow()));
// prefill product example
$('#prodName').value = 'MAIVB02M00 Massage AI + Visibility Boost - No Contract';
$('#prodCode').value = 'MAIVB02M00';

function collect(){
  const prices = [...priceList.querySelectorAll('.priceRow')].map(r => {
    const type = r.querySelector('.p-type').value;
    const p = {
      lookup_key: r.querySelector('.p-lk').value.trim(),
      currency:   r.querySelector('.p-cur').value,
      type,
      amount:     parseFloat(r.querySelector('.p-amt').value),
    };
    if (type === 'subscription'){
      const iv = r.querySelector('.p-iv');
      p.interval = iv.value;
      p.interval_count = parseInt(iv.selectedOptions[0].dataset.c, 10);
    }
    return p;
  });
  return {
    secret_key: $('#secretKey').value.trim(),
    product: { name: $('#prodName').value.trim(), code: $('#prodCode').value.trim() },
    prices,
  };
}

function showMsg(kind, text){
  const m = $('#topMsg'); m.className = 'msg show ' + kind; m.textContent = text;
}

$('#submitBtn').addEventListener('click', async () => {
  const payload = collect();
  if (!payload.secret_key.startsWith('sk_')) return showMsg('err','Enter a valid secret key (sk_...).');
  if (!payload.product.name) return showMsg('err','Product name is required.');
  if (!payload.prices.length) return showMsg('err','Add at least one price.');
  if (payload.prices.some(p => !p.amount || isNaN(p.amount))) return showMsg('err','Every price needs a valid amount.');

  const btn = $('#submitBtn');
  btn.disabled = true; btn.innerHTML = '<span class="spin"></span>Creating…';
  $('#topMsg').className = 'msg';

  try {
    const res  = await fetch('api.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    render(data);
    if (data.success){
      showMsg('ok', 'Product and all prices created.');
      resetForm();
    } else {
      showMsg('err', data.error || 'Some prices failed — see result below.');
    }
  } catch(e){
    showMsg('err', 'Request failed: ' + e.message);
  } finally {
    btn.disabled = false; btn.textContent = 'Create in Stripe';
  }
});

// Clear product + price inputs after a successful create so the next product
// can be entered. Secret key is intentionally kept (same account, next product).
function resetForm(){
  $('#prodName').value = '';
  $('#prodCode').value = '';
  // Keep the existing price rows/layout; only clear editable values.
  priceList.querySelectorAll('.p-lk, .p-amt').forEach(el => el.value = '');
}

function copyText(text, btn){
  navigator.clipboard.writeText(text).then(() => {
    const old = btn.textContent;
    btn.textContent = '✓'; btn.classList.add('copied');
    setTimeout(() => { btn.textContent = old; btn.classList.remove('copied'); }, 1200);
  });
}

function render(data){
  $('#preview').style.display = 'block';
  $('#pvDot').className = 'dot ' + (data.success ? 'ok' : 'err');
  $('#pvJson').textContent = JSON.stringify(data, null, 2);

  if (data.product){
    const pid = data.product.stripe_product_id || '';
    $('#pvProduct').innerHTML =
      `<div><strong>${data.product.name || ''}</strong></div>
       <div class="hint">code <code>${data.product.code||''}</code>
       &nbsp;&middot;&nbsp; product id <code>${pid||'—'}</code>
       ${pid ? `<button class="copy" data-copy="${pid}">copy</button>` : ''}</div>`;
  }
  const body = $('#pvBody'); body.innerHTML = '';
  (data.prices || []).forEach(p => {
    const tr = document.createElement('tr');
    tr.className = p.success === false ? 'rowerr' : 'rowok';
    const typeTag = p.type === 'subscription'
      ? '<span class="tag sub">subscription</span>'
      : '<span class="tag one">onetime</span>';
    const priceCell = p.stripe_price_id
      ? `<code>${p.stripe_price_id}</code> <button class="copy" data-copy="${p.stripe_price_id}">copy</button>`
      : (p.error||'failed');
    tr.innerHTML = `
      <td><code>${p.lookup_key||'—'}</code></td>
      <td>${(p.currency||'').toUpperCase()}</td>
      <td>${typeTag}</td>
      <td>${priceCell}</td>`;
    body.appendChild(tr);
  });

  // Wire all copy buttons (product + prices) via one delegated handler below.
  $('#preview').scrollIntoView({behavior:'smooth', block:'start'});
}

$('#pvJsonToggle').addEventListener('click', () => {
  const j = $('#pvJson');
  const show = j.style.display === 'none';
  j.style.display = show ? 'block' : 'none';
  $('#pvJsonToggle').textContent = show ? 'Hide raw JSON' : 'Show raw JSON';
});

// Delegated copy handler for product id + price id buttons (rendered dynamically).
$('#preview').addEventListener('click', e => {
  const btn = e.target.closest('.copy');
  if (btn) copyText(btn.dataset.copy, btn);
});
</script>
</body>
</html>
