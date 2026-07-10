<?php
declare(strict_types=1);
/* Addon table. component('addons/addon-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-layer-group',
        'title'   => 'No addons',
        'message' => 'Create add-ons to offer extras on your products.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">ID</th>
        <th class="px-5 py-3">Image</th>
        <th class="px-5 py-3">Name</th>
        <th class="px-5 py-3">Price</th>
        <th class="px-5 py-3">Active</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200"><?= (int) ($r['addon_id'] ?? 0) ?></td>
        <td class="px-5 py-3">
          <?php if (!empty($r['image'])): ?>
            <img src="<?= e(root_url($r['image'])) ?>" alt="" class="h-10 w-10 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
          <?php else: ?>
            <span class="text-slate-400">—</span>
          <?php endif; ?>
        </td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['name'] ?? '') ?></td>
        <td class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-200">$<?= number_format((float) ($r['price'] ?? 0), 2) ?></td>
        <td class="px-5 py-3">
          <?php if (!empty($r['is_active'])): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
              <i class="fa-solid fa-check"></i> Active
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-800">Inactive</span>
          <?php endif; ?>
        </td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <a href="<?= e(url('pages/addons/edit.php?id=' . ((int) ($r['addon_id'] ?? 0)))) ?>" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            <i class="fa-solid fa-pen"></i> Edit
          </a>
          <button onclick="confirmDeleteAddon(<?= (int) ($r['addon_id'] ?? 0) ?>, '<?= e($r['name'] ?? '') ?>')" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
