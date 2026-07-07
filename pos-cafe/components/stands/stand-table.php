<?php
declare(strict_types=1);
$stands    = $stands    ?? [];
$canManage = $canManage ?? false;

if (!$stands) {
    component('common/empty-state', [
        'icon'    => 'fa-chair',
        'title'   => 'No stands configured',
        'message' => 'Stands are configured via the STAND_COUNT setting.',
    ]);
    return;
}
?>
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
  <?php foreach ($stands as $s):
    $occupied = $s['occupied'] ?? false;
    $color    = $occupied ? 'red' : 'teal';
    $icon     = $occupied ? 'fa-chair' : 'fa-chair';
    $status   = $occupied ? 'Occupied' : 'Free';
  ?>
  <div class="group relative overflow-hidden rounded-2xl border bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md dark:bg-slate-900 dark:shadow-slate-900/50 <?= $occupied ? 'border-red-200 dark:border-red-900/50' : 'border-slate-200 dark:border-slate-700' ?>">
    <?php if ($occupied): ?>
    <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-red-500/10 blur-2xl"></div>
    <?php else: ?>
    <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-teal-500/10 blur-2xl"></div>
    <?php endif; ?>

    <div class="relative flex items-center gap-4">
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl <?= $occupied ? 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400' : 'bg-teal-100 text-teal-600 dark:bg-teal-500/20 dark:text-teal-400' ?>">
        <i class="fa-solid fa-chair"></i>
      </div>
      <div class="min-w-0 flex-1">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white"><?= e($s['label']) ?></h3>
        <span class="inline-flex items-center gap-1.5 text-sm <?= $occupied ? 'text-red-600 dark:text-red-400' : 'text-teal-600 dark:text-teal-400' ?>">
          <i class="fa-solid fa-circle text-[6px]"></i>
          <?= $status ?>
        </span>
      </div>
    </div>

    <?php if ($occupied && $canManage): ?>
    <button type="button"
            onclick="confirmFreeStand(<?= (int) $s['id'] ?>, <?= e(json_encode($s['label'])) ?>)"
            class="mt-4 w-full rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100 hover:text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-950/50">
      <i class="fa-solid fa-door-open"></i> Free stand
    </button>
    <?php elseif (!$occupied && $canManage): ?>
    <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-2.5 text-center text-sm text-slate-400 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-500">
      <i class="fa-solid fa-check mr-1"></i> Available
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
