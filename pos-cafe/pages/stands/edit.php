<?php
declare(strict_types=1);
/* Stands â€” edit (stands are auto-configured via STAND_COUNT, redirect to index). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stands';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/stands/edit.php?id=' . (int) input('id', 0)));
    }
    flash('Stands are configured via the system settings.', 'info');
    redirect(url('pages/stands/index.php'));
}

$id    = (int) input('id', 0);
$stand = (new Stand())->find($id);
if (!$stand) {
    flash('Stand not found.', 'error');
    redirect(url('pages/stands/index.php'));
}

$pageTitle = 'Edit Â· ' . $stand['label'];

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Stand', 'crumbs' => ['POS', 'Stands', 'Edit']]);
?>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <div class="flex flex-col items-center py-8 text-center">
    <div class="mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-blue-50 text-2xl text-blue-500 dark:bg-blue-500/10">
      <i class="fa-solid fa-circle-info"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200">Stands Are Auto-Configured</h3>
    <p class="mt-2 max-w-md text-sm text-slate-500">Stand numbers are managed via the <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono dark:bg-slate-800">STAND_COUNT</code> constant. To modify stands, update the stand count in the system settings.</p>
    <p class="mt-1 text-sm text-slate-500">Current: <strong><?= e($stand['label']) ?></strong> â€” <?= $stand['occupied'] ? 'Occupied' : 'Free' ?></p>
    <a href="<?= e(url('pages/stands/index.php')) ?>" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-arrow-left"></i> Back to Stands
    </a>
  </div>
</div>
<?php
component('layout/footer');
