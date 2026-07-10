<?php
declare(strict_types=1);
/* ============================================================
   Dashboard â€” KPI overview, kitchen queue, top sellers,
   recent orders, and role-aware quick-access tiles.
   ============================================================ */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

$navActive = 'dashboard';
$admin_name = $_SESSION['username'] ?? 'Admin';
$_is_mgr = in_array($_SESSION['role'] ?? '', ['admin', 'manager', 'supervisor']);

/* â”€â”€ Role info â”€â”€ */
$_roles_db = [];
$_rdb = $conn->query("SELECT slug, name, icon, color FROM roles ORDER BY is_system DESC, id ASC");
while ($_rdbr = $_rdb->fetch_assoc()) $_roles_db[$_rdbr['slug']] = $_rdbr;
$_cur_role = $_SESSION['role'] ?? 'staff';
$_cur_role_info = $_roles_db[$_cur_role] ?? null;
$_cur_role_name = $_cur_role_info['name'] ?? ucwords(str_replace('_', ' ', $_cur_role));
$_cur_role_color = $_cur_role_info['color'] ?? '#d1904b';

/* â”€â”€ Clock-in status â”€â”€ */
$_is_clocked_in = false;
$_clock_since   = null;
$_att_check = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($_att_check && $_att_check->num_rows > 0) {
    $_cs = $conn->prepare("SELECT clock_in FROM attendance WHERE user_id = ? AND date = CURDATE() AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
    $_cs->bind_param('i', $_SESSION['user_id']);
    $_cs->execute();
    $_crow = $_cs->get_result()->fetch_assoc();
    if ($_crow) { $_is_clocked_in = true; $_clock_since = date('g:i A', strtotime($_crow['clock_in'])); }
}

/* â”€â”€ Business date â”€â”€ */
$_now = new DateTime();
$business_date = (int)$_now->format("H") < 6
    ? (clone $_now)->modify("-1 day")->format("Y-m-d")
    : $_now->format("Y-m-d");

/* â”€â”€ Sales / KPI data â”€â”€ */
$sales_result    = $conn->query("SELECT IFNULL(SUM(total),0) AS total_sales FROM orders WHERE DATE(order_date)=CURDATE() AND status='Completed'");
$sales           = (float)($sales_result->fetch_assoc()['total_sales'] ?? 0);
$yesterday_result = $conn->query("SELECT IFNULL(SUM(total),0) AS yesterday_sales FROM orders WHERE DATE(order_date)=CURDATE()-INTERVAL 1 DAY AND status='Completed'");
$yesterday_sales = (float)($yesterday_result->fetch_assoc()['yesterday_sales'] ?? 0);
$sales_trend     = $yesterday_sales > 0 ? round(($sales - $yesterday_sales) / $yesterday_sales * 100, 1) : 0;
$trend_class     = $sales_trend >= 0 ? 'up' : 'down';
$trend_icon      = $sales_trend >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

$order_result = $conn->query("SELECT COUNT(*) AS total_orders FROM orders WHERE DATE(order_date)=CURDATE()");
$total_orders = (int)($order_result->fetch_assoc()['total_orders'] ?? 0);

$low_result = $conn->query("SELECT COUNT(*) AS low_count FROM ingredients WHERE stock_quantity < minimum_stock");
$low_stock  = (int)($low_result->fetch_assoc()['low_count'] ?? 0);

$low_recipe_result = $conn->query("SELECT COUNT(DISTINCT pi.product_id) AS low_recipe_count FROM product_ingredients pi JOIN ingredients i ON pi.ingredient_id = i.ingredient_id WHERE pi.amount_used > 0 AND i.stock_quantity < i.minimum_stock");
$low_recipe_count  = (int)($low_recipe_result->fetch_assoc()['low_recipe_count'] ?? 0);

/* â”€â”€ Reconciliation alerts â”€â”€ */
$_recon_alerts = 0;
if (can('cash_reconciliation')) {
    $_rar = $conn->query("SELECT COUNT(*) FROM cash_counts WHERE shift_date = CURDATE() AND ABS(difference) >= 0.01");
    if ($_rar) $_recon_alerts = (int)$_rar->fetch_row()[0];
}

/* â”€â”€ Unread announcements â”€â”€ */
$_unread_ann = 0;
if (can('announcements')) {
    $_ar = $conn->prepare("SELECT COUNT(*) FROM announcements a WHERE a.is_active = 1 AND (a.expires_at IS NULL OR a.expires_at >= CURDATE()) AND NOT EXISTS (SELECT 1 FROM announcement_reads r WHERE r.announcement_id = a.id AND r.user_id = ?)");
    $_ar->bind_param('i', $_SESSION['user_id']);
    $_ar->execute();
    $_ar->bind_result($_unread_ann);
    $_ar->fetch();
    $_ar->close();
}

$unpaid_result = $conn->query("SELECT COUNT(*) AS unpaid_count FROM orders WHERE status='PendingPayment' AND DATE(order_date)=CURDATE()");
$unpaid_count  = (int)($unpaid_result->fetch_assoc()['unpaid_count'] ?? 0);

$paylater_result = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE payment_method='paylater' AND status IN ('Preparing','PendingPayment','Completed')");
$paylater_count  = (int)($paylater_result->fetch_assoc()['cnt'] ?? 0);

$unpaid_orders_result = $conn->query("SELECT order_id, daily_order_no, customer_name, total, status, payment_method, order_date, is_open, token_number FROM orders WHERE status='PendingPayment' ORDER BY order_date DESC LIMIT 5");

$paid_open_result = $conn->query("SELECT order_id, daily_order_no, customer_name, total, status, payment_method, order_date, is_open, token_number FROM orders WHERE status='Preparing' AND is_open=0 ORDER BY order_date DESC LIMIT 5");

$refund_result = $conn->query("SELECT IFNULL(SUM(refund_amount),0) AS total_refunds, COUNT(*) AS refund_count FROM order_refunds WHERE DATE(refunded_at)=CURDATE()");
$refund_data   = $refund_result->fetch_assoc();
$total_refunds = (float)($refund_data['total_refunds'] ?? 0);
$refund_count  = (int)($refund_data['refund_count'] ?? 0);

/* â”€â”€ Status counts â”€â”€ */
$stmt_status = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE business_date=? GROUP BY status");
$stmt_status->bind_param("s", $business_date);
$stmt_status->execute();
$status_result = $stmt_status->get_result();
$status_counts = [];
while ($row = $status_result->fetch_assoc()) { $status_counts[$row['status']] = (int)$row['count']; }
$pending_count   = $status_counts['PendingPayment'] ?? 0;
$preparing_count = $status_counts['Preparing']      ?? 0;
$completed_count = $status_counts['Completed']      ?? 0;
$cancelled_count = $status_counts['Cancelled']      ?? 0;

$items_result = $conn->query("SELECT IFNULL(SUM(oi.quantity),0) AS total_items FROM order_items oi JOIN orders o ON oi.order_id=o.order_id WHERE DATE(o.order_date)=CURDATE() AND o.status='Completed'");
$items_sold    = (int)($items_result->fetch_assoc()['total_items'] ?? 0);

$kitchen_result = $conn->query("SELECT order_id, daily_order_no, customer_name, total, order_date, token_number FROM orders WHERE DATE(order_date)=CURDATE() AND status='Preparing' ORDER BY order_date ASC LIMIT 8");

$recent_cols = "order_id, daily_order_no, customer_name, total, status, payment_method, order_date,
    (SELECT COALESCE(SUM(quantity),0) FROM order_items oi WHERE oi.order_id = orders.order_id) AS item_count";
$recent_sql = "SELECT $recent_cols FROM orders WHERE DATE(order_date)=CURDATE() ORDER BY order_date DESC LIMIT 20";
$filter_status = (string) input('status', '');
if ($filter_status) {
    $stmt_filter = $conn->prepare("SELECT $recent_cols FROM orders WHERE DATE(order_date)=CURDATE() AND status=? ORDER BY order_date DESC LIMIT 20");
    $stmt_filter->bind_param("s", $filter_status);
    $stmt_filter->execute();
    $recent_orders = $stmt_filter->get_result();
} else {
    $recent_orders = $conn->query($recent_sql);
}

$top_selling_result = $conn->query("SELECT p.name, p.image, SUM(oi.quantity) as total_sold, p.price FROM products p JOIN order_items oi ON p.product_id=oi.product_id JOIN orders o ON oi.order_id=o.order_id WHERE o.status='Completed' GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 5");

/* ── Extra KPI figures for the redesigned stat-card grid ── */
$paid_row      = $conn->query("SELECT IFNULL(SUM(total),0) amt, COUNT(*) cnt FROM orders WHERE DATE(order_date)=CURDATE() AND status='Completed' AND payment_method<>'paylater'")->fetch_assoc();
$paid_revenue  = (float)$paid_row['amt'];
$paid_orders   = (int)$paid_row['cnt'];

$unpaid_row       = $conn->query("SELECT IFNULL(SUM(total),0) amt, COUNT(*) cnt FROM orders WHERE DATE(order_date)=CURDATE() AND (status='PendingPayment' OR (payment_method='paylater' AND status NOT IN ('Cancelled','Completed')))")->fetch_assoc();
$unpaid_amount    = (float)$unpaid_row['amt'];
$unpaid_amt_count = (int)$unpaid_row['cnt'];

$orders_today_total = (float)($conn->query("SELECT IFNULL(SUM(total),0) t FROM orders WHERE DATE(order_date)=CURDATE()")->fetch_assoc()['t']);

$prod_row        = $conn->query("SELECT COUNT(*) c, IFNULL(SUM(is_available=1),0) a FROM products")->fetch_assoc();
$products_count  = (int)$prod_row['c'];
$products_active = (int)$prod_row['a'];

$low_stock_value = (float)($conn->query("SELECT IFNULL(SUM(stock_quantity*cost_per_unit),0) v FROM ingredients WHERE stock_quantity < minimum_stock")->fetch_assoc()['v']);

$customers_count = (int)($conn->query("SELECT COUNT(*) c FROM loyalty_cards")->fetch_assoc()['c']);

$profit_row      = $conn->query("SELECT IFNULL(SUM(total),0) rev, IFNULL(SUM(cost_total),0) cogs FROM orders WHERE DATE(order_date)=CURDATE() AND status='Completed'")->fetch_assoc();
$rev_completed   = (float)$profit_row['rev'];
$cogs_today      = (float)$profit_row['cogs'];
$profit_today    = $rev_completed - $cogs_today;
$margin_pct      = $rev_completed > 0 ? round($profit_today / $rev_completed * 100, 1) : 0.0;

$pending_new_open = $pending_count + $preparing_count;   // "New + Open"

/* ── Revenue & Orders time-series (Completed orders) for the chart ── */
$_keyed = function (string $sql) use ($conn): array {
    $out = [];
    if ($r = $conn->query($sql)) { while ($x = $r->fetch_assoc()) { $out[(string)$x['k']] = $x; } }
    return $out;
};
$_series = function (array $buckets, array $rows): array {
    $labels = $rev = $ord = [];
    foreach ($buckets as $k => $label) {
        $labels[] = $label;
        $rev[]    = isset($rows[(string)$k]) ? round((float)$rows[(string)$k]['rev'], 2) : 0;
        $ord[]    = isset($rows[(string)$k]) ? (int)$rows[(string)$k]['ord'] : 0;
    }
    $tRev = array_sum($rev); $tOrd = array_sum($ord);
    return [
        'labels' => $labels, 'revenue' => $rev, 'orders' => $ord,
        'totalRevenue' => round($tRev, 2), 'totalOrders' => $tOrd,
        'avg' => $tOrd > 0 ? round($tRev / $tOrd, 2) : 0,
        'peak' => $rev ? round(max($rev), 2) : 0,
    ];
};

$hB = []; for ($h = 6; $h <= 22; $h++) { $hr = $h % 12 ?: 12; $hB[$h] = $hr . ($h < 12 ? 'a' : 'p'); }
$dB = []; for ($i = 6; $i >= 0; $i--) { $dt = date('Y-m-d', strtotime("-$i day")); $dB[$dt] = date('D', strtotime($dt)); }
$mB = []; for ($i = 11; $i >= 0; $i--) { $key = date('Y-m', strtotime("first day of -$i month")); $mB[$key] = date('M', strtotime($key . '-01')); }
$yB = []; for ($i = 4; $i >= 0; $i--) { $yr = (int)date('Y') - $i; $yB[$yr] = (string)$yr; }
$cB = []; for ($i = 29; $i >= 0; $i--) { $dt = date('Y-m-d', strtotime("-$i day")); $cB[$dt] = date('j M', strtotime($dt)); }

$chart = [
    'hourly'  => $_series($hB, $_keyed("SELECT HOUR(order_date) k, SUM(total) rev, COUNT(*) ord FROM orders WHERE status='Completed' AND DATE(order_date)=CURDATE() GROUP BY HOUR(order_date)")),
    'daily'   => $_series($dB, $_keyed("SELECT DATE(order_date) k, SUM(total) rev, COUNT(*) ord FROM orders WHERE status='Completed' AND order_date >= CURDATE()-INTERVAL 6 DAY GROUP BY DATE(order_date)")),
    'monthly' => $_series($mB, $_keyed("SELECT DATE_FORMAT(order_date,'%Y-%m') k, SUM(total) rev, COUNT(*) ord FROM orders WHERE status='Completed' AND order_date >= DATE_FORMAT(CURDATE()-INTERVAL 11 MONTH,'%Y-%m-01') GROUP BY k")),
    'yearly'  => $_series($yB, $_keyed("SELECT YEAR(order_date) k, SUM(total) rev, COUNT(*) ord FROM orders WHERE status='Completed' AND YEAR(order_date) >= YEAR(CURDATE())-4 GROUP BY k")),
    'custom'  => $_series($cB, $_keyed("SELECT DATE(order_date) k, SUM(total) rev, COUNT(*) ord FROM orders WHERE status='Completed' AND order_date >= CURDATE()-INTERVAL 29 DAY GROUP BY DATE(order_date)")),
];

$_flash_welcome     = !empty($_SESSION['flash_welcome']);     unset($_SESSION['flash_welcome']);
$_flash_stock_alert = !empty($_SESSION['flash_stock_alert']); unset($_SESSION['flash_stock_alert']);

/* â”€â”€ Render â”€â”€ */
$pageTitle    = 'Dashboard';
$pageSubtitle = date("l, d F Y");
component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
?>
<?php if ($_is_mgr): ?>
<div class="mdash">
  <?php component('dashboard/mgr-styles'); ?>
  <?php component('dashboard/mgr-header', ['admin_name' => $admin_name]); ?>
  <?php component('dashboard/stat-cards', [
      'sales' => $sales, 'total_orders' => $total_orders, 'orders_today_total' => $orders_today_total,
      'paid_revenue' => $paid_revenue, 'paid_orders' => $paid_orders,
      'unpaid_amount' => $unpaid_amount, 'unpaid_amt_count' => $unpaid_amt_count,
      'pending_new_open' => $pending_new_open, 'completed_count' => $completed_count,
      'products_count' => $products_count, 'products_active' => $products_active,
      'low_stock' => $low_stock, 'low_stock_value' => $low_stock_value,
      'customers_count' => $customers_count,
      'profit_today' => $profit_today, 'margin_pct' => $margin_pct, 'cogs_today' => $cogs_today,
  ]); ?>
  <div class="mdash-grid2">
    <?php component('dashboard/revenue-chart', ['chart' => $chart]); ?>
    <?php component('dashboard/recent-orders', ['recent_orders' => $recent_orders, 'filter_status' => $filter_status]); ?>
  </div>
</div>
<?php else: ?>
<div class="dash dash-body">
  <?php component('dashboard/styles'); ?>
  <?php component('dashboard/header-bar', ['admin_name' => $admin_name, '_is_mgr' => $_is_mgr, '_is_clocked_in' => $_is_clocked_in, '_clock_since' => $_clock_since, '_cur_role_color' => $_cur_role_color, '_cur_role_name' => $_cur_role_name, '_flash_welcome' => $_flash_welcome]); ?>
  <?php component('dashboard/alerts', ['low_stock' => $low_stock, '_is_mgr' => $_is_mgr, 'unpaid_count' => $unpaid_count]); ?>
  <?php component('dashboard/focus-card', ['preparing_count' => $preparing_count, 'unpaid_count' => $unpaid_count, 'paylater_count' => $paylater_count, 'low_stock' => $low_stock]); ?>
  <?php component('dashboard/quick-access', ['low_stock' => $low_stock, 'low_recipe_count' => $low_recipe_count, 'unpaid_count' => $unpaid_count, 'paylater_count' => $paylater_count, '_unread_ann' => $_unread_ann]); ?>
</div>
<?php endif; ?>
<?php
component('dashboard/scripts', ['_flash_welcome' => $_flash_welcome, '_flash_stock_alert' => $_flash_stock_alert, 'low_stock' => $low_stock]);
component('layout/footer');
