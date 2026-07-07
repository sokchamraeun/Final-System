<?php
declare(strict_types=1);
/* Receipts — Landing page with links to receipt generation pages. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'view_orders';
require POS_ROOT . '/middleware/permission.php';

$pageTitle    = 'Receipts';
$pageSubtitle = 'Print or download order receipts';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Receipts', 'crumbs' => ['Sales', 'Receipts']]);
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
  <a href="<?= e(root_url('receipt_print.php')) ?>" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-amber-50 text-xl text-brand group-hover:bg-amber-100 dark:bg-slate-800">
      <i class="fa-solid fa-print"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Print Receipt</h3>
    <p class="mt-1 text-sm text-slate-500">Send an order receipt to the thermal printer.</p>
  </a>
  <a href="<?= e(root_url('receipt_pdf.php')) ?>" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-red-50 text-xl text-red-500 group-hover:bg-red-100 dark:bg-slate-800">
      <i class="fa-solid fa-file-pdf"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-white">PDF Receipt</h3>
    <p class="mt-1 text-sm text-slate-500">Download a PDF version of the order receipt.</p>
  </a>
</div>

<?php component('layout/footer'); ?>
