<?php
declare(strict_types=1);
/* Loyalty transaction history table.
   component('loyalty/loyalty-history', ['history' => $rows, 'showOrder' => true, 'showCard' => false]); */
$history   = $history   ?? [];
$showOrder = $showOrder ?? true;
$showCard  = $showCard  ?? false;

if (!$history) {
    component('common/empty-state', [
        'icon'    => 'fa-clock-rotate-left',
        'title'   => 'No transactions yet',
        'message' => 'Point activity will appear here.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">Date</th>
        <?php if ($showCard): ?><th class="px-5 py-3">Card</th><?php endif; ?>
        <th class="px-5 py-3">Type</th>
        <th class="px-5 py-3">Points</th>
        <th class="px-5 py-3">Description</th>
        <?php if ($showOrder): ?><th class="px-5 py-3">Order</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($history as $h):
        $tc   = Loyalty::typeConfig((string) ($h['type'] ?? ''));
        $pos  = (int) ($h['points_change'] ?? 0) >= 0;
        $desc = $h['reward_name'] ?? $h['description'] ?? '';
      ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="whitespace-nowrap px-5 py-3 text-slate-500">
          <div><?= !empty($h['created_at']) ? date('M j, Y', strtotime($h['created_at'])) : '—' ?></div>
          <div class="text-xs text-slate-400"><?= !empty($h['created_at']) ? date('g:i A', strtotime($h['created_at'])) : '' ?></div>
        </td>
        <?php if ($showCard): ?>
        <td class="px-5 py-3 font-medium text-brand"><?= e($h['loyalty_id'] ?? '—') ?></td>
        <?php endif; ?>
        <td class="px-5 py-3">
          <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $tc['cls'] ?>">
            <i class="fa-solid <?= $tc['icon'] ?>"></i><?= e($tc['label']) ?>
          </span>
        </td>
        <td class="px-5 py-3">
          <span class="font-bold tabular-nums <?= $pos ? 'text-emerald-600' : 'text-red-500' ?>">
            <?= $pos ? '+' : '' ?><?= (int) ($h['points_change'] ?? 0) ?>
          </span>
        </td>
        <td class="max-w-[220px] truncate px-5 py-3 text-slate-600 dark:text-slate-300" title="<?= e($desc) ?>">
          <?= $desc !== '' ? e($desc) : '<span class="text-slate-300">—</span>' ?>
        </td>
        <?php if ($showOrder): ?>
        <td class="px-5 py-3 text-slate-500"><?= !empty($h['daily_order_no']) ? '<span class="font-semibold text-brand">#' . e($h['daily_order_no']) . '</span>' : '—' ?></td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
