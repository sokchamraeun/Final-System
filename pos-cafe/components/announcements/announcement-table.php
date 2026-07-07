<?php
declare(strict_types=1);
$announcements = $announcements ?? [];
$canManage     = $canManage     ?? false;

if (!$announcements) {
    component('common/empty-state', [
        'icon'    => 'fa-bullhorn',
        'title'   => 'No announcements found',
        'message' => 'Try a different search or create a new announcement.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/announcements/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Announcement</a>'
            : '',
    ]);
    return;
}

$typeBadges = [
    'info'      => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'warning'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'promotion' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'alert'     => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
];
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <tr>
        <th class="px-5 py-3">Title</th>
        <th class="px-5 py-3">Type</th>
        <th class="px-5 py-3">Expires at</th>
        <th class="px-5 py-3">Status</th>
        <th class="px-5 py-3">Created at</th>
        <?php if ($canManage): ?>
        <th class="px-5 py-3 text-right">Actions</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($announcements as $a): ?>
      <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
        <td class="px-5 py-4 font-medium text-slate-800 dark:text-white"><?= e($a['title']) ?></td>
        <td class="px-5 py-4">
          <span class="inline-block rounded-full px-3 py-0.5 text-xs font-semibold <?= $typeBadges[$a['type']] ?? $typeBadges['info'] ?>">
            <?= e(ucfirst($a['type'])) ?>
          </span>
        </td>
        <td class="px-5 py-4 text-slate-500"><?= e($a['expires_at'] ?? '—') ?></td>
        <td class="px-5 py-4">
          <?php if ((int) ($a['is_active'] ?? 0) === 1): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
              <i class="fa-solid fa-circle text-[6px]"></i> Active
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-3 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-400">
              <i class="fa-solid fa-circle text-[6px]"></i> Inactive
            </span>
          <?php endif; ?>
        </td>
        <td class="px-5 py-4 text-slate-500"><?= e($a['created_at']) ?></td>
        <?php if ($canManage): ?>
        <td class="px-5 py-4 text-right">
          <a href="<?= e(url('pages/announcements/edit.php?id=' . (int) $a['id'])) ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
            <i class="fa-solid fa-pen"></i> Edit
          </a>
          <button type="button" onclick="confirmDeleteAnnouncement(<?= (int) $a['id'] ?>, <?= json_encode(e($a['title'])) ?>)" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/30">
            <i class="fa-solid fa-trash-can"></i> Delete
          </button>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
