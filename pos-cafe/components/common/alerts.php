<?php
declare(strict_types=1);
/* Flash alert banner. Rendered by the layout when a flash exists.
   $flash = ['message' => ..., 'type' => success|error|info|warning] */
$flash = $flash ?? flash();
if (!$flash) return;

$styles = [
    'success' => ['bg-emerald-50 border-emerald-200 text-emerald-800', 'fa-circle-check'],
    'error'   => ['bg-red-50 border-red-200 text-red-800',             'fa-circle-exclamation'],
    'warning' => ['bg-amber-50 border-amber-200 text-amber-800',       'fa-triangle-exclamation'],
    'info'    => ['bg-blue-50 border-blue-200 text-blue-800',          'fa-circle-info'],
];
[$cls, $icon] = $styles[$flash['type']] ?? $styles['info'];
?>
<div class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium <?= $cls ?>">
  <i class="fa-solid <?= $icon ?> mt-0.5"></i>
  <span class="flex-1"><?= e($flash['message']) ?></span>
  <button type="button" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100" aria-label="Dismiss">
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>
