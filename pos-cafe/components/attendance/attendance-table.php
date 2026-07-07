<?php
declare(strict_types=1);
$records = $records ?? [];

if (!$records) {
    component('common/empty-state', [
        'icon'    => 'fa-clock',
        'title'   => 'No attendance records found',
        'message' => 'Try a different search or date range.',
    ]);
    return;
}
?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full text-left text-sm">
    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-900/50">
      <tr>
        <th class="px-5 py-3">Username</th>
        <th class="px-5 py-3">Date</th>
        <th class="px-5 py-3">Clock In</th>
        <th class="px-5 py-3">Clock Out</th>
        <th class="px-5 py-3">Hours Worked</th>
        <th class="px-5 py-3">Status</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
      <?php foreach ($records as $r): ?>
      <?php
        $clockIn  = $r['clock_in']  ?? null;
        $clockOut = $r['clock_out'] ?? null;
        $hours    = $r['hours_worked'] ?? null;
        $isActive = ($clockIn && !$clockOut);
      ?>
      <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
        <td class="px-5 py-4 font-medium text-slate-800 dark:text-white"><?= e($r['username']) ?></td>
        <td class="px-5 py-4 text-slate-500"><?= e($r['date']) ?></td>
        <td class="px-5 py-4 text-slate-500"><?= $clockIn ? e(date('h:i A', strtotime($clockIn))) : '—' ?></td>
        <td class="px-5 py-4 text-slate-500"><?= $clockOut ? e(date('h:i A', strtotime($clockOut))) : ($isActive ? '—' : '—') ?></td>
        <td class="px-5 py-4 text-slate-500"><?= $hours !== null ? e(number_format((float) $hours, 1)) . 'h' : ($isActive ? '<span class="text-amber-600">In progress</span>' : '—') ?></td>
        <td class="px-5 py-4">
          <?php if ($isActive): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
              <i class="fa-solid fa-play"></i> Clocked In
            </span>
          <?php elseif ($clockIn && $clockOut): ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
              <i class="fa-solid fa-check"></i> Completed
            </span>
          <?php else: ?>
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-3 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-400">
              <i class="fa-solid fa-minus"></i> Not started
            </span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
