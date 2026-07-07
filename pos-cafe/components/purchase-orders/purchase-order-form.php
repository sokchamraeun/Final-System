<?php
declare(strict_types=1);
$purchaseOrder = $purchaseOrder ?? [];
$suppliers     = $suppliers     ?? [];
$errors        = $errors        ?? [];
$action        = $action        ?? '';
$isEdit        = !empty($purchaseOrder['po_id']);

$val = static fn(string $k, $d = '') => e($purchaseOrder[$k] ?? $d);

$statusBadges = [
    'pending'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'ordered'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'received'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
?>
<form method="POST" action="<?= e($action) ?>" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <?= csrf_field() ?>

  <?php if ($errors): ?>
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-inside list-disc space-y-0.5">
      <?php foreach ($errors as $fieldErrs): foreach ($fieldErrs as $msg): ?>
        <li><?= e($msg) ?></li>
      <?php endforeach; endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="grid gap-5 sm:grid-cols-2">
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">PO Number</label>
      <input type="text" name="po_number" value="<?= $val('po_number') ?>" required maxlength="50"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Supplier</label>
      <select name="supplier_id" required
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— select —</option>
        <?php foreach ($suppliers as $sid => $sname): ?>
          <option value="<?= (int) $sid ?>" <?= (int) ($purchaseOrder['supplier_id'] ?? 0) === (int) $sid ? 'selected' : '' ?>><?= e($sname) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Status</label>
      <select name="status"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="pending" <?= ($purchaseOrder['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="ordered" <?= ($purchaseOrder['status'] ?? '') === 'ordered' ? 'selected' : '' ?>>Ordered</option>
        <option value="received" <?= ($purchaseOrder['status'] ?? '') === 'received' ? 'selected' : '' ?>>Received</option>
        <option value="cancelled" <?= ($purchaseOrder['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Total Cost ($)</label>
      <input type="number" name="total_cost" step="0.01" min="0" value="<?= $val('total_cost', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <?php if ($isEdit): ?>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Ordered at</label>
      <input type="datetime-local" name="ordered_at" value="<?= $val('ordered_at') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Received at</label>
      <input type="datetime-local" name="received_at" value="<?= $val('received_at') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>
    <?php endif; ?>
  </div>

  <div>
    <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Notes</label>
    <textarea name="notes" rows="3"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800"><?= $val('notes') ?></textarea>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/purchase-orders/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create purchase order' ?>
    </button>
  </div>
</form>
