<?php declare(strict_types=1);
/* ── Manager dashboard header: title + live clock (matches design). ──
   Expects: $admin_name ── */
$admin_name = $admin_name ?? 'Admin';
?>
<div class="mdash-head">
  <div>
    <h1 class="mdash-title">Dashboard</h1>
    <p class="mdash-sub">Welcome back! Here's what's happening today.</p>
  </div>
  <div class="mdash-clock">
    <div class="ic"><i class="fa-regular fa-clock"></i></div>
    <div>
      <div class="t" id="mdashClockTime"><?= date('h:i:s A') ?></div>
      <div class="d"><?= date('l, F j, Y') ?></div>
    </div>
  </div>
</div>
<script>
(function(){
  var el = document.getElementById('mdashClockTime');
  if (!el) return;
  if (window.__mdashClock) clearInterval(window.__mdashClock);
  window.__mdashClock = setInterval(function(){
    if (!document.body.contains(el)) { clearInterval(window.__mdashClock); return; }
    var d = new Date(), h = d.getHours(), ap = h < 12 ? 'AM' : 'PM';
    h = h % 12 || 12;
    var p = function(n){ return (n < 10 ? '0' : '') + n; };
    el.textContent = p(h) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds()) + ' ' + ap;
  }, 1000);
})();
</script>
