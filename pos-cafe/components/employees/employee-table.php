<?php
declare(strict_types=1);
/* Employee table. component('employees/employee-table', ['rows' => $rows, 'canManage' => bool]); */
$rows      = $rows      ?? [];
$canManage = $canManage ?? false;
$page      = (int) ($page    ?? 1);
$perPage   = (int) ($perPage ?? PER_PAGE);

if (!$rows) {
    component('common/empty-state', [
        'icon'    => 'fa-users',
        'title'   => 'No employees',
        'message' => 'Add your staff members to get started.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-sm">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-medium text-slate-500 dark:border-slate-800 dark:bg-slate-900">
        <th class="px-5 py-3">No#</th>
        <th class="px-5 py-3">Name</th>
        <th class="px-5 py-3">User Access</th>
        <th class="px-5 py-3">Phone</th>
        <th class="px-5 py-3">Job Title</th>
        <th class="px-5 py-3">Salary</th>
        <th class="px-5 py-3">Shift</th>
        <?php if ($canManage): ?><th class="px-5 py-3 text-right">Actions</th><?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($rows as $i => $r): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
        <td class="px-5 py-3 text-slate-500 dark:text-slate-400"><?= ($page - 1) * $perPage + $i + 1 ?></td>
        <td class="px-5 py-3">
          <div class="flex items-center gap-3">
            <?php if (!empty($r['photo'])): ?>
            <img src="<?= e(root_url($r['photo'])) ?>" alt="" class="h-9 w-9 shrink-0 rounded-full border border-slate-200 object-cover dark:border-slate-700">
            <?php else: ?>
            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand/10 text-xs font-bold text-brand"><?= e(strtoupper(substr((string) ($r['name'] ?? '?'), 0, 1))) ?></div>
            <?php endif; ?>
            <span class="font-medium text-slate-700 dark:text-slate-200"><?= e($r['name'] ?? '') ?></span>
          </div>
        </td>
        <td class="px-5 py-3">
          <?php if (!empty($r['user_id'])): ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
            <i class="fa-solid fa-check-circle"></i> Yes
          </span>
          <?php else: ?>
          <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-400 dark:bg-slate-800 dark:text-slate-500">
            <i class="fa-solid fa-xmark"></i> No
          </span>
          <?php endif; ?>
        </td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['phone'] ?? '—') ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['job_title'] ?? '—') ?></td>
        <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= isset($r['salary']) ? money($r['salary']) : '—' ?></td>
        <td class="px-5 py-3">
          <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
            <?= e($r['shift'] ?? '—') ?>
          </span>
        </td>
        <?php if ($canManage): ?>
        <td class="px-5 py-3 text-right">
          <a href="<?= e(url('pages/employees/edit.php?id=' . ((int) ($r['employee_id'] ?? 0)))) ?>" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
            <i class="fa-solid fa-pen"></i> Edit
          </a>
          <button onclick="confirmDeleteEmployee(<?= (int) ($r['employee_id'] ?? 0) ?>, '<?= e($r['name'] ?? '') ?>')" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
            <i class="fa-solid fa-trash-can"></i>
          </button>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
