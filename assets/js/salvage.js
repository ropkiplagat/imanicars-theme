/* ============================================================
   SALVAGE BOARD — /insurance
   Vanilla, no build step, no external dependency.

   The browser NEVER calculates a fee. It sends the hammer price to the
   server and renders the formatted strings that come back. A percentage
   multiplied in JavaScript floats and one multiplied in PHP integer cents
   would eventually disagree, and the figure on screen is the one Rop bids
   to.

   All injection is via textContent. Nothing here builds HTML from data.
   ============================================================ */
(function () {
  'use strict';

  if (typeof ICSalvage === 'undefined') { return; }
  var S = ICSalvage.strings || {};

  function post(action, fields) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', ICSalvage.nonce);
    Object.keys(fields).forEach(function (k) {
      if (fields[k] !== null && fields[k] !== undefined) { body.set(k, fields[k]); }
    });
    return fetch(ICSalvage.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    }).then(function (r) { return r.json(); });
  }

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) { n.className = cls; }
    if (text !== undefined && text !== null) { n.textContent = text; }
    return n;
  }

  function clear(node) { while (node.firstChild) { node.removeChild(node.firstChild); } }

  /* ----------------------------------------------------------
     Live fee quote as a hammer price is typed
     ---------------------------------------------------------- */
  function renderQuote(box, data) {
    clear(box);

    if (!data.ok) {
      box.appendChild(el('span', 'sb-price__notfound', data.message || S.failed));
      return;
    }

    if (data.landed_before_duty) {
      box.appendChild(el('span', 'sb-q-landed', data.landed_before_duty));
      box.appendChild(el('span', 'sb-price__qualifier', 'all-in, before duty'));
      if (data.buyer_fee) {
        box.appendChild(el('span', 'sb-q-fee sb-cell-note', 'buyer fee ' + data.buyer_fee));
      }
    } else {
      box.appendChild(el('span', 'sb-price__notfound', 'Buyer fee: ' + (S.notFound || 'not found')));
    }

    // Caveats are rendered as visible text beside the number, never hidden
    // behind a tooltip. A partial fee that looks complete is the failure mode.
    if (data.fee_partial && data.caveats && data.caveats.length) {
      box.appendChild(el('div', 'sb-cell-warn', data.caveats[0]));
    }
    if (!data.landed_before_duty && data.unavailable_reason) {
      box.appendChild(el('div', 'sb-cell-note', data.unavailable_reason));
    }
  }

  var timers = {};
  document.querySelectorAll('[data-sb-form]').forEach(function (form) {
    var lot    = form.getAttribute('data-lot');
    var source = form.getAttribute('data-source');
    var input  = form.querySelector('[data-sb-hammer]');
    var box    = form.querySelector('[data-sb-quote]');
    if (!input || !box) { return; }

    input.addEventListener('input', function () {
      clearTimeout(timers[lot]);
      var raw = input.value.trim();
      if (raw === '') { return; }
      timers[lot] = setTimeout(function () {
        post('ic_salvage_quote', { source: source, hammer: raw })
          .then(function (res) {
            if (res && res.success) { renderQuote(box, res.data); }
          })
          .catch(function () { /* leave the last good quote in place */ });
      }, 250);
    });
  });

  /* ----------------------------------------------------------
     Save an observation
     ---------------------------------------------------------- */
  document.querySelectorAll('[data-sb-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var lot  = form.getAttribute('data-lot');
      var msg  = form.querySelector('[data-sb-msg]');
      var btn  = form.querySelector('button[type="submit"]');
      var data = {
        lot_id: lot,
        status: (form.querySelector('[name="status"]') || {}).value || '',
        hammer: (form.querySelector('[name="hammer"]') || {}).value || '',
        notes:  (form.querySelector('[name="notes"]') || {}).value || ''
      };

      msg.className = 'sb-entry__msg';
      msg.textContent = S.saving || 'Saving…';
      if (btn) { btn.disabled = true; }

      post('ic_salvage_save_observation', data)
        .then(function (res) {
          if (!res || !res.success) {
            msg.className = 'sb-entry__msg sb-entry__msg--bad';
            msg.textContent = (res && res.data && res.data.message) ? res.data.message : (S.failed || 'Not saved');
            return;
          }
          var d = res.data;
          msg.className = 'sb-entry__msg sb-entry__msg--ok';
          msg.textContent = (S.saved || 'Recorded') + ' ' + d.observed_on;

          // Repaint the row's price cell from the SERVER's strings.
          var price = document.querySelector('[data-sb-price-for="' + lot + '"]');
          if (price) {
            clear(price);
            if (d.hammer) { price.appendChild(el('div', 'sb-price__hammer', d.hammer)); }
            if (d.landed_before_duty) {
              var landed = el('div', 'sb-price__landed', d.landed_before_duty);
              landed.appendChild(el('span', 'sb-price__qualifier', 'all-in, before duty'));
              price.appendChild(landed);
              if (d.fee_partial && d.caveats && d.caveats.length) {
                price.appendChild(el('div', 'sb-cell-warn', d.caveats[0]));
              }
            } else if (d.hammer) {
              price.appendChild(el('div', 'sb-price__notfound', S.notFound || 'not found'));
              if (d.unavailable_reason) {
                price.appendChild(el('div', 'sb-cell-note', d.unavailable_reason));
              }
            } else {
              price.appendChild(el('div', 'sb-price__none', 'no price recorded'));
            }
          }

          var st = document.querySelector('[data-sb-status-for="' + lot + '"]');
          if (st && d.status) {
            st.textContent = d.status;
            st.className = 'sb-status sb-status--' + d.status.toLowerCase().replace(/\s+/g, '');
          }
        })
        .catch(function () {
          msg.className = 'sb-entry__msg sb-entry__msg--bad';
          msg.textContent = S.failed || 'Not saved';
        })
        .finally(function () { if (btn) { btn.disabled = false; } });
    });
  });

  /* ----------------------------------------------------------
     Copy / print / email
     ---------------------------------------------------------- */
  var statusEl = document.querySelector('[data-sb-status]');
  function say(text) { if (statusEl) { statusEl.textContent = text; } }

  var copyBtn = document.querySelector('[data-sb-copy]');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      var table = document.getElementById('sb-board');
      if (!table) { return; }

      var lines = [];
      table.querySelectorAll('thead th').forEach(function () {});
      var head = [];
      table.querySelectorAll('thead th').forEach(function (th) { head.push(th.textContent.trim()); });
      lines.push(head.join('\t'));

      table.querySelectorAll('tbody tr').forEach(function (tr) {
        var cells = [];
        tr.querySelectorAll('td').forEach(function (td) {
          var c = td.cloneNode(true);
          // Drop the entry form: it is a control, not data.
          c.querySelectorAll('.sb-entry').forEach(function (n) { n.remove(); });
          cells.push(c.textContent.replace(/\s+/g, ' ').trim());
        });
        lines.push(cells.join('\t'));
      });

      var text = lines.join('\n');
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text)
          .then(function () { say(S.copied || 'Copied'); })
          .catch(function () { say(S.copyFail || 'Could not copy'); });
      } else {
        say(S.copyFail || 'Could not copy');
      }
    });
  }

  var printBtn = document.querySelector('[data-sb-print]');
  if (printBtn) {
    printBtn.addEventListener('click', function () { window.print(); });
  }

  var emailLink = document.getElementById('sb-email');
  if (emailLink) {
    emailLink.addEventListener('click', function (e) {
      e.preventDefault();
      var table = document.getElementById('sb-board');
      var rows  = table ? table.querySelectorAll('tbody tr') : [];
      var lede  = document.querySelector('.sb-lede__num');

      var body = [];
      body.push('Imani salvage board — ' + new Date().toISOString().slice(0, 10));
      body.push('');
      if (lede) { body.push(lede.textContent.trim() + ' lots sell in the next 48 hours.'); }
      body.push(rows.length + ' lots in the current view.');
      body.push('');

      var limit = Math.min(rows.length, 20);
      for (var i = 0; i < limit; i++) {
        var tds = rows[i].querySelectorAll('td');
        if (tds.length < 5) { continue; }
        var sale    = tds[0].textContent.replace(/\s+/g, ' ').trim();
        var house   = tds[1].textContent.replace(/\s+/g, ' ').trim();
        var stock   = tds[2].textContent.replace(/\s+/g, ' ').trim();
        var vehicle = tds[3].textContent.replace(/\s+/g, ' ').trim();
        body.push('- ' + vehicle + ' | ' + house + ' ' + stock + ' | ' + sale);
      }
      if (rows.length > limit) {
        body.push('... and ' + (rows.length - limit) + ' more. Use Export to Excel for the full table.');
      }
      body.push('');
      body.push('All costs are BEFORE Kenyan duty. Duty is assessed on KRA CRSP, not on the price paid.');
      body.push('Manheim buyer fees are not published and show as "not found".');

      var href = 'mailto:?subject=' + encodeURIComponent('Imani salvage board — ' + new Date().toISOString().slice(0, 10)) +
                 '&body=' + encodeURIComponent(body.join('\n'));
      window.location.href = href;
    });
  }
}());
