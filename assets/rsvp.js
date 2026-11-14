/* Ma3zoom / Wisal RSVP handler.
   Supports both:
   1) Local backend API (api/save_rsvp.php)
   2) Browser local storage sync for independent admin dashboard (admin.html)
   3) Optional Google Sheets endpoint (if provided in data-sheet) */
(function () {
  var tag = document.currentScript;
  var sheetEndpoint = (tag && tag.dataset.sheet || '').trim();

  window.ma3zoomRSVP = function (data) {
    var record = {
      id: Date.now().toString(),
      date: new Date().toISOString(),
      name: (data.name || '').trim(),
      status: data.attending === 'yes' ? 'attending' : 'declined',
      guests: data.attending === 'yes' ? Math.max(1, parseInt(data.guests || 1, 10)) : 0,
      message: (data.message || '').trim()
    };

    // 1. Save locally in localStorage (always available)
    try {
      var stored = JSON.parse(localStorage.getItem('wisal_rsvp_responses') || '[]');
      stored.unshift(record);
      localStorage.setItem('wisal_rsvp_responses', JSON.stringify(stored));
      // Notify other open tabs/windows
      if ('BroadcastChannel' in window) {
        var bc = new BroadcastChannel('wisal_rsvp_channel');
        bc.postMessage({ type: 'new_rsvp', record: record });
      }
    } catch (err) {
      console.warn('[RSVP] LocalStorage error:', err);
    }

    // 2. Post to our local PHP API if hosted on server
    try {
      fetch('api/save_rsvp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(record)
      }).catch(function () {});
    } catch (err) {}

    // 3. Post to Google Sheet endpoint if provided
    if (sheetEndpoint) {
      var qs = new URLSearchParams(location.search);
      data.guestLink = qs.get('to') || '';
      data.lang = document.documentElement.lang || 'ar';
      data.page = location.pathname;
      return fetch(sheetEndpoint, { method: 'POST', mode: 'no-cors', body: new URLSearchParams(data) })
        .then(function () { return true; })
        .catch(function () { return true; });
    }

    return Promise.resolve(true);
  };
})();
