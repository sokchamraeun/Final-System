<?php
declare(strict_types=1);
/* Stands â€” create (stands are auto-configured via STAND_COUNT, redirect to index). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stands';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/stands/create.php'));
    }
    flash('Stands are configured via the system settings. Use STAND_COUNT to manage stand numbers.', 'info');
    redirect(url('pages/stands/index.php'));
}

$pageTitle = 'New Stand';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Stand', 'crumbs' => ['POS', 'Stands', 'New']]);
?>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
  <div class="flex flex-col items-center py-8 text-center">
    <div class="mb-4 grid h-16 w-16 place-items-center rounded-2xl bg-blue-50 text-2xl text-blue-500 dark:bg-blue-500/10">
      <i class="fa-solid fa-circle-info"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-700 dark:text-slate-200">Stands Are Auto-Configured</h3>
    <p class="mt-2 max-w-md text-sm text-slate-500">Stand numbers are managed via the <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs font-mono dark:bg-slate-800">STAND_COUNT</code> constant in the system settings. To add or remove stands, update the stand count in the configuration.</p>
    <a href="<?= e(url('pages/stands/index.php')) ?>" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600">
      <i class="fa-solid fa-arrow-left"></i> Back to Stands
    </a>
  </div>
</div>
<?php
component('layout/footer');
