<?php
declare(strict_types=1);
/* Points balance + (admin) adjust form.
   component('loyalty/loyalty-points', ['card' => $card, 'canAdjust' => bool]); */
$card = $card ?? [];
if (!$card) return;
$canAdjust = $canAdjust ?? false;
$points = (int) ($card['points'] ?? 0);
$cardId = (int) ($card['card_id'] ?? 0);
?>
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <div class="flex items-center justify-between">
    <div>
      <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Points balance</div>
      <div class="mt-1 text-3xl font-extrabold text-brand tabular-nums" id="lpBalance"><?= number_format($points) ?></div>
    </div>
    <i class="fa-solid fa-coins text-3xl text-brand/30"></i>
  </div>

  <?php if ($canAdjust): ?>
  <form class="mt-4 space-y-3 border-t border-slate-100 pt-4 dark:border-slate-800" onsubmit="return lpAdjust(event)">
    <input type="hidden" id="lpCardId" value="<?= $cardId ?>">
    <div class="flex items-center gap-2">
      <input type="number" id="lpAmount" min="1" placeholder="Amount" required
             class="w-28 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <input type="text" id="lpReason" placeholder="Reason (optional)"
             class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>
    <div class="flex gap-2">
      <button type="submit" onclick="window._lpDir=1" class="flex-1 rounded-xl bg-emerald-500 py-2 text-sm font-semibold text-white hover:bg-emerald-600"><i class="fa-solid fa-plus"></i> Add</button>
      <button type="submit" onclick="window._lpDir=-1" class="flex-1 rounded-xl bg-red-500 py-2 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-minus"></i> Deduct</button>
    </div>
  </form>
  <?php endif; ?>
</div>

<?php if ($canAdjust && empty($GLOBALS['_lp_adjust_js'])): $GLOBALS['_lp_adjust_js'] = true; ?>
<script>
  window._lpDir = 1;
  function lpAdjust(ev) {
    ev.preventDefault();
    var amount = parseInt(document.getElementById('lpAmount').value) || 0;
    if (amount <= 0) { window.toast && window.toast('Enter a valid amount', 'error'); return false; }
    var adjustment = (window._lpDir || 1) * amount;
    var body = 'card_id='     + encodeURIComponent(document.getElementById('lpCardId').value) +
               '&adjustment=' + adjustment +
               '&reason='     + encodeURIComponent(document.getElementById('lpReason').value) +
               '&csrf_token=' + encodeURIComponent(<?= json_encode(csrf_token()) ?>);
    fetch(<?= json_encode(url('api/loyalty/adjust-points.php')) ?>, {
      method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.success) { window.toast && window.toast(d.message || 'Failed', 'error'); return; }
      document.getElementById('lpBalance').textContent = Number(d.new_points).toLocaleString();
      window.toast && window.toast('Points updated', 'success');
      document.getElementById('lpAmount').value = '';
      document.getElementById('lpReason').value = '';
    })
    .catch(function () { window.toast && window.toast('Request failed', 'error'); });
    return false;
  }
</script>
<?php endif; ?>
