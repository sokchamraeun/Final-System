<?php
declare(strict_types=1);
/* Loyalty card summary block. component('loyalty/loyalty-card', ['card' => $card]); */
$card = $card ?? [];
if (!$card) return;
$points = (int) ($card['points'] ?? 0);
$tier   = Loyalty::tier($points);
?>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5 dark:border-slate-800">
    <div class="flex items-center gap-3">
      <div class="grid h-12 w-12 place-items-center rounded-xl text-xl" style="background: <?= e($tier['color']) ?>22; color: <?= e($tier['color']) ?>">
        <i class="fa-solid fa-medal"></i>
      </div>
      <div>
        <div class="text-lg font-bold text-slate-800 dark:text-white"><?= e($card['loyalty_id'] ?? '—') ?></div>
        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold" style="color: <?= e($tier['color']) ?>; background: <?= e($tier['color']) ?>1a">
          <?= e($tier['name']) ?>
        </span>
      </div>
    </div>
    <div class="text-right">
      <div class="text-3xl font-extrabold tabular-nums" style="color: <?= e($tier['color']) ?>"><?= number_format($points) ?></div>
      <div class="text-xs text-slate-400">points</div>
    </div>
  </div>
  <div class="grid grid-cols-2 gap-px bg-slate-100 dark:bg-slate-800 sm:grid-cols-4">
    <?php
    $meta = [
        ['fa-receipt',  'Orders',    (string) (int) ($card['total_orders'] ?? 0)],
        ['fa-mug-hot',  'Drinks',    (string) (int) ($card['total_drinks'] ?? 0)],
        ['fa-calendar', 'Since',     !empty($card['created_at']) ? date('M Y', strtotime($card['created_at'])) : '—'],
        ['fa-clock',    'Last used', !empty($card['last_used'])  ? date('M j', strtotime($card['last_used']))  : '—'],
    ];
    foreach ($meta as [$icon, $label, $value]): ?>
    <div class="bg-white p-4 dark:bg-slate-900">
      <div class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
        <i class="fa-solid <?= $icon ?> text-brand"></i><?= e($label) ?>
      </div>
      <div class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($value) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
