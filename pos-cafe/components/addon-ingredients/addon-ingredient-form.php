<?php
declare(strict_types=1);
/* Addon-ingredient assign/edit form.
   component('addon-ingredients/addon-ingredient-form', ['action'=>url(...), 'addonIngredient'=>[], 'addons'=>[], 'ingredients'=>[], 'errors'=>[]]); */
$addonIngredient = $addonIngredient ?? [];
$addons          = $addons          ?? [];
$ingredients     = $ingredients     ?? [];
$errors          = $errors          ?? [];
$action          = $action          ?? '';
$isEdit          = !empty($addonIngredient['id']);

$val = static fn(string $k, $d = '') => e($addonIngredient[$k] ?? $d);
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
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Addon</label>
      <select name="addon_id" <?= $isEdit ? 'disabled' : 'required' ?>
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— select addon —</option>
        <?php foreach ($addons as $id => $name): ?>
          <option value="<?= $id ?>" <?= (int) ($addonIngredient['addon_id'] ?? 0) === $id ? 'selected' : '' ?>>
            <?= e($name) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if ($isEdit): ?>
        <input type="hidden" name="addon_id" value="<?= (int) $addonIngredient['addon_id'] ?>">
      <?php endif; ?>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Ingredient</label>
      <select name="ingredient_id" <?= $isEdit ? 'disabled' : 'required' ?>
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800">
        <option value="">— select ingredient —</option>
        <?php foreach ($ingredients as $id => $name): ?>
          <option value="<?= $id ?>" <?= (int) ($addonIngredient['ingredient_id'] ?? 0) === $id ? 'selected' : '' ?>>
            <?= e($name) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if ($isEdit): ?>
        <input type="hidden" name="ingredient_id" value="<?= (int) $addonIngredient['ingredient_id'] ?>">
      <?php endif; ?>
    </div>

    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Amount Used</label>
      <input type="number" name="amount_used" step="0.01" min="0" value="<?= $val('amount_used') ?>" required
             class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
    </div>
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/addon-ingredients/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> <?= $isEdit ? 'Save changes' : 'Assign ingredient' ?>
    </button>
  </div>
</form>
