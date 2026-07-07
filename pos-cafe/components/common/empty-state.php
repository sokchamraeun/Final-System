<?php
declare(strict_types=1);
/* Empty-state placeholder.
   component('common/empty-state', [
     'icon' => 'fa-mug-hot', 'title' => 'No products',
     'message' => 'Add your first product to get started.',
     'action' => '<a class="..." href="...">+ New</a>',
   ]); */
$icon    = $icon    ?? 'fa-box-open';
$title   = $title   ?? 'Nothing here yet';
$message = $message ?? '';
$action  = $action  ?? '';
?>
<div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center dark:border-slate-700 dark:bg-slate-900">
  <div class="mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-amber-50 text-2xl text-brand dark:bg-slate-800">
    <i class="fa-solid <?= e($icon) ?>"></i>
  </div>
  <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200"><?= e($title) ?></h3>
  <?php if ($message !== ''): ?><p class="mt-1 max-w-sm text-sm text-slate-500"><?= e($message) ?></p><?php endif; ?>
  <?php if ($action !== ''): ?><div class="mt-5"><?= $action ?></div><?php endif; ?>
</div>
