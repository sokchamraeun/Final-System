<?php
declare(strict_types=1);
/* Payment — Landing page with links to payment processing pages. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

$pageTitle    = 'Payments';
$pageSubtitle = 'Manage order payments';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Payments', 'crumbs' => ['Sales', 'Payments']]);
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
  <a href="<?= e(root_url('payment.php')) ?>" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-emerald-50 text-xl text-emerald-500 group-hover:bg-emerald-100 dark:bg-slate-800">
      <i class="fa-solid fa-credit-card"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Process Payment</h3>
    <p class="mt-1 text-sm text-slate-500">Accept cash, card, or KHQR for an existing order.</p>
  </a>
  <a href="<?= e(root_url('payment_record.php')) ?>" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="mb-4 grid h-14 w-14 place-items-center rounded-xl bg-blue-50 text-xl text-blue-500 group-hover:bg-blue-100 dark:bg-slate-800">
      <i class="fa-solid fa-clock-rotate-left"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Payment Records</h3>
    <p class="mt-1 text-sm text-slate-500">View and search past payment transactions.</p>
  </a>
</div>

<?php component('layout/footer'); ?>
