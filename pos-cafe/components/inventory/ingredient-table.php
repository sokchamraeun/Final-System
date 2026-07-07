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
<style>
/* Times New Roman font */
.ingredient-table {
    font-family: 'Times New Roman', Times, serif;
}

.ingredient-table th {
    font-weight: 600;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    font-size: 0.7rem;
    font-family: 'Times New Roman', Times, serif;
}

.ingredient-table td {
    font-size: 0.875rem;
    line-height: 1.6;
    font-family: 'Times New Roman', Times, serif;
}

.ingredient-table .font-mono {
    font-family: 'Times New Roman', Times, serif;
    font-size: 0.8125rem;
}

.ingredient-table input,
.ingredient-table button,
.ingredient-table a {
    font-family: 'Times New Roman', Times, serif;
}
</style>

<div class="ingredient-table overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-lg shadow-slate-200/50 dark:border-slate-700/60 dark:bg-slate-900 dark:shadow-slate-900/30">
  
  <!-- Table Header -->
 

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead>
        <tr class="border-b border-slate-200/60 bg-slate-50/80 dark:border-slate-700/60 dark:bg-slate-800/40">
          <th class="w-16 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">#</th>
          <th class="min-w-[150px] px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ingredient</th>
          <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Unit</th>
          <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Stock</th>
          <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Min Level</th>
          <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Cost/Unit</th>
          <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
          <?php if ($canManage): ?>
          <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800/80">
        <?php foreach ($rows as $index => $r): ?>
        <?php 
        $stockQty = (float) ($r['stock_quantity'] ?? 0);
        $reorderLevel = (float) ($r['minimum_stock'] ?? 0);
        $isLow = $stockQty <= $reorderLevel && $stockQty > 0;
        $isOutOfStock = $stockQty <= 0;
        
        // Status with icons
        if ($isOutOfStock) {
            $statusColor = 'rose';
            $statusIcon = 'fa-circle-xmark';
            $statusLabel = 'Out of Stock';
        } elseif ($isLow) {
            $statusColor = 'amber';
            $statusIcon = 'fa-triangle-exclamation';
            $statusLabel = 'Low Stock';
        } else {
            $statusColor = 'emerald';
            $statusIcon = 'fa-circle-check';
            $statusLabel = 'In Stock';
        }
        
        // Progress bar for stock level
        $stockPercent = $reorderLevel > 0 ? min(100, ($stockQty / ($reorderLevel * 3)) * 100) : 100;
        $progressColor = $isOutOfStock ? 'rose' : ($isLow ? 'amber' : 'emerald');
        ?>
        <tr class="group transition-all duration-200 hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
          <!-- ID -->
          <td class="px-4 py-3.5 text-center text-xs font-medium text-slate-400">
            <?= $index + 1 ?>
          </td>
          
          <!-- Name with icon -->
          <td class="px-4 py-3.5">
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-600 dark:from-indigo-500/20 dark:to-purple-500/20 dark:text-indigo-400">
                <i class="fa-solid fa-cube text-xs"></i>
              </div>
              <span class="font-medium text-slate-700 dark:text-slate-200">
                <?= e($r['ingredient_name'] ?? '') ?>
              </span>
            </div>
          </td>
          
          <!-- Unit -->
          <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium dark:bg-slate-800">
              <?= e($r['unit'] ?? '—') ?>
            </span>
          </td>
          
          <!-- Stock Quantity with progress bar -->
          <td class="px-4 py-3.5">
            <div class="flex flex-col items-end gap-1">
              <span class="font-mono text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-200">
                <?= number_format($stockQty, 0) ?>
              </span>
              <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div 
                  class="h-full rounded-full transition-all duration-500 <?= $progressColor === 'rose' ? 'bg-rose-500' : ($progressColor === 'amber' ? 'bg-amber-500' : 'bg-emerald-500') ?>"
                  style="width: <?= min(100, max(0, $stockPercent)) ?>%"
                ></div>
              </div>
            </div>
          </td>
          
          <!-- Reorder Level -->
          <td class="px-4 py-3.5 text-right font-mono text-sm tabular-nums text-slate-500 dark:text-slate-400">
            <?= number_format($reorderLevel, 0) ?>
          </td>
          
          <!-- Cost/Unit -->
          <td class="px-4 py-3.5 text-right font-mono text-sm font-medium tabular-nums text-slate-600 dark:text-slate-300">
            <?= isset($r['cost_price']) ? '$' . number_format((float) $r['cost_price'], 2) : '—' ?>
          </td>
          
          <!-- Status Badge -->
          <td class="px-4 py-3.5 text-center">
            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium
              <?php 
              switch($statusColor) {
                  case 'rose': 
                      echo 'bg-rose-50 text-rose-700 ring-1 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20';
                      break;
                  case 'amber': 
                      echo 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20';
                      break;
                  case 'emerald': 
                      echo 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20';
                      break;
              }
              ?>
            ">
              <i class="fa-solid <?= $statusIcon ?> text-[10px]"></i>
              <?= $statusLabel ?>
            </span>
          </td>
          
          <!-- Actions -->
          <?php if ($canManage): ?>
          <td class="px-4 py-3.5">
            <div class="flex items-center justify-center gap-1.5 opacity-70 transition-opacity group-hover:opacity-100">
              <button 
                onclick="stockInIngredient(<?= (int) ($r['ingredient_id'] ?? 0) ?>)" 
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-emerald-600 transition-all hover:bg-emerald-50 hover:text-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-500/10"
                title="Stock In">
                <i class="fa-solid fa-arrow-down text-[10px]"></i>
                Stock
              </button>
              <a 
                href="<?= e(url('pages/inventory/edit.php?id=' . ((int) ($r['ingredient_id'] ?? 0)))) ?>" 
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition-all hover:bg-indigo-50 hover:text-indigo-700 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
                title="Edit">
                <i class="fa-solid fa-pen text-[10px]"></i>
              </a>
              <button 
                onclick="confirmDeleteIngredient(<?= (int) ($r['ingredient_id'] ?? 0) ?>, '<?= e($r['ingredient_name'] ?? '') ?>')" 
                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition-all hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-500/10"
                title="Delete">
                <i class="fa-solid fa-trash-can text-[10px]"></i>
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
      <span class="text-xs text-slate-500" style="font-family: 'Times New Roman', Times, serif;">
        Showing <strong class="text-slate-700 dark:text-slate-300"><?= count($rows) ?></strong> ingredients
      </span>
      <div class="flex items-center gap-2 text-xs text-slate-500" style="font-family: 'Times New Roman', Times, serif;">
        <span class="flex items-center gap-1">
          <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
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
      <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800" style="font-family: 'Times New Roman', Times, serif;">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <span class="text-xs text-slate-500" style="font-family: 'Times New Roman', Times, serif;">Page <?= $page ?? 1 ?> of <?= $totalPages ?? 1 ?></span>
      <button class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800" style="font-family: 'Times New Roman', Times, serif;">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
    <?php endif; ?>
  </div>
</div>