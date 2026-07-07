<?php
declare(strict_types=1);
/* Inline loading spinner. component('common/loading-spinner', ['label' => 'Loading…']); */
$label = $label ?? 'Loading…';
?>
<div class="flex items-center justify-center gap-2 py-10 text-sm text-slate-500">
  <i class="fa-solid fa-spinner fa-spin text-brand"></i>
  <span><?= e($label) ?></span>
</div>
