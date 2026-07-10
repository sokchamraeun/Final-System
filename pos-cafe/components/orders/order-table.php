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
<div class="overflow-hidden rounded-3xl border border-slate-200/60 bg-white dark:border-slate-700/60 dark:bg-slate-900">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-teal-800 dark:bg-teal-900">
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Order</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Customer</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Phone</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Table</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Items</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Total</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Date</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Status</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Payment</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Method</th>
          <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Placed By</th>
          <?php if ($canManage): ?>
          <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-white">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php foreach ($rows as $r): ?>
        <?php
          $status = $r['status'] ?? '';
          $payment = $r['payment_status'] ?? '';

          $statusBadge = STATUS_BADGES[$status]
              ?? 'border border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300';

          $paymentBadge = match (strtolower((string) $payment)) {
              'paid'    => 'border border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300',
              'unpaid'  => 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
              'refunded' => 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-500/10 dark:text-rose-400',
              default   => 'border border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300',
          };
        ?>
        <tr class="transition-colors duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40">

          <td class="px-5 py-4 font-semibold text-teal-700 dark:text-teal-400">
            #<?= (int) ($r['order_id'] ?? 0) ?>
          </td>

          <td class="px-5 py-4 font-medium text-slate-700 dark:text-slate-200">
            <?= e($r['customer_name'] ?? 'Guest') ?>
          </td>

          <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
            <?= e($r['phone'] ?? '-') ?>
          </td>

          <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
            <?= e((string) ($r['table_number'] ?? '-')) ?>
          </td>

          <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
            <?= e((string) ($r['items_count'] ?? '-')) ?>
          </td>

          <td class="px-5 py-4 font-semibold text-teal-700 dark:text-teal-400">
            <?= isset($r['total']) ? money($r['total']) : '—' ?>
          </td>

          <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
            <?= isset($r['order_date']) ? e(date('Y-m-d', strtotime((string) $r['order_date']))) : '—' ?>
          </td>

          <td class="px-5 py-4">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($statusBadge) ?>">
              <?= e(ucwords(str_replace('_', ' ', $status))) ?>
            </span>
          </td>

          <td class="px-5 py-4">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($paymentBadge) ?>">
              <?= e(ucwords((string) $payment) ?: '—') ?>
            </span>
          </td>

          <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
            <?= e($r['method'] ?? '-') ?>
          </td>

          <td class="px-5 py-4 text-slate-500 dark:text-slate-400">
            <?= e($r['placed_by'] ?? '-') ?>
          </td>

          <?php if ($canManage): ?>
          <td class="px-5 py-4">
            <div class="flex justify-end">
              <a href="<?= e(url('pages/order/index.php?id=' . (int) ($r['order_id'] ?? 0))) ?>"
                 class="rounded-full border border-teal-200 bg-teal-50 px-3.5 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300 dark:hover:bg-teal-500/20">
                View Detail
              </a>
            </div>
          </td>
          <?php endif; ?>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>