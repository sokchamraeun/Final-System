<?php
declare(strict_types=1);
/* Loyalty — card lookup (from loyalty_lookup.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';

$loyaltyId = trim((string) input('loyalty_id', ''));
$l    = new Loyalty();
$card = $loyaltyId !== '' ? $l->findByLoyaltyId($loyaltyId) : null;
$found = $card && (int) ($card['is_active'] ?? 0) === 1;

if ($found) {
    /* Mirror the API: remember the card so confirm_order.php can award points. */
    $_SESSION['loyalty_card_id'] = (int) $card['card_id'];
}

$pageTitle = 'Loyalty Lookup';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Card Lookup', 'crumbs' => ['Sales', 'Loyalty', 'Lookup']]);
?>

<div class="mx-auto max-w-3xl space-y-5">

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <?php component('loyalty/loyalty-lookup', ['value' => $loyaltyId]); ?>
  </div>

  <?php if ($loyaltyId !== '' && !$found): ?>
    <?php component('common/empty-state', [
        'icon'    => 'fa-circle-exclamation',
        'title'   => 'Card not found',
        'message' => 'No active loyalty card matches “' . $loyaltyId . '”.',
    ]); ?>
  <?php elseif ($found): ?>
    <?php
    $cardId  = (int) $card['card_id'];
    $points  = (int) $card['points'];
    $rewards = $l->rewards();
    $history = $l->historyFor($cardId, 1, 5);
    ?>

    <?php component('loyalty/loyalty-card', ['card' => $card]); ?>

    <div class="grid gap-5 sm:grid-cols-2">
      <?php component('loyalty/loyalty-points', ['card' => $card, 'canAdjust' => can('loyalty')]); ?>
      <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Quick links</div>
        <div class="flex flex-col gap-2">
          <a href="<?= e(url('pages/loyalty/history.php?id=' . urlencode($card['loyalty_id']))) ?>" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"><i class="fa-solid fa-clock-rotate-left"></i> Full history</a>
          <a href="<?= e(root_url('print_loyalty_card.php?id=' . urlencode($card['loyalty_id']))) ?>" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"><i class="fa-solid fa-print"></i> Print card</a>
        </div>
      </div>
    </div>

    <div>
      <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Redeem Rewards</h3>
      <?php component('loyalty/loyalty-redeem', ['rewards' => $rewards, 'points' => $points, 'loyaltyId' => $card['loyalty_id']]); ?>
    </div>

    <div>
      <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Recent Transactions</h3>
      <?php component('loyalty/loyalty-history', ['history' => $history, 'showOrder' => true]); ?>
    </div>
  <?php endif; ?>
</div>

<?php component('layout/footer'); ?>
