<?php
declare(strict_types=1);
/* Orders — list / search / filter / paginate (read-only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'view_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'orders';
$canManage = can('view_orders');

$search = (string) input('search', '');
$status = (string) input('status', '');
$page   = max(1, (int) input('page', 1));

$model  = new Order();
$result = $model->paginate(['search' => $search, 'status' => $status], $page);

$pageTitle    = 'Orders';
$pageSubtitle = $result['total'] . ' order' . ($result['total'] === 1 ? '' : 's');

$baseUrl = url('pages/orders/index.php?search=' . urlencode($search) . '&status=' . urlencode($status));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Orders', 'crumbs' => ['Sales', 'Orders']]);
component('orders/order-search', ['search' => $search, 'status' => $status]);
component('orders/order-table',  ['rows' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);
component('layout/footer');
