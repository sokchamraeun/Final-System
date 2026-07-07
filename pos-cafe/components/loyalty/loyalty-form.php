<?php
declare(strict_types=1);
/* Loyalty form. component('loyalty/loyalty-form', ['loyalty' => $card, 'errors' => $errs, 'action' => url(...)]); */
$loyalty = $loyalty ?? [];
$errors  = $errors  ?? [];
$action  = $action  ?? '';
$isEdit  = !empty($loyalty['card_id']);

$val = static fn(string $k, $d = '') => e($loyalty[$k] ?? $d);
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
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Loyalty ID</label>
      <input type="text" name="loyalty_id" value="<?= $val('loyalty_id') ?>" required maxlength="50"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      <p class="mt-1 text-xs text-slate-400">Unique identifier for the loyalty card (e.g. phone number or code).</p>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Points</label>
      <input type="number" name="points" min="0" step="1" value="<?= $val('points', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Total Orders</label>
      <input type="number" name="total_orders" min="0" step="1" value="<?= $val('total_orders', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Total Drinks</label>
      <input type="number" name="total_drinks" min="0" step="1" value="<?= $val('total_drinks', '0') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Last Used</label>
      <input type="datetime-local" name="last_used" value="<?= $val('last_used') ?>"
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
      <input type="checkbox" name="is_active" value="1" <?= (int) ($loyalty['is_active'] ?? 1) === 1 ? 'checked' : '' ?> class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
      Active
    </label>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/loyalty/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Create loyalty card' ?>
    </button>
  </div>
</form>
