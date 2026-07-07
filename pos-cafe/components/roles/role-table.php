<?php
declare(strict_types=1);
/* Role table. component('roles/role-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-shield-halved',
        'title'   => 'No roles',
        'message' => 'Roles define what users can do in the system.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">Name</th>
        <th class="px-5 py-3">Slug</th>
        <th class="px-5 py-3">Color</th>
        <th class="px-5 py-3">Description</th>
        <th class="px-5 py-3">System</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3">
          <span class="inline-flex items-center gap-2 font-medium text-slate-700 dark:text-slate-200">
            <?php if (!empty($r['color'])): ?>
            <span class="inline-block h-3 w-3 rounded-full" style="background: <?= e($r['color']) ?>"></span>
            <?php endif; ?>
            <?= e($r['name'] ?? '') ?>
          </span>
        </td>
        <td class="px-5 py-3"><code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs dark:bg-slate-800"><?= e($r['slug'] ?? '') ?></code></td>
        <td class="px-5 py-3 text-slate-500"><?= e($r['color'] ?? '—') ?></td>
        <td class="max-w-xs truncate px-5 py-3 text-slate-500"><?= e($r['description'] ?? '') ?></td>
        <td class="px-5 py-3">
          <?php if (!empty($r['is_system'])): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
              <i class="fa-solid fa-lock"></i> System
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-800">Custom</span>
          <?php endif; ?>
        </td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <div class="flex items-center justify-end gap-1">
            <a href="<?= e(url('pages/roles/edit.php?id=' . (int) ($r['id'] ?? 0))) ?>"
               class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Edit">
              <i class="fa-solid fa-pen"></i>
            </a>
            <?php if (empty($r['is_system'])): ?>
            <button type="button"
                    onclick="confirmDeleteRole(<?= (int) ($r['id'] ?? 0) ?>, <?= e(json_encode($r['name'] ?? '')) ?>)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10" title="Delete">
              <i class="fa-solid fa-trash-can"></i>
            </button>
            <?php endif; ?>
          </div>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
