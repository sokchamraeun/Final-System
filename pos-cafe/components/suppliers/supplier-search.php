<?php
declare(strict_types=1);
$search = $search ?? '';
?>
<form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or contact…" autocomplete="off"
           class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-900">
  </div>
  <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
    <i class="fa-solid fa-filter"></i> Filter
  </button>
</form>
