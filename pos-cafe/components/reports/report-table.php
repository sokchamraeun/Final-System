<?php
declare(strict_types=1);
/* Report summary table. component('reports/report-table', ['rows' => $rows, 'canManage' => bool]);
   Generic fallback: displays any tabular data with auto-generated columns. */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-chart-simple',
        'title'   => 'No report data',
        'message' => 'Run a report to see results here.',
    ]);
    return;
}

$columns = array_keys(reset($rows));
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <?php foreach ($columns as $col): ?>
        <th class="px-5 py-3"><?= e(ucwords(str_replace('_', ' ', $col))) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <?php foreach ($columns as $col): ?>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e((string) ($r[$col] ?? '')) ?></td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
