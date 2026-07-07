<?php
declare(strict_types=1);
/* Simple stand number input. component('stands/stand-form', ['stand'=>$s, 'action'=>$url]); */
$stand  = $stand  ?? [];
$action = $action ?? '';
$val = static fn(string $k, $d = '') => e($stand[$k] ?? $d);
?>
<form method="POST" action="<?= e($action) ?>" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <?= csrf_field() ?>

  <div>
    <label class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">Stand number</label>
    <input type="number" name="stand_id" value="<?= $val('id') ?>" min="1" required
           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
  </div>

  <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
    <a href="<?= e(url('pages/stands/index.php')) ?>" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
    <button type="submit" class="rounded-xl bg-brand px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-check"></i> Save
    </button>
  </div>
</form>
