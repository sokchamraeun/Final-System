<?php
declare(strict_types=1);
$p         = $p         ?? [];
$canManage = $canManage ?? false;
$no        = (int) ($no ?? 0);
$available = (int) ($p['is_available'] ?? 1) === 1;
?>
<tr class="border-b border-slate-100 transition hover:bg-slate-50/50 dark:border-slate-800 dark:hover:bg-slate-900/50">
  <td class="whitespace-nowrap px-4 py-3 text-center text-sm font-medium text-slate-400"><?= $no ?></td>
  <td class="whitespace-nowrap px-3 py-3">
    <div class="flex items-center gap-3">
      <div class="h-8 w-8 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800">
        <?php if (!empty($p['image'])): ?>
          <img src="<?= e(ROOT_URL . '/' . $p['image']) ?>" alt="" loading="lazy" class="h-full w-full object-cover <?= $available ? '' : 'grayscale' ?>">
        <?php else: ?>
          <div class="grid h-full w-full place-items-center text-sm text-slate-300"><i class="fa-solid fa-mug-hot"></i></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="flex items-center gap-1.5">
          <span class="font-semibold text-slate-800 dark:text-white"><?= e($p['name']) ?></span>
          <?php if (!empty($p['badge_text'])): ?>
            <span class="rounded-full bg-brand/10 px-2 py-0.5 text-[10px] font-bold text-brand"><?= e($p['badge_text']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </td>
  <td class="whitespace-nowrap px-2 py-3 text-sm text-slate-500 dark:text-slate-400">
    <?php if (!empty($p['category_name'])): ?>
      <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
        <i class="fa-solid fa-tag text-[9px]"></i><?= e($p['category_name']) ?>
      </span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
    <?php if (!empty($p['sizes'])): ?>
      <span class="text-xs font-medium text-slate-600 dark:text-slate-300"><?= e($p['sizes']) ?></span>
    <?php elseif ((int)($p['has_sizes'] ?? 0) === 1): ?>
      <span class="text-xs text-slate-400">Has sizes</span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
    <?php if (!empty($p['addon_names'])): ?>
      <span class="text-xs text-slate-600 dark:text-slate-300"><?= e($p['addon_names']) ?></span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
    <?php if (!empty($p['milk_levels'])): ?>
      <span class="text-xs text-slate-600 dark:text-slate-300"><?= e($p['milk_levels']) ?></span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3">
    <?php if (!empty($p['sugar_levels'])): ?>
      <span class="text-xs text-slate-600 dark:text-slate-300"><?= e($p['sugar_levels']) ?></span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3">
    <?php if (!empty($p['ice_levels'])): ?>
      <span class="text-xs text-slate-600 dark:text-slate-300"><?= e($p['ice_levels']) ?></span>
    <?php else: ?>
      <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3">
    <?php if ($available): ?>
      <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
        <i class="fa-solid fa-circle text-[6px]"></i> Active
      </span>
    <?php else: ?>
      <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-400">
        <i class="fa-solid fa-circle text-[6px]"></i> Hidden
      </span>
    <?php endif; ?>
  </td>
  <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-slate-400 dark:text-slate-500">
    <span class="text-slate-300 dark:text-slate-600">&mdash;</span>
  </td>
  <?php if ($canManage): ?>
  <td class="whitespace-nowrap px-4 py-3">
    <div class="flex items-center gap-1">
      <button type="button" onclick="openEditProductModal(<?= (int) $p['product_id'] ?>)"
         class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
         title="Edit">
        <i class="fa-solid fa-pen text-xs"></i>
      </button>
      <button type="button" onclick="toggleProduct(<?= (int) $p['product_id'] ?>, this)"
              class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
              title="<?= $available ? 'Hide' : 'Show' ?>">
        <i class="fa-solid <?= $available ? 'fa-eye' : 'fa-eye-slash' ?> text-xs"></i>
      </button>
      <button type="button" onclick="confirmDeleteProduct(<?= (int) $p['product_id'] ?>, <?= e(json_encode($p['name'])) ?>)"
              class="grid h-8 w-8 place-items-center rounded-lg text-red-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10"
              title="Delete">
        <i class="fa-solid fa-trash-can text-xs"></i>
      </button>
    </div>
  </td>
  <?php endif; ?>
</tr>
