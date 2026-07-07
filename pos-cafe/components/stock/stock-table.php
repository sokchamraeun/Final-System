<?php
declare(strict_types=1);
/* Stock count table. component('stock/stock-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-clipboard-list',
        'title'   => 'No stock counts yet',
        'message' => 'Start a new stock count to begin tracking inventory.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/stock/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Count</a>'
            : '',
    ]);
    return;
}

$statusBadges = [
    'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'completed'   => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
];
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">#</th>
        <th class="px-5 py-3">Business Date</th>
        <th class="px-5 py-3">Status</th>
        <th class="px-5 py-3">Submitted By</th>
        <th class="px-5 py-3">Notes</th>
        <th class="px-5 py-3">Created</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200"><?= (int) $r['count_id'] ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['business_date'] ?? '') ?></td>
        <td class="px-5 py-3">
          <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold <?= e($statusBadges[$r['status']] ?? 'bg-slate-100 text-slate-600') ?>">
            <?= e(ucwords(str_replace('_', ' ', $r['status'] ?? ''))) ?>
          </span>
        </td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['submitted_by_name'] ?? '—') ?></td>
        <td class="max-w-xs truncate px-5 py-3 text-slate-500"><?= e($r['notes'] ?? '') ?></td>
        <td class="px-5 py-3 text-slate-500"><?= isset($r['created_at']) ? time_ago($r['created_at']) : '—' ?></td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <?php if (($r['status'] ?? '') === 'in_progress'): ?>
          <a href="<?= e(url('pages/stock/edit.php?id=' . (int) $r['count_id'])) ?>"
             class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            <i class="fa-solid fa-pen"></i> Edit
          </a>
          <?php endif; ?>
          <button type="button"
                  onclick="confirmDeleteStock(<?= (int) $r['count_id'] ?>, <?= e(json_encode('#' . $r['count_id'] . ' — ' . ($r['business_date'] ?? ''))) ?>)"
                  class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
            <i class="fa-solid fa-trash-can"></i> Delete
          </button>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
