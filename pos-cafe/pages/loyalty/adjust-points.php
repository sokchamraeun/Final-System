<?php
declare(strict_types=1);
/* Loyalty — manual points adjustment (from loyalty_adjust_points.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';

$loyaltyId = trim((string) input('id', input('loyalty_id', '')));
$l    = new Loyalty();
$card = $loyaltyId !== '' ? $l->findByLoyaltyId($loyaltyId) : null;
$found = $card && (int) ($card['is_active'] ?? 0) === 1;

$pageTitle = 'Adjust Points';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main', ['title' => 'Adjust Points', 'crumbs' => ['Sales', 'Loyalty', 'Adjust']]);
?>
<div class="mx-auto max-w-3xl space-y-5">

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
      <i class="fa-solid fa-magnifying-glass text-brand"></i> Find the card to adjust
    </div>
    <?php component('loyalty/loyalty-lookup', ['value' => $loyaltyId, 'action' => url('pages/loyalty/adjust-points.php')]); ?>
  </div>

  <?php if ($loyaltyId !== '' && !$found): ?>
    <?php component('common/empty-state', ['icon' => 'fa-circle-exclamation', 'title' => 'Card not found', 'message' => 'No active card matches “' . $loyaltyId . '”.']); ?>
  <?php elseif ($found): ?>
    <?php component('loyalty/loyalty-card', ['card' => $card]); ?>
    <div class="grid gap-5 sm:grid-cols-2">
      <?php component('loyalty/loyalty-points', ['card' => $card, 'canAdjust' => true]); ?>
      <div>
        <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Recent Activity</h3>
        <?php component('loyalty/loyalty-history', ['history' => $l->historyFor((int) $card['card_id'], 1, 5), 'showOrder' => true]); ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php component('layout/footer'); ?>
