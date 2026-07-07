<?php
declare(strict_types=1);
/* Pagination bar.
   component('common/pagination', [
     'page' => 2, 'perPage' => 12, 'total' => 57, 'baseUrl' => url('pages/products/index.php?search=x')
   ]); */
$page    = max(1, (int) ($page ?? 1));
$perPage = max(1, (int) ($perPage ?? PER_PAGE));
$total   = (int) ($total ?? 0);
$baseUrl = $baseUrl ?? '';
$pages   = (int) ceil($total / $perPage);
if ($pages <= 1) return;

$link = static function (int $p) use ($baseUrl): string {
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    return e($baseUrl . $sep . 'page=' . $p);
};
$from = ($page - 1) * $perPage + 1;
$to   = min($total, $page * $perPage);
?>
<div class="mt-6 flex flex-col items-center justify-between gap-3 sm:flex-row">
  <p class="text-sm text-slate-500">Showing <?= $from ?>–<?= $to ?> of <?= $total ?></p>
  <div class="flex items-center gap-1">
    <a href="<?= $page > 1 ? $link($page - 1) : '#' ?>"
       class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800 <?= $page <= 1 ? 'pointer-events-none opacity-40' : '' ?>">
      <i class="fa-solid fa-chevron-left"></i>
    </a>
    <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
      <a href="<?= $link($p) ?>"
         class="grid h-9 min-w-9 place-items-center rounded-lg px-2 text-sm font-medium <?= $p === $page ? 'bg-brand text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800' ?>">
        <?= $p ?>
      </a>
    <?php endfor; ?>
    <a href="<?= $page < $pages ? $link($page + 1) : '#' ?>"
       class="grid h-9 w-9 place-items-center rounded-lg border border-slate-200 text-sm text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800 <?= $page >= $pages ? 'pointer-events-none opacity-40' : '' ?>">
      <i class="fa-solid fa-chevron-right"></i>
    </a>
  </div>
</div>
