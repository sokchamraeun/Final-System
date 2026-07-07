<?php
declare(strict_types=1);
$suppliers = $suppliers ?? [];
$canManage = $canManage ?? false;

if (!$suppliers) {
    component('common/empty-state', [
        'icon'    => 'fa-truck',
        'title'   => 'No suppliers found',
        'message' => 'Try a different search or add a new supplier.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/suppliers/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Supplier</a>'
            : '',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">Name</th>
        <th class="px-5 py-3">Contact Person</th>
        <th class="px-5 py-3">Phone</th>
        <th class="px-5 py-3">Email</th>
        <?php if ($canManage): ?>
        <th class="px-5 py-3 text-right">Actions</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($suppliers as $s): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 font-medium text-slate-800 dark:text-white"><?= e($s['name']) ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($s['contact_person'] ?? '—') ?></td>
        <td class="px-5 py-3 text-slate-500"><?= e($s['phone'] ?? '—') ?></td>
        <td class="px-5 py-3 text-slate-500"><?= e($s['email'] ?? '—') ?></td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <div class="flex items-center justify-end gap-1">
            <a href="<?= e(url('pages/suppliers/edit.php?id=' . (int) $s['supplier_id'])) ?>"
               class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </a>
            <button type="button"
                    onclick="confirmDeleteSupplier(<?= (int) $s['supplier_id'] ?>, <?= e(json_encode($s['name'])) ?>)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10" title="Delete">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
