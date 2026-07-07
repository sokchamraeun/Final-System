<?php
declare(strict_types=1);
/* Loyalty — redeem rewards for a card (from loyalty_redeem.php).
   The actual point deduction is deferred to order confirmation; this page
   stages redemptions via api/loyalty/redeem.php. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';

$loyaltyId = trim((string) input('loyalty_id', ''));
if ($loyaltyId === '') {
    redirect(url('pages/loyalty/lookup.php'));
}

$l    = new Loyalty();
$card = $l->findByLoyaltyId($loyaltyId);
if (!$card || (int) ($card['is_active'] ?? 0) !== 1) {
    flash('Card not found.', 'error');
    redirect(url('pages/loyalty/lookup.php?loyalty_id=' . urlencode($loyaltyId)));
}

$points  = (int) $card['points'];
$rewards = $l->rewards();

$pageTitle = 'Redeem · ' . $card['loyalty_id'];

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main', [
    'title'  => 'Redeem Rewards',
    'crumbs' => ['Sales', 'Loyalty', 'Redeem'],
    'actions'=> '<a href="' . e(url('pages/loyalty/lookup.php?loyalty_id=' . urlencode($card['loyalty_id']))) . '" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"><i class="fa-solid fa-arrow-left"></i> Back to card</a>',
]);
?>
<div class="mx-auto max-w-3xl space-y-5">
  <?php component('loyalty/loyalty-card', ['card' => $card]); ?>
  <div>
    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Available Rewards</h3>
    <?php component('loyalty/loyalty-redeem', ['rewards' => $rewards, 'points' => $points, 'loyaltyId' => $card['loyalty_id']]); ?>
  </div>
</div>
<?php component('layout/footer'); ?>
