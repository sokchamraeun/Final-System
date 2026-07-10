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
?><style>
.dark body { background: #0F172A !important; }
.dark .dark\:bg-slate-950 { background: #0F172A !important; }
.dark .dark\:bg-slate-900,
.dark .dark\:bg-slate-900\/90 { background: #111827 !important; }
.dark .dark\:bg-slate-800 { background: #1A2332 !important; }
.dark .dark\:bg-slate-800\/50 { background: rgba(26,35,50,.5) !important; }
.dark .dark\:border-slate-700,
.dark .dark\:border-slate-700\/60,
.dark .dark\:border-slate-800 { border-color: #23314D !important; }
.dark .dark\:text-slate-100,
.dark .dark\:text-slate-200 { color: #F8FAFC !important; }
.dark .dark\:text-slate-300,
.dark .dark\:text-slate-400 { color: #94A3B8 !important; }
.dark .dark\:text-slate-500 { color: #475569 !important; }
.dark .dark\:text-slate-600 { color: #475569 !important; }
.dark .dark\:divide-slate-800 > * + * { border-color: #23314D !important; }
.dark .dark\:hover\:bg-slate-800\/40:hover { background: rgba(26,35,50,.5) !important; }
</style>
<?php
component('layout/main',   ['title' => 'Orders', 'crumbs' => ['Sales', 'Orders']]);
component('orders/order-search', ['search' => $search, 'status' => $status]);
component('orders/order-table',  ['rows' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);
component('layout/footer');
