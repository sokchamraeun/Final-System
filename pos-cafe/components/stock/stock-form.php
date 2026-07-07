<?php
declare(strict_types=1);
/* Stock count form (create + edit). component('stock/stock-form', ['action'=>url(...), 'count'=>[], 'errors'=>[]]); */
$count  = $count  ?? [];
$errors = $errors ?? [];
$action = $action ?? '';
$isEdit = !empty($count['count_id']);

$val = static fn(string $k, $d = '') => e($count[$k] ?? $d);
?>
<form method="POST" action="<?= e($action) ?>" class="space-y-5">
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

  <?php if ($isEdit): ?>
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-4 grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Business Date</label>
        <input type="date" name="business_date" value="<?= $val('business_date') ?>" required
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Status</label>
        <span class="block rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
          <?= e(ucwords(str_replace('_', ' ', $count['status'] ?? 'in_progress'))) ?>
        </span>
      </div>
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Notes</label>
      <textarea name="notes" rows="2"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800"><?= $val('notes') ?></textarea>
    </div>

    <?php if (!empty($count['items'])): ?>
    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
            <th class="px-4 py-3">Ingredient</th>
            <th class="px-4 py-3">Unit</th>
            <th class="px-4 py-3">Expected Qty</th>
            <th class="px-4 py-3">Actual Qty</th>
            <th class="px-4 py-3">Difference</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php foreach ($count['items'] as $item): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200"><?= e($item['ingredient_name']) ?></td>
            <td class="px-4 py-3 text-slate-500"><?= e($item['unit'] ?? '') ?></td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e((string) $item['expected_qty']) ?></td>
            <td class="px-4 py-3">
              <?php if (($count['status'] ?? '') === 'in_progress'): ?>
              <input type="number" name="actual_qty[<?= (int) $item['item_id'] ?>]" step="0.001" min="0"
                     value="<?= e((string) ($item['actual_qty'] ?? 0)) ?>"
                     class="w-28 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
              <?php else: ?>
              <span class="text-slate-600 dark:text-slate-300"><?= e((string) $item['actual_qty']) ?></span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <?php $diff = (float) ($item['difference'] ?? 0); ?>
              <span class="<?= $diff < 0 ? 'text-red-500' : ($diff > 0 ? 'text-emerald-500' : 'text-slate-400') ?>">
                <?= $diff > 0 ? '+' : '' ?><?= e((string) $diff) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
      <a href="<?= e(url('pages/stock/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" name="form_action" value="save"
              class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
        <i class="fa-solid fa-floppy-disk"></i> Save
      </button>
      <?php if (($count['status'] ?? '') === 'in_progress'): ?>
      <button type="submit" name="form_action" value="complete"
              class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"
              onclick="return confirm('Complete this stock count? This will calculate differences.')">
        <i class="fa-solid fa-check"></i> Complete Count
      </button>
      <?php endif; ?>
    </div>
  </div>

  <?php else: /* Create mode */ ?>
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Business Date</label>
        <input type="date" name="business_date" value="<?= date('Y-m-d') ?>" required
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Notes (optional)</label>
        <input type="text" name="notes" value="<?= $val('notes') ?>" placeholder="End of day count…"
               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
      </div>
    </div>

    <p class="mt-4 text-sm text-slate-500">
      <i class="fa-solid fa-info-circle"></i> Starting a count will pre-fill all ingredient quantities from current stock levels.
    </p>

    <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
      <a href="<?= e(url('pages/stock/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
        <i class="fa-solid fa-play"></i> Start Count
      </button>
    </div>
  </div>
  <?php endif; ?>
</form>
