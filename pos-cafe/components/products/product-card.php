<?php
declare(strict_types=1);
/* Single product card. component('products/product-card', ['p' => $row, 'canManage' => can('products')]);
   $p columns: product_id, name, price, image, category, category_name,
   description, badge_text, has_sizes, is_available. */
$p         = $p         ?? [];
$canManage = $canManage ?? false;
$available = (int) ($p['is_available'] ?? 1) === 1;
?>
<div class="group rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
  <div class="p-3 pb-0">
    <div class="relative aspect-square overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
      <?php if (!empty($p['image'])): ?>
        <img src="<?= e(ROOT_URL . '/' . $p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"
             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 <?= $available ? '' : 'grayscale' ?>">
      <?php else: ?>
        <div class="grid h-full w-full place-items-center text-4xl text-slate-300"><i class="fa-solid fa-mug-hot"></i></div>
      <?php endif; ?>

      <?php if (!empty($p['badge_text'])): ?>
        <span class="absolute left-3 top-3 rounded-full bg-brand px-3 py-1 text-xs font-bold text-white shadow"><?= e($p['badge_text']) ?></span>
      <?php endif; ?>
      <?php if (!$available): ?>
        <span class="absolute right-3 top-3 rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white">Hidden</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="p-4">
    <div class="mb-1 flex items-start justify-between gap-2">
      <h3 class="font-bold leading-tight text-slate-800 dark:text-white"><?= e($p['name']) ?></h3>
      <span class="shrink-0 text-lg font-extrabold text-brand"><?= money($p['price']) ?></span>
    </div>
    <?php if (!empty($p['category_name'] ?? $p['category'])): ?>
      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-800">
        <i class="fa-solid fa-tag text-[10px]"></i><?= e($p['category_name'] ?? $p['category']) ?>
      </span>
    <?php endif; ?>
    <?php if (!empty($p['description'])): ?>
      <p class="mt-2 line-clamp-2 text-sm text-slate-500"><?= e($p['description']) ?></p>
    <?php endif; ?>

    <?php if ($canManage): ?>
    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
      <button type="button" onclick="openEditProductModal(<?= (int) $p['product_id'] ?>)"
         class="flex-1 rounded-xl bg-slate-100 py-2 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">
        <i class="fa-solid fa-pen"></i> Edit
      </button>
      <button type="button"
              onclick="toggleProduct(<?= (int) $p['product_id'] ?>, this)"
              class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800"
              title="<?= $available ? 'Hide' : 'Show' ?>">
        <i class="fa-solid <?= $available ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
      </button>
      <button type="button"
              onclick="confirmDeleteProduct(<?= (int) $p['product_id'] ?>, <?= e(json_encode($p['name'])) ?>)"
              class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 text-red-500 transition hover:bg-red-50 dark:border-slate-700 dark:hover:bg-red-500/10"
              title="Delete">
        <i class="fa-solid fa-trash-can"></i>
      </button>
    </div>
    <?php endif; ?>
  </div>
</div>
