<?php
declare(strict_types=1);
/* Loyalty — full transaction history for a card (from loyalty_history.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';

$loyaltyId = trim((string) input('id', input('loyalty_id', '')));
if ($loyaltyId === '') {
    redirect(url('pages/loyalty/index.php'));
}

$l    = new Loyalty();
$card = $l->findByLoyaltyId($loyaltyId);
if (!$card) {
    flash('Card not found.', 'error');
    redirect(url('pages/loyalty/index.php'));
}

$cardId  = (int) $card['card_id'];
$points  = (int) $card['points'];
$tier    = Loyalty::tier($points);
$perPage = 10;
$page    = max(1, (int) input('page', 1));
$total   = $l->historyCount($cardId);
$history = $l->historyFor($cardId, $page, $perPage);

$pct = $tier['next_pts'] > 0 ? min(100, (int) round(($points / $tier['next_pts']) * 100)) : 100;

$pageTitle = 'History · ' . $card['loyalty_id'];
$baseUrl   = url('pages/loyalty/history.php?id=' . urlencode($loyaltyId));

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main', [
    'title'  => 'Transaction History',
    'crumbs' => ['Sales', 'Loyalty', 'History'],
    'actions'=> '<a href="' . e(url('pages/loyalty/lookup.php?loyalty_id=' . urlencode($card['loyalty_id']))) . '" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"><i class="fa-solid fa-arrow-left"></i> Back to card</a>',
]);
?>
<div class="space-y-5">
  <?php component('loyalty/loyalty-card', ['card' => $card]); ?>

  <!-- Tier progress -->
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-2 flex items-center justify-between text-sm">
      <span class="font-semibold" style="color: <?= e($tier['color']) ?>"><i class="fa-solid fa-medal"></i> <?= e($tier['name']) ?></span>
      <?php if ($tier['next']): ?>
        <span class="text-slate-500"><?= $pct ?>% → <?= e($tier['next']) ?> (<?= number_format($tier['next_pts']) ?> pts)</span>
      <?php else: ?>
        <span class="text-slate-500">Maximum tier reached</span>
      <?php endif; ?>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
      <div class="h-full rounded-full transition-all" style="width: <?= $pct ?>%; background: <?= e($tier['color']) ?>"></div>
    </div>
    <?php if ($tier['next']): ?>
    <div class="mt-1.5 text-right text-xs text-slate-400"><?= number_format(max(0, $tier['next_pts'] - $points)) ?> more pts to reach <?= e($tier['next']) ?></div>
    <?php endif; ?>
  </div>

  <div>
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">All Transactions</h3>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 dark:bg-slate-800"><?= number_format($total) ?> total</span>
    </div>
    <?php component('loyalty/loyalty-history', ['history' => $history, 'showOrder' => true]); ?>
    <?php component('common/pagination', ['page' => $page, 'perPage' => $perPage, 'total' => $total, 'baseUrl' => $baseUrl]); ?>
  </div>
</div>
<?php component('layout/footer'); ?>
