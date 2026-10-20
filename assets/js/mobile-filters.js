/*
 * Mobile filter sheet and sticky "Add new" bar.
 *
 * On a phone the filter chrome dwarfs the data it filters: signupLogs spends
 * 474px of a 740px screen on date pickers and selects before a single row is
 * visible. Below the phone breakpoint that chrome is collected into a sheet
 * that opens from a filter button in a compact toolbar, and any page-level
 * "Add new" button is moved to a sticky bar at the bottom of the screen.
 *
 * Nothing is cloned: the original controls are MOVED into the sheet and moved
 * back when the viewport grows again. Cloning would duplicate element ids and
 * break the pages' own jQuery handlers, which bind by id.
 */
(function () {
  'use strict';

  var PHONE = 767.98;
  var built = false;

  function isPhone() { return window.innerWidth <= PHONE; }

  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  }

  // The block a page uses for its filters. Pages disagree on where it lives:
  // .card-header on some, .card-body on others, a bare <div> on websiteList.
  // Rather than list every shape, find the controls first and then climb to
  // the smallest container that holds them all without swallowing the table.
  function filterBlock() {
    var scope = document.querySelector('.content-wrapper');
    if (!scope) return null;

    var table = document.querySelector('table.table, table.dataTable');

    // Page-level filter controls: not the table's own search/length widgets,
    // not anything already relocated, not inside a modal.
    var ctrls = [];
    scope.querySelectorAll('select, input').forEach(function (c) {
      if (c.closest('#mfSheet, #mfBar, .modal, .dataTables_wrapper, .dt-container')) return;
      if (table && table.contains(c)) return;
      if (c.type === 'hidden') return;
      // l4uPassword parks an off-screen <input> next to each copy button to
      // hold the value being copied. Those are not filters, and treating
      // them as such would drag half the page into the sheet.
      var r = c.getBoundingClientRect();
      if (r.width === 0 || r.height === 0 || r.right < 0 || r.bottom < 0) return;
      ctrls.push(c);
    });
    if (!ctrls.length) return null;

    // Lowest common ancestor of those controls.
    var node = ctrls[0];
    for (var i = 1; i < ctrls.length; i++) {
      while (node && !node.contains(ctrls[i])) node = node.parentElement;
      if (!node) return null;
    }

    // Climb out of a bare wrapper so the block's own heading comes along,
    // but never far enough to take the table with it.
    while (node && node !== scope) {
      var up = node.parentElement;
      if (!up || up === scope) break;
      if (table && up.contains(table)) break;
      if (up.querySelector('.dataTables_wrapper, .dt-container')) break;
      node = up;
    }

    if (!node || node === scope) return null;
    if (table && node.contains(table)) return null;
    return node;
  }

  // The blocks that make up a page's filter chrome, in the order they appear.
  function chromeParts() {
    var parts = [];
    var header = filterBlock();
    if (header) parts.push(header);
    // A page with no filters of its own still benefits from the toolbar: its
    // DataTables length/search controls are collected below either way.

    // DataTables 1.x names these .dataTables_length/.dataTables_filter; 2.x
    // uses .dt-length/.dt-search. Both appear across these pages.
    var len = document.querySelector('.dataTables_length, .dt-length');
    var flt = document.querySelector('.dataTables_filter, .dt-search');
    if (len) parts.push(len.closest('.col-sm-12, .col-md-6, .dt-layout-cell') || len);
    if (flt) parts.push(flt.closest('.col-sm-12, .col-md-6, .dt-layout-cell') || flt);

    return parts.filter(function (p, i, a) { return p && a.indexOf(p) === i; });
  }

  function countActive() {
    var n = 0;
    document.querySelectorAll('#mfSheetBody input, #mfSheetBody select').forEach(function (c) {
      if (c.type === 'search' || c.type === 'button' || c.type === 'submit') return;
      if (c.tagName === 'SELECT') {
        // A select sitting on its first option is not a narrowing choice.
        if (c.selectedIndex > 0) n++;
      } else if (c.value && c.value.trim() !== '') n++;
    });
    return n;
  }

  function syncBadge() {
    var b = document.getElementById('mfCount');
    if (!b) return;
    var n = countActive();
    b.textContent = n;
    b.hidden = n === 0;
  }

  function build() {
    if (built || !isPhone()) return;

    var parts = chromeParts();
    var addBtn = document.getElementById('btnModal');
    if (!parts.length && !addBtn) return;

    var anchor = document.querySelector('.content-wrapper .content .container-fluid')
              || document.querySelector('.content-wrapper');
    if (!anchor) return;

    built = true;

    // --- Toolbar: search stays visible, filters hide behind a button -------
    var bar = el('div', 'mf-bar');
    bar.id = 'mfBar';

    var searchWrap = el('div', 'mf-search');
    searchWrap.id = 'mfSearchSlot';
    bar.appendChild(searchWrap);

    if (parts.length) {
      var toggle = el('button', 'mf-toggle',
        '<i class="bi bi-funnel"></i><span>Filter</span>' +
        '<span class="mf-count" id="mfCount" hidden>0</span>');
      toggle.type = 'button';
      toggle.setAttribute('aria-expanded', 'false');
      toggle.addEventListener('click', openSheet);
      bar.appendChild(toggle);
    }

    // --- Sheet ------------------------------------------------------------
    var overlay = el('div', 'mf-overlay');
    overlay.id = 'mfOverlay';
    overlay.addEventListener('click', closeSheet);

    var sheet = el('div', 'mf-sheet');
    sheet.id = 'mfSheet';
    sheet.setAttribute('role', 'dialog');
    sheet.setAttribute('aria-label', 'Filters');

    var head = el('div', 'mf-sheet-head',
      '<span class="mf-sheet-title">Filters</span>');
    var close = el('button', 'mf-sheet-close', '<i class="bi bi-x-lg"></i>');
    close.type = 'button';
    close.setAttribute('aria-label', 'Close filters');
    close.addEventListener('click', closeSheet);
    head.appendChild(close);

    var body = el('div', 'mf-sheet-body');
    body.id = 'mfSheetBody';

    var foot = el('div', 'mf-sheet-foot');
    var done = el('button', 'btn btn-primary mf-done', 'Done');
    done.type = 'button';
    done.addEventListener('click', closeSheet);
    foot.appendChild(done);

    sheet.appendChild(head);
    sheet.appendChild(body);
    sheet.appendChild(foot);

    // Move the real controls in, remembering where each came from.
    parts.forEach(function (p) {
      var mark = el('span', 'mf-placeholder');
      mark.hidden = true;
      p.parentNode.insertBefore(mark, p);
      p.setAttribute('data-mf-moved', '1');
      body.appendChild(p);
    });

    // The DataTables search box is the one control worth keeping on screen.
    var searchInput = body.querySelector('.dataTables_filter input, .dt-search input');
    if (searchInput) {
      searchInput.setAttribute('data-mf-search', '1');
      var lbl = searchInput.closest('label');
      var slotMark = el('span', 'mf-placeholder');
      slotMark.hidden = true;
      (lbl || searchInput).parentNode.insertBefore(slotMark, lbl || searchInput);
      searchWrap.appendChild(searchInput);
      searchInput.setAttribute('placeholder', 'Search');
    } else {
      searchWrap.remove();
    }

    anchor.insertBefore(bar, anchor.firstChild);
    document.body.appendChild(overlay);
    document.body.appendChild(sheet);

    // --- Sticky add bar ---------------------------------------------------
    if (addBtn && !/digit|meaning/i.test(addBtn.textContent)) {
      var addMark = el('span', 'mf-placeholder');
      addMark.hidden = true;
      addBtn.parentNode.insertBefore(addMark, addBtn);
      addBtn.setAttribute('data-mf-moved', '1');

      var addBar = el('div', 'mf-addbar');
      addBar.id = 'mfAddBar';
      addBar.appendChild(addBtn);
      document.body.appendChild(addBar);
      document.body.classList.add('mf-has-addbar');
    }

    // Tag the shapes the stylesheet needs to treat specially. CSS :has()
    // covers this in current browsers, but tagging keeps the layout correct
    // where it is unsupported, and makes the intent visible in the DOM.
    body.querySelectorAll('label').forEach(function (l) {
      if (l.querySelector('select, input:not([type="checkbox"]):not([type="radio"])')) {
        l.classList.add('mf-inline-label');
      }
    });

    // A "Search:" label whose input was moved to the toolbar labels nothing.
    body.querySelectorAll('label').forEach(function (l) {
      if (!l.querySelector('input, select') && /^\s*search\s*:?\s*$/i.test(l.textContent)) {
        l.classList.add('mf-orphan-label');
      }
    });

    body.addEventListener('change', syncBadge);
    body.addEventListener('input', syncBadge);
    syncBadge();
  }

  function openSheet() {
    document.getElementById('mfSheet').classList.add('is-open');
    document.getElementById('mfOverlay').classList.add('is-open');
    document.querySelector('.mf-toggle').setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeSheet() {
    var s = document.getElementById('mfSheet');
    if (!s) return;
    s.classList.remove('is-open');
    document.getElementById('mfOverlay').classList.remove('is-open');
    var t = document.querySelector('.mf-toggle');
    if (t) t.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    syncBadge();
  }

  // Put every moved node back where it was, so the desktop layout is intact.
  function teardown() {
    if (!built) return;
    built = false;

    var marks = document.querySelectorAll('.mf-placeholder');
    var moved = document.querySelectorAll('[data-mf-moved], [data-mf-search]');

    for (var i = 0; i < marks.length && i < moved.length; i++) {
      marks[i].parentNode.insertBefore(moved[i], marks[i]);
      moved[i].removeAttribute('data-mf-moved');
      moved[i].removeAttribute('data-mf-search');
    }
    marks.forEach(function (m) { m.remove(); });

    ['mfBar', 'mfSheet', 'mfOverlay', 'mfAddBar'].forEach(function (id) {
      var n = document.getElementById(id);
      if (n) n.remove();
    });
    document.body.classList.remove('mf-has-addbar');
    document.body.style.overflow = '';
  }

  function init() {
    if (isPhone()) build();

    // DataTables creates its length/search controls when the table draws,
    // which for an ajax-backed table can land either before or after this
    // runs. A MutationObserver alone misses the "already drawn" case, since
    // no further mutation follows; polling alone loses a slow response. Do
    // both, and stop as soon as the toolbar exists.
    if (!built) {
      var obs = null;
      var poll = null;

      var stop = function () {
        if (obs) obs.disconnect();
        if (poll) clearInterval(poll);
      };

      var attempt = function () {
        if (!isPhone()) return;
        build();
        if (built) stop();
      };

      if (window.MutationObserver) {
        obs = new MutationObserver(attempt);
        obs.observe(document.body, { childList: true, subtree: true });
      }

      poll = setInterval(attempt, 400);
      setTimeout(stop, 15000);
    }

    var timer;
    window.addEventListener('resize', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        if (isPhone()) build(); else teardown();
      }, 150);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeSheet();
    });
  }

  // The DataTables controls this reparents do not exist until the table is
  // initialised, which happens on DOM ready in main.php and in the pages.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 350); });
  } else {
    setTimeout(init, 350);
  }
})();
