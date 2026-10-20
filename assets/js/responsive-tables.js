/*
 * Compact table view for phones.
 *
 * A 9-30 column table cannot be read on a 360px screen, but turning every row
 * into a stack of labels makes the list impossible to scan. Following the
 * reference design the table stays a table: only the first few meaningful
 * columns are shown, and tapping a row reveals the rest underneath it.
 *
 * Column choice is automatic so pages do not have to be edited one by one:
 *
 *   - columns whose header is empty are dropped (action/checkbox columns)
 *   - columns whose cells hold only an icon are dropped
 *   - of what is left, the first MAX_COLS are shown; the first of those is
 *     marked .mobile-primary and carries the row's identity
 *
 * Most tables here are filled by DataTables over ajax and ship no <tbody>
 * rows in the HTML at all, so running once on DOMContentLoaded would find an
 * empty table. A MutationObserver re-runs whenever rows are inserted, which
 * covers ajax loading, paging, searching and sorting without needing to know
 * how each page initialises its table.
 */
(function () {
  'use strict';

  var PHONE = 767.98;
  // Every column is shown: the table scrolls sideways inside its wrapper
  // instead of hiding what does not fit. Kept as a constant so the column
  // sizing below has one place to read from.
  var SHOW_ALL = true;

  function isPhone() {
    return window.innerWidth <= PHONE;
  }

  function headerLabels(table) {
    // Prefer the last header row: grouped headers put the real column names
    // there, and a single-row thead is unaffected.
    var head = table.tHead;
    if (!head || !head.rows.length) return null;
    var cells = head.rows[head.rows.length - 1].cells;
    var out = [];
    for (var i = 0; i < cells.length; i++) {
      out.push((cells[i].textContent || '').replace(/\s+/g, ' ').trim());
    }
    return out;
  }

  function firstDataRow(table) {
    for (var b = 0; b < table.tBodies.length; b++) {
      var rows = table.tBodies[b].rows;
      for (var r = 0; r < rows.length; r++) {
        var row = rows[r];
        if (row.classList.contains('mobile-detail')) continue;
        // DataTables' "no data available" placeholder is one full-width cell.
        if (row.cells.length === 1 && row.cells[0].hasAttribute('colspan')) continue;
        return row;
      }
    }
    return null;
  }

  // A cell whose visible content is an icon or a lone control carries no text
  // worth one of the few columns a phone has room for.
  function isIconCell(cell) {
    if (!cell) return false;
    var text = (cell.textContent || '').replace(/\s+/g, '').length;
    if (text > 2) return false;
    return cell.querySelector('svg, i, img, .fa, .bi, button, a, input') !== null;
  }

  // Every column is kept: the wrapper scrolls sideways rather than dropping
  // anything, so action and icon columns stay reachable too.
  function pickColumns(table, labels) {
    var keep = [];
    for (var i = 0; i < labels.length; i++) keep.push(i);
    return keep;
  }

  /* The detail moves the real cell nodes rather than copying their HTML.
     Copying would duplicate ids: the copy-to-clipboard controls on the
     password page each carry a hidden <input id="..."> holding the value, and
     a second element with the same id would make the copy button read the
     wrong one. Moving keeps ids unique, and inline onclick handlers with
     them; putRowBack() restores every node when the detail closes. */
  function detailRow(row, labels, shown) {
    var list = document.createElement('div');
    list.className = 'mobile-detail-list';

    for (var i = 0; i < row.cells.length && i < labels.length; i++) {
      var cell = row.cells[i];

      // Columns already visible in the row above do not need repeating.
      if (labels[i] && shown.indexOf(i) !== -1) continue;
      if (!cell.textContent.trim() && !cell.querySelector('*')) continue;

      var item = document.createElement('div');
      item.className = 'mobile-detail-item' + (labels[i] ? '' : ' is-actions');

      if (labels[i]) {
        var lab = document.createElement('span');
        lab.className = 'mobile-detail-label';
        lab.textContent = labels[i];
        item.appendChild(lab);
      }

      var val = document.createElement('span');
      val.className = 'mobile-detail-value';
      val.setAttribute('data-col', i);
      while (cell.firstChild) val.appendChild(cell.firstChild);
      item.appendChild(val);

      list.appendChild(item);
    }

    var tr = document.createElement('tr');
    tr.className = 'mobile-detail';
    var td = document.createElement('td');
    td.colSpan = shown.length;
    td.appendChild(list);
    tr.appendChild(td);
    return tr;
  }

  // Return the moved nodes to the cells they came from.
  function putRowBack(row, detail) {
    var vals = detail.querySelectorAll('.mobile-detail-value[data-col]');
    for (var i = 0; i < vals.length; i++) {
      var cell = row.cells[parseInt(vals[i].getAttribute('data-col'), 10)];
      if (!cell) continue;
      while (vals[i].firstChild) cell.appendChild(vals[i].firstChild);
    }
  }

  function toggle(row, labels, shown) {
    var next = row.nextElementSibling;

    if (next && next.classList.contains('mobile-detail')) {
      putRowBack(row, next);
      next.parentNode.removeChild(next);
      row.classList.remove('is-open');
      return;
    }

    // Only one row open at a time keeps the list short.
    var open = row.parentNode.querySelectorAll('tr.is-open');
    for (var i = 0; i < open.length; i++) {
      var d = open[i].nextElementSibling;
      if (d && d.classList.contains('mobile-detail')) {
        putRowBack(open[i], d);
        d.parentNode.removeChild(d);
      }
      open[i].classList.remove('is-open');
    }

    row.parentNode.insertBefore(detailRow(row, labels, shown), row.nextSibling);
    row.classList.add('is-open');
  }

  function markCell(cell, hidden, primary) {
    cell.classList.toggle('mobile-hidden', hidden);
    cell.classList.toggle('mobile-primary', primary);
  }

  function process(table) {
    var labels = headerLabels(table);
    if (!labels) return;

    var shown = pickColumns(table, labels);
    if (!shown.length) return;

    var head = table.tHead.rows[table.tHead.rows.length - 1];
    var i;

    // DataTables builds a <colgroup> and sizes the table through it. Those
    // <col> widths beat any width set on <th>, so the visible columns were
    // being squeezed to the share the hidden ones still claimed. Give each
    // <col> the same treatment as its header cell.
    var cg = table.querySelector('colgroup');
    if (cg) {
      var cols = cg.children;
      for (i = 0; i < cols.length; i++) {
        var vis = shown.indexOf(i);
        if (vis === -1) {
          cols[i].style.width = '0';
          cols[i].style.visibility = 'collapse';
        } else {
          // Let content decide each column's width; the wrapper scrolls.
          cols[i].style.visibility = '';
          cols[i].style.width = '';
        }
      }
    }

    for (i = 0; i < head.cells.length; i++) {
      markCell(head.cells[i], shown.indexOf(i) === -1, false);
      // Position among the visible columns, so CSS can size them without
      // relying on nth-of-type, which counts hidden cells too.
      var hp = shown.indexOf(i);
      if (hp === -1) head.cells[i].removeAttribute('data-mobile-col');
      else head.cells[i].setAttribute('data-mobile-col', hp);
    }


    for (var b = 0; b < table.tBodies.length; b++) {
      var rows = table.tBodies[b].rows;

      for (var r = 0; r < rows.length; r++) {
        var row = rows[r];
        if (row.classList.contains('mobile-detail')) continue;

        var cells = row.cells;
        if (cells.length === 1 && cells[0].hasAttribute('colspan')) continue;

        for (i = 0; i < cells.length && i < labels.length; i++) {
          markCell(cells[i], shown.indexOf(i) === -1, i === shown[0]);

          var pos = shown.indexOf(i);
          if (pos === -1) cells[i].removeAttribute('data-mobile-col');
          else cells[i].setAttribute('data-mobile-col', pos);

        }

        // The click handler is bound once per row. Adding a real cell for
        // the chevron would give the row more cells than DataTables knows
        // about, and its next redraw would abort with "Requested unknown
        // parameter". The chevron is drawn by CSS on the last visible cell
        // instead, so the DOM keeps exactly the columns DataTables built.
        if (!row.hasAttribute('data-mobile-bound')) {
          row.setAttribute('data-mobile-bound', '1');

          (function (r2, l2, s2) {
            r2.addEventListener('click', function (e) {
              // Let the row's own links and buttons work normally.
              if (e.target.closest('a, button, input, select, label')) return;
              toggle(r2, l2, s2);
            });
          })(row, labels, shown);
        }
      }
    }
  }

  // Close every open detail, returning its moved nodes first. DataTables
  // replaces the whole tbody on redraw, which would otherwise discard cell
  // contents that are currently living inside a detail row.
  function closeAll() {
    var open = document.querySelectorAll('tr.is-open');
    for (var i = 0; i < open.length; i++) {
      var d = open[i].nextElementSibling;
      if (d && d.classList.contains('mobile-detail')) {
        putRowBack(open[i], d);
        d.parentNode.removeChild(d);
      }
      open[i].classList.remove('is-open');
    }
  }

  function apply() {
    if (!isPhone()) return;
    closeAll();
    var list = document.querySelectorAll('table.table, table.dataTable');
    for (var i = 0; i < list.length; i++) process(list[i]);
  }

  // Returning to a wide screen must leave the table as the page built it.
  function restore() {
    closeAll();
    var marked = document.querySelectorAll('.mobile-hidden, .mobile-primary');
    for (var i = 0; i < marked.length; i++) {
      marked[i].classList.remove('mobile-hidden', 'mobile-primary');
    }
    var bound = document.querySelectorAll('[data-mobile-bound]');
    for (var j = 0; j < bound.length; j++) {
      bound[j].removeAttribute('data-mobile-bound');
    }

    var cols = document.querySelectorAll('[data-mobile-col]');
    for (var m = 0; m < cols.length; m++) {
      cols[m].removeAttribute('data-mobile-col');
    }

    var wraps = document.querySelectorAll('.mobile-clamp');
    for (var k = 0; k < wraps.length; k++) {
      var w = wraps[k];
      while (w.firstChild) w.parentNode.insertBefore(w.firstChild, w);
      w.parentNode.removeChild(w);
    }
  }

  // Re-run when rows appear, which is how every ajax-backed table here gets
  // its content. Batched through rAF so redrawing 50 rows costs one pass.
  var queued = false;
  function schedule() {
    if (queued || !isPhone()) return;
    queued = true;
    window.requestAnimationFrame(function () {
      queued = false;
      apply();
    });
  }

  function init() {
    apply();

    // A table whose rows arrive after the observer's first pass (ajax that
    // resolves between the initial apply and the observer attaching) would
    // keep unprocessed rows. Re-run a few times over the first second, which
    // is cheap: process() exits immediately once a table is already done.
    var tries = 0;
    var settle = setInterval(function () {
      if (++tries > 6 || !isPhone()) { clearInterval(settle); return; }
      apply();
    }, 250);

    // DataTables replaces the tbody on every redraw. Detail rows are removed
    // before that happens, so their moved cell contents go back to the cells
    // they belong to rather than being discarded with the old tbody.
    if (window.jQuery) {
      jQuery(document).on('preDraw.dt', function () { closeAll(); });
    }

    if (!window.MutationObserver) return;
    new MutationObserver(function (records) {
      for (var i = 0; i < records.length; i++) {
        var added = records[i].addedNodes;
        for (var j = 0; j < added.length; j++) {
          // Ignore the detail rows this script inserts itself.
          if (added[j].nodeType === 1 && added[j].classList &&
              added[j].classList.contains('mobile-detail')) continue;
          schedule();
          return;
        }
      }
    }).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Rotating the phone can cross the breakpoint in either direction.
  var resizeTimer;
  var wasPhone = isPhone();
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var now = isPhone();
      if (now) apply();
      else if (wasPhone) restore();
      wasPhone = now;
    }, 150);
  });
})();
