<?php
declare(strict_types=1);
/* ============================================================
   Layout: main — reusable in-page header block (title, optional
   breadcrumbs, and a right-aligned actions slot). Include inside
   the content area, after layout/header.php.

   Usage:
     component('layout/main', [
       'title'   => 'Products',
       'crumbs'  => ['Catalog', 'Products'],
       'actions' => '<a class="..." href="...">+ New</a>',
     ]);
   ============================================================ */
$title   = $title   ?? '';
$crumbs  = $crumbs  ?? [];
$actions = $actions ?? '';
?>
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <?php if ($crumbs): ?>
    <nav class="mb-1 flex items-center gap-1.5 text-xs text-slate-400">
      <?php foreach ($crumbs as $i => $c): ?>
        <?php if ($i > 0): ?><i class="fa-solid fa-chevron-right text-[8px]"></i><?php endif; ?>
        <span class="<?= $i === array_key_last($crumbs) ? 'text-slate-600 dark:text-slate-300' : '' ?>"><?= e($c) ?></span>
      <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($title !== ''): ?>
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white"><?= e($title) ?></h2>
    <?php endif; ?>
  </div>
  <?php if ($actions !== ''): ?>
  <div class="flex items-center gap-2"><?= $actions ?></div>
  <?php endif; ?>
</div>
