<?php
declare(strict_types=1);
/* Order detail — single order view (read-only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'view_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'orders';

$id    = (int) input('id', 0);
$model = new Order();
$order = $model->find($id);

if (!$order) {
    flash('Order not found.', 'error');
    redirect(url('pages/orders/index.php'));
}

$items = $model->items($id);

$pageTitle = 'Order #' . (int) $order['daily_order_no'];
$backBtn   = '<a href="' . e(url('pages/orders/index.php')) . '" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"><i class="fa-solid fa-arrow-left"></i> Back to Orders</a>';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $order['customer_name']]);
component('layout/main',   [
    'title'   => $pageTitle,
    'crumbs'  => ['Sales', 'Orders', '#' . $order['daily_order_no']],
    'actions' => $backBtn,
]);
component('orders/order-detail', ['order' => $order, 'items' => $items]);
component('layout/footer');
