<?php
declare(strict_types=1);
/* Standalone breadcrumbs. component('common/breadcrumbs', ['crumbs' => ['Catalog','Products']]); */
$crumbs = $crumbs ?? [];
if (!$crumbs) return;
?>
<nav class="flex items-center gap-1.5 text-xs text-slate-400">
  <?php foreach ($crumbs as $i => $c): ?>
    <?php if ($i > 0): ?><i class="fa-solid fa-chevron-right text-[8px]"></i><?php endif; ?>
    <span class="<?= $i === array_key_last($crumbs) ? 'font-medium text-slate-600 dark:text-slate-300' : '' ?>"><?= e($c) ?></span>
  <?php endforeach; ?>
</nav>
