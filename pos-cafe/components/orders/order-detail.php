<?php
declare(strict_types=1);
/* Order detail. component('orders/order-detail', ['order' => $order, 'items' => $items]); */
$order = $order ?? [];
$items = $items ?? [];
if (!$order) return;

$status      = $order['status'] ?? '';
$statusBadge = STATUS_BADGES[$status]
    ?? 'border border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300';

$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += (float) $it['price'] * (int) $it['quantity'];
}
?>
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 p-5 dark:border-slate-800">
      <div>
        <div class="text-lg font-bold text-slate-800 dark:text-white">#<?= (int) $order['daily_order_no'] ?> &middot; <?= e($order['customer_name'] ?? 'Guest') ?></div>
        <div class="mt-1 text-xs text-slate-400"><?= isset($order['order_date']) ? e(date('d M Y, g:i A', strtotime((string) $order['order_date']))) : '—' ?></div>
      </div>
      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= e($statusBadge) ?>">
        <?= e(ucwords(str_replace('_', ' ', $status))) ?>
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-teal-800 dark:bg-teal-900">
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Item</th>
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">Options</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-white">Qty</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-white">Price</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-white">Line Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php if (!$items): ?>
          <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400">No items on this order.</td></tr>
          <?php endif; ?>
          <?php foreach ($items as $it): ?>
          <?php
            $opts = array_filter([
                $it['size_label'] ?? null,
                $it['sweetness'] ?? null,
                $it['ice'] ?? null,
                $it['sugar'] ?? null,
                $it['milk'] ?? null,
            ]);
          ?>
          <tr>
            <td class="px-5 py-4 font-medium text-slate-700 dark:text-slate-200"><?= e($it['product_name']) ?></td>
            <td class="px-5 py-4 text-xs text-slate-500 dark:text-slate-400"><?= $opts ? e(implode(' · ', $opts)) : '—' ?></td>
            <td class="px-5 py-4 text-right text-slate-600 dark:text-slate-300"><?= (int) $it['quantity'] ?></td>
            <td class="px-5 py-4 text-right text-slate-600 dark:text-slate-300"><?= money($it['price']) ?></td>
            <td class="px-5 py-4 text-right font-semibold text-teal-700 dark:text-teal-400"><?= money((float) $it['price'] * (int) $it['quantity']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if ($items): ?>
        <tfoot>
          <tr class="border-t border-slate-200 dark:border-slate-800">
            <td colspan="4" class="px-5 py-3 text-right text-sm font-semibold text-slate-500 dark:text-slate-400">Subtotal</td>
            <td class="px-5 py-3 text-right font-semibold text-slate-700 dark:text-slate-200"><?= money($subtotal) ?></td>
          </tr>
          <tr>
            <td colspan="4" class="px-5 py-3 text-right text-base font-bold text-slate-700 dark:text-slate-200">Total</td>
            <td class="px-5 py-3 text-right text-base font-bold text-teal-700 dark:text-teal-400"><?= money($order['total']) ?></td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>

    <?php if (!empty($order['cancel_reason'])): ?>
    <div class="border-t border-slate-100 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300">
      <i class="fa-solid fa-ban text-rose-500"></i>
      Cancelled by <?= e($order['cancelled_by']) ?><?= !empty($order['cancelled_at']) ? ' on ' . e(date('d M Y, g:i A', strtotime((string) $order['cancelled_at']))) : '' ?> — <?= e($order['cancel_reason']) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($order['refund_reason'])): ?>
    <div class="border-t border-slate-100 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-300">
      <i class="fa-solid fa-rotate-left text-purple-500"></i>
      Refunded <?= money($order['refund_amount'] ?? 0) ?> by <?= e($order['refunded_by']) ?><?= !empty($order['refunded_at']) ? ' on ' . e(date('d M Y, g:i A', strtotime((string) $order['refunded_at']))) : '' ?> — <?= e($order['refund_reason']) ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="space-y-5">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-100 p-4 text-xs font-bold uppercase tracking-wide text-slate-400 dark:border-slate-800">Customer</div>
      <div class="divide-y divide-slate-100 dark:divide-slate-800">
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Name</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($order['customer_name'] ?? 'Guest') ?></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Phone</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($order['customer_phone'] ?? '—') ?></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Table / Stand</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($order['table_number'] ?? '—') ?></span>
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-100 p-4 text-xs font-bold uppercase tracking-wide text-slate-400 dark:border-slate-800">Payment</div>
      <div class="divide-y divide-slate-100 dark:divide-slate-800">
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Method</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e(ucfirst((string) ($order['payment_method'] ?? '—'))) ?></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Discount</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= money(((float) ($order['manual_discount'] ?? 0)) + ((float) ($order['promotion_discount'] ?? 0))) ?></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Points earned</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= (int) ($order['points_earned'] ?? 0) ?></span>
        </div>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="border-b border-slate-100 p-4 text-xs font-bold uppercase tracking-wide text-slate-400 dark:border-slate-800">Handled by</div>
      <div class="divide-y divide-slate-100 dark:divide-slate-800">
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Placed by</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($order['employee_name'] ?? $order['served_by'] ?? '—') ?></span>
        </div>
        <div class="flex items-center justify-between px-4 py-3 text-sm">
          <span class="text-slate-400">Prepared by</span>
          <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($order['prepared_by'] ?? '—') ?></span>
        </div>
      </div>
    </div>
  </div>

</div>
