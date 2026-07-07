<?php
declare(strict_types=1);
/* Loyalty table. component('loyalty/loyalty-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-gift',
        'title'   => 'No loyalty records',
        'message' => 'Loyalty data will appear once customers start earning points.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">Loyalty ID</th>
        <th class="px-5 py-3">Points</th>
        <th class="px-5 py-3">Total Orders</th>
        <th class="px-5 py-3">Status</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200"><?= e($r['loyalty_id'] ?? '—') ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e((string) ($r['points'] ?? 0)) ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= (int) ($r['total_orders'] ?? 0) ?></td>
        <td class="px-5 py-3">
          <?php if (!empty($r['is_active'])): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
              Active
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800">Inactive</span>
          <?php endif; ?>
        </td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <div class="flex items-center justify-end gap-1">
            <a href="<?= e(url('pages/loyalty/edit.php?id=' . (int) ($r['card_id'] ?? 0))) ?>"
               class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </a>
            <button type="button"
                    onclick="confirmDeleteLoyalty(<?= (int) ($r['card_id'] ?? 0) ?>, <?= e(json_encode($r['loyalty_id'] ?? '')) ?>)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10" title="Delete">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
