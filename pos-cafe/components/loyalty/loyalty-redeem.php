<?php
declare(strict_types=1);
/* Rewards list with redeem buttons.
   component('loyalty/loyalty-redeem', ['rewards' => $rewards, 'points' => $pts, 'loyaltyId' => $id]); */
$rewards   = $rewards   ?? [];
$points    = (int) ($points ?? 0);
$loyaltyId = $loyaltyId ?? '';

if (!$rewards) {
    component('common/empty-state', [
        'icon'    => 'fa-gift',
        'title'   => 'No rewards',
        'message' => 'There are no active rewards to redeem.',
    ]);
    return;
}
?>
<div class="grid gap-3 sm:grid-cols-2">
  <?php foreach ($rewards as $rw):
    $req = (int) ($rw['points_required'] ?? 0);
    $ok  = $points >= $req;
  ?>
  <div class="flex items-center justify-between gap-3 rounded-2xl border p-4 <?= $ok ? 'border-brand/40 bg-amber-50/60 dark:bg-slate-800/40' : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' ?>">
    <div class="min-w-0">
      <div class="truncate font-semibold text-slate-800 dark:text-white"><?= e($rw['reward_name'] ?? '') ?></div>
      <div class="text-xs text-slate-500"><?= number_format($req) ?> pts</div>
    </div>
    <?php if ($ok): ?>
    <button type="button"
            onclick="lrRedeem(this, <?= e(json_encode($loyaltyId)) ?>, <?= e(json_encode($rw['reward_name'] ?? '')) ?>)"
            class="shrink-0 rounded-xl bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">
      Redeem
    </button>
    <?php else: ?>
    <span class="shrink-0 rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-400 dark:bg-slate-800">Need <?= number_format($req - $points) ?></span>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<?php if (empty($GLOBALS['_lr_redeem_js'])): $GLOBALS['_lr_redeem_js'] = true; ?>
<script>
  function lrRedeem(btn, loyaltyId, rewardName) {
    btn.disabled = true; btn.textContent = '…';
    fetch(<?= json_encode(url('api/loyalty/redeem.php')) ?>, {
      method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'loyalty_id='  + encodeURIComponent(loyaltyId) +
            '&reward_name=' + encodeURIComponent(rewardName) +
            '&csrf_token='  + encodeURIComponent(<?= json_encode(csrf_token()) ?>)
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.success) { window.toast && window.toast(d.message || 'Failed', 'error'); btn.disabled = false; btn.textContent = 'Redeem'; return; }
      window.toast && window.toast(d.message || 'Reward added', 'success');
      btn.outerHTML = '<span class="shrink-0 rounded-xl bg-emerald-100 px-4 py-2 text-xs font-semibold text-emerald-700">Added ✓</span>';
    })
    .catch(function () { window.toast && window.toast('Request failed', 'error'); btn.disabled = false; btn.textContent = 'Redeem'; });
  }
</script>
<?php endif; ?>
