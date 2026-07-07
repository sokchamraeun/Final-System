<?php
declare(strict_types=1);
/* Order table. component('orders/order-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-receipt',
        'title'   => 'No orders',
        'message' => 'Orders will appear here once customers start ordering.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">Order #</th>
        <th class="px-5 py-3">Customer</th>
        <th class="px-5 py-3">Total</th>
        <th class="px-5 py-3">Status</th>
        <th class="px-5 py-3">Date</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200">#<?= (int) ($r['order_id'] ?? 0) ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['customer_name'] ?? 'Walk-in') ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= isset($r['total']) ? money($r['total']) : '—' ?></td>
        <td class="px-5 py-3">
          <?php
            $status = $r['status'] ?? '';
            $badgeClass = STATUS_BADGES[$status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
          ?>
          <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold <?= e($badgeClass) ?>">
            <?= e(ucwords(str_replace('_', ' ', $status))) ?>
          </span>
        </td>
        <td class="px-5 py-3 text-slate-500"><?= isset($r['order_date']) ? time_ago($r['order_date']) : '—' ?></td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <div class="flex items-center justify-end gap-1">
            <a href="<?= e(url('orders/board') . '?highlight=' . (int) ($r['order_id'] ?? 0)) ?>"
               class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="View">
              <i class="fa-solid fa-eye"></i>
            </a>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
