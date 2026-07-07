<?php
declare(strict_types=1);
/* Dashboard KPI cards. component('dashboard/dashboard-stats', []); */
?>
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Products</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-800 dark:text-white"><?= (new Product())->count() ?></p>
      </div>
      <div class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-xl text-brand dark:bg-slate-800">
        <i class="fa-solid fa-mug-hot"></i>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Employees</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-800 dark:text-white"><?= Database::instance()->scalar("SELECT COUNT(*) FROM employees") ?></p>
      </div>
      <div class="grid h-12 w-12 place-items-center rounded-xl bg-blue-50 text-xl text-blue-500 dark:bg-slate-800">
        <i class="fa-solid fa-users"></i>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Today's Orders</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-800 dark:text-white"><?= (int) Database::instance()->scalar("SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()") ?></p>
      </div>
      <div class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-xl text-emerald-500 dark:bg-slate-800">
        <i class="fa-solid fa-receipt"></i>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">Low Stock Items</p>
        <p class="mt-1 text-2xl font-extrabold text-slate-800 dark:text-white"><?= count((new Ingredient())->lowStock()) ?></p>
      </div>
      <div class="grid h-12 w-12 place-items-center rounded-xl bg-red-50 text-xl text-red-500 dark:bg-slate-800">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
    </div>
  </div>
</div>
