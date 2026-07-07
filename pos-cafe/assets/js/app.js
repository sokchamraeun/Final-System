/* ============================================================
   pos-cafe — shared front-end helpers.
   The layout (components/layout/footer.php) already defines the
   core UI JS inline: toggleSidebar(), toggleTheme(), window.toast().
   Put page-agnostic helpers you want cached across pages here, and
   include with: <script src="<?= asset('js/app.js') ?>"></script>
   ============================================================ */

/* Post a form as fetch and return JSON (helper for API calls). */
window.postJson = function (url, data) {
  var body = new URLSearchParams(data).toString();
  return fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
    body: body,
  }).then(function (r) { return r.json(); });
};
