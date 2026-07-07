<?php
declare(strict_types=1);
/* Loyalty — dashboard (from loyalty_dashboard.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';

$l        = new Loyalty();
$stats    = $l->stats();
$topCard  = $l->topCard();
$cards    = $l->activeCards();
$recent   = $l->recentHistory(10);
$rewards  = $l->rewards();

$pageTitle    = 'Loyalty';
$pageSubtitle = $stats['total_cards'] . ' active card' . ($stats['total_cards'] === 1 ? '' : 's');

$actions = '<a href="' . e(url('pages/loyalty/generate-id.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Card</a>';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Loyalty Dashboard', 'crumbs' => ['Sales', 'Loyalty'], 'actions' => $actions]);

/* ── Stat cards ── */
$statCards = [
    ['fa-id-card',     'Active Cards',   number_format($stats['total_cards']),                       'text-brand'],
    ['fa-coins',       'Points Issued',  number_format($stats['total_points']),                      'text-emerald-500'],
    ['fa-crown',       'Top Card',       $topCard ? e($topCard['loyalty_id']) . ' · ' . number_format((int) $topCard['points']) : '—', 'text-amber-500'],
    ['fa-gift',        'Active Rewards', number_format(count($rewards)),                             'text-purple-500'],
];
?>
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ($statCards as [$icon, $label, $value, $color]): ?>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div class="min-w-0">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400"><?= e($label) ?></div>
        <div class="mt-1 truncate text-2xl font-extrabold text-slate-800 dark:text-white"><?= $value ?></div>
      </div>
      <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-slate-50 text-lg <?= $color ?> dark:bg-slate-800">
        <i class="fa-solid <?= $icon ?>"></i>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Lookup box -->
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
    <i class="fa-solid fa-magnifying-glass text-brand"></i> Look up a card
  </div>
  <?php component('loyalty/loyalty-lookup'); ?>
</div>

<div class="grid gap-6 lg:grid-cols-2">
  <!-- All cards -->
  <div>
    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">All Cards</h3>
    <?php if (!$cards): ?>
      <?php component('common/empty-state', ['icon' => 'fa-id-card', 'title' => 'No cards yet', 'message' => 'Generate a loyalty ID to create the first card.']); ?>
    <?php else: ?>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
            <th class="px-5 py-3">Card</th><th class="px-5 py-3">Tier</th><th class="px-5 py-3">Points</th><th class="px-5 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php foreach ($cards as $c):
            $tier = Loyalty::tier((int) $c['points']); ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200"><?= e($c['loyalty_id']) ?></td>
            <td class="px-5 py-3"><span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold" style="color: <?= e($tier['color']) ?>; background: <?= e($tier['color']) ?>1a"><?= e($tier['name']) ?></span></td>
            <td class="px-5 py-3 font-semibold tabular-nums text-slate-700 dark:text-slate-200"><?= number_format((int) $c['points']) ?></td>
            <td class="px-5 py-3 text-right">
              <a href="<?= e(url('pages/loyalty/lookup.php?loyalty_id=' . urlencode($c['loyalty_id']))) ?>" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"><i class="fa-solid fa-eye"></i></a>
              <a href="<?= e(url('pages/loyalty/history.php?id=' . urlencode($c['loyalty_id']))) ?>" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"><i class="fa-solid fa-clock-rotate-left"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Recent activity -->
  <div>
    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Recent Activity</h3>
    <?php component('loyalty/loyalty-history', ['history' => $recent, 'showCard' => true, 'showOrder' => false]); ?>
  </div>
</div>

<?php component('layout/footer'); ?>
