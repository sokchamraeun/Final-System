<?php
declare(strict_types=1);
/* Loyalty ID lookup form. component('loyalty/loyalty-lookup', ['value' => $v, 'action' => url(...)]); */
$value  = $value  ?? '';
$action = $action ?? url('pages/loyalty/lookup.php');
?>
<form method="GET" action="<?= e($action) ?>" class="flex flex-col gap-3 sm:flex-row">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="loyalty_id" value="<?= e($value) ?>" placeholder="Enter loyalty ID (e.g. CARD-12345)" autocomplete="off"
           class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-900">
  </div>
  <button type="submit" class="rounded-xl bg-brand px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">
    <i class="fa-solid fa-magnifying-glass"></i> Look up
  </button>
</form>
