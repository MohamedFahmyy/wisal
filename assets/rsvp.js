/* Ma3zoom RSVP sender.
   Each invitation page loads this file with its own Google Sheet endpoint:
     <script src="rsvp.js" data-sheet="https://script.google.com/macros/s/XXXX/exec"></script>
   With an empty data-sheet the page stays in demo mode (shows the thank-you only). */
(function () {
  var tag = document.currentScript;
  var endpoint = (tag && tag.dataset.sheet || '').trim();

  window.ma3zoomRSVP = function (data) {
    if (!endpoint) return Promise.resolve(false);
    var qs = new URLSearchParams(location.search);
    data.guestLink = qs.get('to') || '';
    data.lang = document.documentElement.lang || '';
    data.page = location.pathname;
    // Apps Script web apps don't send CORS headers, so post "no-cors":
    // the row is written, the page just can't read the response.
    return fetch(endpoint, { method: 'POST', mode: 'no-cors', body: new URLSearchParams(data) })
      .then(function () { return true; })
      .catch(function () { return false; });
  };
})();
