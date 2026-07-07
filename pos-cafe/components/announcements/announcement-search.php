<?php
declare(strict_types=1);
$search  = $search  ?? '';
$type    = $type    ?? '';
$showAll = $showAll ?? false;
?>
<form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search announcements…" autocomplete="off"
           class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-900">
  </div>
  <select name="type"
          class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-900">
    <option value="">All types</option>
    <option value="info" <?= $type === 'info' ? 'selected' : '' ?>>Info</option>
    <option value="warning" <?= $type === 'warning' ? 'selected' : '' ?>>Warning</option>
    <option value="promotion" <?= $type === 'promotion' ? 'selected' : '' ?>>Promotion</option>
    <option value="alert" <?= $type === 'alert' ? 'selected' : '' ?>>Alert</option>
  </select>
  <?php if ($showAll): ?>
  <select name="is_active"
          class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-900">
    <option value="">All statuses</option>
    <option value="1" <?= (string) ($isActive ?? '') === '1' ? 'selected' : '' ?>>Active</option>
    <option value="0" <?= (string) ($isActive ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
  </select>
  <?php endif; ?>
  <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
    <i class="fa-solid fa-filter"></i> Filter
  </button>
</form>
