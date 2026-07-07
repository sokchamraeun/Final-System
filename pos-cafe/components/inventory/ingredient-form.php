<?php
declare(strict_types=1);
/* Create/Edit form. component('inventory/ingredient-form', ['ingredient'=>$i, 'suppliers'=>$suppliers, 'errors'=>$errs, 'action'=>url(...)]); */
$ingredient = $ingredient ?? [];
$suppliers  = $suppliers  ?? [];
$errors     = $errors     ?? [];
$action     = $action     ?? '';
$isEdit     = !empty($ingredient['ingredient_id']);

$val = static fn(string $k, $d = '') => e($ingredient[$k] ?? $d);
$units = ['g', 'kg', 'ml', 'l', 'pc', 'oz'];
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
    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Ingredient name</label>
      <input type="text" name="ingredient_name" value="<?= $val('ingredient_name') ?>" required
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Unit</label>
      <select name="unit"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— select —</option>
        <?php foreach ($units as $u): ?>
          <option value="<?= e($u) ?>" <?= ($ingredient['unit'] ?? '') === $u ? 'selected' : '' ?>><?= e($u) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Stock quantity</label>
      <input type="number" name="stock_quantity" step="0.01" min="0" value="<?= $val('stock_quantity', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Minimum stock</label>
      <input type="number" name="minimum_stock" step="0.01" min="0" value="<?= $val('minimum_stock', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Cost price ($)</label>
      <input type="number" name="cost_price" step="0.01" min="0" value="<?= $val('cost_price') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Purchase qty</label>
      <input type="number" name="purchase_qty" step="0.01" min="0" value="<?= $val('purchase_qty', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Cost per unit ($)</label>
      <input type="number" name="cost_per_unit" step="0.01" min="0" value="<?= $val('cost_per_unit') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Supplier</label>
      <select name="supplier_id"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— none —</option>
        <?php foreach ($suppliers as $sid => $sname): ?>
          <option value="<?= (int) $sid ?>" <?= (int) ($ingredient['supplier_id'] ?? 0) === (int) $sid ? 'selected' : '' ?>><?= e($sname) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/inventory/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create ingredient' ?>
    </button>
  </div>
</form>
