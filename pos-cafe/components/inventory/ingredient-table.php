<?php
declare(strict_types=1);
/* Ingredient table. component('inventory/ingredient-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;
$page      = $page      ?? 1;
$perPage   = 100;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-egg',
        'title'   => 'No ingredients',
        'message' => 'Add ingredients to start tracking your inventory.',
    ]);
    return;
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

<style>
.ingredient-table {
    font-family: 'Quicksand', sans-serif;
}
</style>

<div class="ingredient-table overflow-hidden rounded-3xl border border-slate-200/60 bg-white shadow-lg shadow-slate-200/50 dark:border-slate-700/60 dark:bg-slate-900 dark:shadow-slate-900/30">

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr class="bg-teal-800 dark:bg-teal-900">
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">ID</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Name</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Unit</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Stock Quantity</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Reorder Level</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Cost/Unit</th>
          <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-white">Status</th>
          <?php if ($canManage): ?>
          <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-white">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        <?php foreach ($rows as $index => $r): ?>
        <?php
        $stockQty = (float) ($r['stock_quantity'] ?? 0);
        $reorderLevel = (float) ($r['minimum_stock'] ?? 0);
        $isLow = $stockQty <= $reorderLevel && $stockQty > 0;
        $isOutOfStock = $stockQty <= 0;

        if ($isOutOfStock) {
            $statusColor = 'rose';
            $statusLabel = 'Out of Stock';
        } elseif ($isLow) {
            $statusColor = 'amber';
            $statusLabel = 'Low Stock';
        } else {
            $statusColor = 'teal';
            $statusLabel = 'In Stock';
        }

        $initial = strtoupper(substr($r['ingredient_name'] ?? '?', 0, 1));
        ?>
        <tr class="transition-colors duration-150 hover:bg-slate-50/70 dark:hover:bg-slate-800/40">

          <!-- ID -->
          <td class="px-6 py-4 text-sm font-semibold text-teal-700 dark:text-teal-400">
            <?= $index + 1 ?>
          </td>

          <!-- Name with avatar -->
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <?php if (!empty($r['image_url'])): ?>
                <img src="<?= e($r['image_url']) ?>" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
              <?php else: ?>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-teal-200 bg-teal-50 text-sm font-bold text-teal-700 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300">
                  <?= e($initial) ?>
                </span>
              <?php endif; ?>
              <span class="font-semibold text-slate-700 dark:text-slate-200">
                <?= e($r['ingredient_name'] ?? '') ?>
              </span>
            </div>
          </td>

          <!-- Unit -->
          <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
            <?= e($r['unit'] ?? '—') ?>
          </td>

          <!-- Stock Quantity -->
          <td class="px-6 py-4 text-sm font-medium tabular-nums text-slate-700 dark:text-slate-200">
            <?= number_format($stockQty, 2) ?>
          </td>

          <!-- Reorder Level -->
          <td class="px-6 py-4 text-sm tabular-nums text-slate-500 dark:text-slate-400">
            <?= number_format($reorderLevel, 2) ?>
          </td>

          <!-- Cost/Unit -->
          <td class="px-6 py-4 text-sm font-medium tabular-nums text-slate-700 dark:text-slate-200">
            <?= isset($r['cost_price']) ? '$' . number_format((float) $r['cost_price'], 2) : '—' ?>
          </td>

          <!-- Status Badge -->
          <td class="px-6 py-4">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
              <?php
              switch ($statusColor) {
                  case 'rose':
                      echo 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-500/10 dark:text-rose-400';
                      break;
                  case 'amber':
                      echo 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-400';
                      break;
                  default:
                      echo 'border border-teal-200 bg-teal-50 text-teal-700 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300';
              }
              ?>
            ">
              <?= $statusLabel ?>
            </span>
          </td>

          <!-- Actions -->
          <?php if ($canManage): ?>
          <td class="px-6 py-4">
            <div class="flex items-center justify-end gap-2">

              <button
                onclick="stockInIngredient(<?= (int) ($r['ingredient_id'] ?? 0) ?>)"
                class="rounded-full border border-teal-200 bg-teal-50 px-3.5 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300 dark:hover:bg-teal-500/20">
                Stock In
              </button>

              <a
                href="<?= e(url('pages/inventory/edit.php?id=' . ((int) ($r['ingredient_id'] ?? 0)))) ?>"
                class="rounded-full border border-teal-200 bg-teal-50 px-3.5 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-800 dark:bg-teal-500/10 dark:text-teal-300 dark:hover:bg-teal-500/20">
                Edit
              </a>

              <button
                onclick="confirmDeleteIngredient(<?= (int) ($r['ingredient_id'] ?? 0) ?>, '<?= e($r['ingredient_name'] ?? '') ?>')"
                class="rounded-full border border-rose-200 bg-rose-50 px-3.5 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-800 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                Delete
              </button>

            </div>
          </td>
          <?php endif; ?>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Table Footer -->
  <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-200/60 bg-slate-50/50 px-6 py-4 dark:border-slate-700/60 dark:bg-slate-800/30">
    <div class="flex items-center gap-4">
      <span class="text-xs text-slate-500">
        Showing <strong class="text-slate-700 dark:text-slate-300"><?= count($rows) ?></strong> ingredients
      </span>
      <div class="flex items-center gap-3 text-xs text-slate-500">
        <span class="flex items-center gap-1">
          <span class="inline-block h-2 w-2 rounded-full bg-teal-500"></span>
          In Stock
        </span>
        <span class="flex items-center gap-1">
          <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
          Low
        </span>
        <span class="flex items-center gap-1">
          <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>
          Out
        </span>
      </div>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="flex items-center gap-2">
      <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <span class="text-xs text-slate-500">Page <?= $page ?? 1 ?> of <?= $totalPages ?? 1 ?></span>
      <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
    <?php endif; ?>
  </div>
</div>