<?php
global $db;
$hasTable = (bool) $db->query("SHOW TABLES LIKE 'monitor_templates'")->fetchArray();
?>
<style>
.wh-wrap { padding: 4px; }
.wh-card { background:#fff; border:1px solid #e3e6ef; border-radius:10px; padding:18px; margin-bottom:16px; }
.wh-card h5 { font-weight:600; margin:0; font-size:1rem; }
.wh-key { font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; }
.wh-vars code { background:#f1f3f9; padding:2px 6px; border-radius:4px; margin:0 3px 4px 0;
                display:inline-block; font-size:.78rem; cursor:pointer; }
.wh-body { font-family:ui-monospace,Menlo,Consolas,monospace; font-size:.85rem; min-height:130px; }
.wh-preview { background:#202124; color:#e8eaed; border-radius:8px; padding:12px 14px;
              font-family:ui-monospace,Menlo,Consolas,monospace; font-size:.82rem;
              white-space:pre-wrap; word-break:break-word; }
.wh-preview .men { color:#8ab4f8; font-weight:600; }
.wh-preview .ttl { color:#fdd663; font-weight:600; }
</style>

<div class="wh-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h4 class="mb-1" style="font-weight:600">Manage Text Website Down</h4>
      <div class="text-muted" style="font-size:.85rem">
        ข้อความแจ้งเตือนที่ระบบส่งเข้ากลุ่ม Google Chat "Website Down"
      </div>
    </div>
    <a href="main.php?p=monitor" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i>ไปหน้า Monitor
    </a>
  </div>

<?php if (!$hasTable): ?>
  <div class="alert alert-warning">
    ยังไม่มีตาราง <code>monitor_templates</code> ในฐานข้อมูลนี้ —
    กรุณารันไฟล์ <code>assets/sql/monitor_templates.sql</code> ก่อนใช้งานหน้านี้
  </div>
<?php else: ?>

  <div class="wh-card" style="background:#f8f9ff">
    <div class="wh-key mb-2">ตัวแปรที่ใช้ได้ (คลิกเพื่อคัดลอก)</div>
    <div class="wh-vars">
      <code>{name}</code><code>{domain}</code><code>{url}</code><code>{resolvedIP}</code><code>{httpCode}</code><code>{errorMsg}</code><code>{responseMs}</code><code>{sslExpiry}</code><code>{sslDaysLeft}</code><code>{time}</code>
    </div>
  </div>

  <div id="tplList"><div class="text-muted p-3">กำลังโหลด…</div></div>

<?php endif; ?>
</div>

<script>
const VARS = {
  '{name}':'ตัวอย่างเว็บไซต์ (TEST)', '{url}':'https://example.com', '{domain}':'example.com', '{resolvedIP}':'162.159.140.177',
  '{httpCode}':'500', '{errorMsg}':'WordPress critical error (HTTP 200)',
  '{responseMs}':'812', '{sslExpiry}':'2026-10-01', '{sslDaysLeft}':'15',
  '{time}':'<?php echo date('Y-m-d H:i:s'); ?>'
};
const KEY_LABEL = {
  wp_fatal:     ['WordPress critical error', 'เว็บขึ้น critical error (HTTP 200 แต่หน้าพัง)'],
  down:         ['เว็บล่ม — โดเมนปกติ',      'DNS ปกติ แต่เซิร์ฟเวอร์ตอบไม่ได้ / 404 / 500'],
  expired:      ['โดเมนหมดอายุ',            'WHOIS บอกว่าหมดอายุ หรือถูกระงับ (hold)'],
  unregistered: ['โดเมนไม่ได้ถูกซื้อ',        'WHOIS ไม่พบโดเมนนี้ในระบบทะเบียน'],
  moved_away:   ['ย้ายออกจาก Server เรา',   'เว็บ down และ IP ไม่ได้ชี้มาที่ server เรา — อาจยกเลิกบริการแล้ว'],
  recovered:    ['เว็บกลับมาปกติ',           'ส่งเมื่อเว็บกลับมาใช้งานได้'],
  ssl:          ['SSL กำลังจะหมดอายุ',       'ส่งเมื่อ SSL เหลือ ≤ 1 วัน (วันละครั้ง)'],
  ssl_expired:  ['SSL หมดอายุแล้ว',          'ส่งเมื่อ SSL หมดอายุจริง — เว็บขึ้นหน้าเตือนสีแดง']
};

function h(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function fill(s){ let o = s ?? ''; for (const k in VARS) o = o.split(k).join(VARS[k]); return o; }

function loadList() {
  $.post('assets/php/actionManagerWebhook.php', { act:'getList' }, function(res){
    if (res.status !== 'ok') {
      $('#tplList').html('<div class="alert alert-danger">โหลดข้อมูลไม่สำเร็จ</div>');
      return;
    }
    $('#tplList').html(res.data.map(function(t){
      const lbl = KEY_LABEL[t.tpl_key] || [t.tpl_key, ''];
      return `
      <div class="wh-card" data-id="${t.id}">
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
          <div>
            <h5>${h(lbl[0])}</h5>
            <div class="wh-key">${h(t.tpl_key)} — ${h(lbl[1])}</div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input tpl-active" type="checkbox" ${+t.is_active ? 'checked' : ''}>
            <label class="form-check-label" style="font-size:.85rem">เปิดใช้งาน</label>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-lg-6">
            <label class="form-label" style="font-size:.85rem">หัวข้อ (Title)</label>
            <input type="text" class="form-control form-control-sm tpl-title" value="${h(t.title)}">
            <label class="form-label mt-3" style="font-size:.85rem">เนื้อหา (Body)</label>
            <textarea class="form-control form-control-sm wh-body tpl-body" rows="6">${h(t.body)}</textarea>
            <div class="form-check mt-3">
              <input class="form-check-input tpl-mention" type="checkbox" ${+t.mention_all ? 'checked' : ''}>
              <label class="form-check-label" style="font-size:.85rem">
                แท็กทุกคนในกลุ่ม <code>@all</code>
              </label>
            </div>
          </div>
          <div class="col-lg-6">
            <label class="form-label" style="font-size:.85rem">ตัวอย่างที่จะส่งเข้า Google Chat</label>
            <div class="wh-preview tpl-preview"></div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-3">
          <button class="btn btn-primary btn-sm btn-save"><i class="bi bi-save me-1"></i>บันทึก</button>
          <button class="btn btn-outline-secondary btn-sm btn-test"><i class="bi bi-send me-1"></i>ส่งทดสอบเข้ากลุ่ม</button>
          <span class="align-self-center text-muted" style="font-size:.8rem">
            ${t.update_at ? 'แก้ไขล่าสุด: ' + h(t.update_at) : ''}
          </span>
        </div>
      </div>`;
    }).join(''));
    $('.wh-card[data-id]').each(function(){ preview($(this)); });
  }, 'json');
}

function preview($c) {
  const men = $c.find('.tpl-mention').is(':checked') ? '<span class="men">@all </span>' : '';
  $c.find('.tpl-preview').html(
    men + '<span class="ttl">' + h(fill($c.find('.tpl-title').val())) + '</span>\n\n'
        + h(fill($c.find('.tpl-body').val()))
  );
}

// jQuery is loaded after this page is included, so bind once the DOM is ready.
document.addEventListener('DOMContentLoaded', function () {
$(document)
  .on('input change', '.tpl-title, .tpl-body, .tpl-mention', function(){ preview($(this).closest('.wh-card')); })
  .on('click', '.wh-vars code', function(){
    navigator.clipboard.writeText($(this).text());
    $(this).css('background', '#cfe2ff');
    setTimeout(() => $(this).css('background', ''), 400);
  })
  .on('click', '.btn-save', function(){
    const $c = $(this).closest('.wh-card'), $b = $(this);
    $b.prop('disabled', true);
    $.post('assets/php/actionManagerWebhook.php', {
      act:'save', id:$c.data('id'),
      title:$c.find('.tpl-title').val(), body:$c.find('.tpl-body').val(),
      mention_all:$c.find('.tpl-mention').is(':checked') ? 1 : 0,
      is_active:$c.find('.tpl-active').is(':checked') ? 1 : 0
    }, function(res){
      $b.prop('disabled', false);
      alert(res.status === 'ok' ? 'บันทึกเรียบร้อย' : 'บันทึกไม่สำเร็จ: ' + res.status);
      if (res.status === 'ok') loadList();
    }, 'json');
  })
  .on('click', '.btn-test', function(){
    const $c = $(this).closest('.wh-card'), $b = $(this);
    if (!confirm('ส่งข้อความทดสอบเข้ากลุ่ม Google Chat จริง — ยืนยันไหม?')) return;
    $b.prop('disabled', true);
    $.post('assets/php/actionManagerWebhook.php', { act:'test', id:$c.data('id') }, function(res){
      $b.prop('disabled', false);
      alert(res.status === 'ok' ? 'ส่งแล้ว ลองเช็กในกลุ่ม Website Down'
          : res.status === 'no_webhook' ? 'ยังไม่ได้ตั้งค่า webhook URL'
          : 'ส่งไม่สำเร็จ: ' + res.status);
    }, 'json');
  });

<?php if ($hasTable): ?>loadList();<?php endif; ?>
});
</script>
