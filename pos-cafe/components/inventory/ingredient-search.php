<?php
declare(strict_types=1);
/* Ingredient search bar. component('inventory/ingredient-search', ['search'=>$s, 'lowStock'=>$low]); */
$search   = $search   ?? '';
$lowStock = $lowStock ?? '';
?>
<form method="GET" class="mb-5 flex items-center gap-3">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search ingredients…"
           class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
  </div>
  <button type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-search"></i> Search</button>
  <?php if ($search !== '' || $lowStock !== ''): ?>
    <a href="<?= e(url('pages/inventory/index.php')) ?>" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">Clear</a>
  <?php endif; ?>
  <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
    <input type="checkbox" name="low_stock" value="1" <?= $lowStock === '1' ? 'checked' : '' ?> onchange="this.form.submit()" class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand">
    Low stock only
  </label>
</form>
