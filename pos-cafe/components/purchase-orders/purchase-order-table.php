<?php
declare(strict_types=1);
$purchaseOrders = $purchaseOrders ?? [];
$canManage      = $canManage      ?? false;

if (!$purchaseOrders) {
    component('common/empty-state', [
        'icon'    => 'fa-file-invoice',
        'title'   => 'No purchase orders found',
        'message' => 'Try a different search or create a new purchase order.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/purchase-orders/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Purchase Order</a>'
            : '',
    ]);
    return;
}

$statusBadges = [
    'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'ordered'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'received'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <tr>
        <th class="px-5 py-3">PO Number</th>
        <th class="px-5 py-3">Supplier</th>
        <th class="px-5 py-3">Status</th>
        <th class="px-5 py-3">Total Cost</th>
        <th class="px-5 py-3">Ordered At</th>
        <?php if ($canManage): ?>
        <th class="px-5 py-3 text-right">Actions</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($purchaseOrders as $po): ?>
      <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
        <td class="px-5 py-4 font-medium text-slate-800 dark:text-white"><?= e($po['po_number']) ?></td>
        <td class="px-5 py-4 text-slate-500"><?= e($po['supplier_name'] ?? '—') ?></td>
        <td class="px-5 py-4">
          <span class="inline-block rounded-full px-3 py-0.5 text-xs font-semibold <?= $statusBadges[$po['status']] ?? $statusBadges['pending'] ?>">
            <?= e(ucfirst($po['status'] ?? 'pending')) ?>
          </span>
        </td>
        <td class="px-5 py-4 text-slate-500"><?= money((float) ($po['total_cost'] ?? 0)) ?></td>
        <td class="px-5 py-4 text-slate-500"><?= e($po['ordered_at'] ?? $po['created_at'] ?? '—') ?></td>
        <?php if ($canManage): ?>
        <td class="px-5 py-4 text-right">
          <a href="<?= e(url('pages/purchase-orders/edit.php?id=' . (int) $po['po_id'])) ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
            <i class="fa-solid fa-pen"></i> Edit
          </a>
          <button type="button" onclick="confirmDeletePO(<?= (int) $po['po_id'] ?>, <?= json_encode(e($po['po_number'])) ?>)" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30">
            <i class="fa-solid fa-trash-can"></i> Delete
          </button>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
