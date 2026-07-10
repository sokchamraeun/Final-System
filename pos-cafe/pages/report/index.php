<?php
require __DIR__ . '/../../../auth.php';
require __DIR__ . '/../../../config.php';
if (!can('report')) { header('Location: /FinalSystem/pos-cafe/index.php?denied=1'); exit; }

date_default_timezone_set("Asia/Phnom_Penh");

require __DIR__ . '/_helpers.php';

$mode = $_GET['mode'] ?? 'daily';

if (!in_array($mode, ['daily', 'monthly', 'range'])) {
    $mode = 'daily';
}

if ($mode === 'monthly') {
    $month = $_GET['month'] ?? (new DateTime())->format("Y-m");
    $start = new DateTime($month . "-01 06:00:00");
    $end = clone $start;
    $end->modify("+1 month")->modify("-1 second");
    $label = $start->format("F Y");
} elseif ($mode === 'range') {
    $fromDate = $_GET['from_date'] ?? getBusinessDateToday();
    $toDate   = $_GET['to_date'] ?? getBusinessDateToday();
    $start = new DateTime($fromDate . " 06:00:00");
    $end   = new DateTime($toDate . " 06:00:00");
    $end->modify("+1 day")->modify("-1 second");
    $label = (new DateTime($fromDate))->format("d M Y") .
             " → " .
             (new DateTime($toDate))->format("d M Y");
} else {
    $date = $_GET['date'] ?? getBusinessDateToday();
    [$start, $end] = businessRangeFromDate($date);
    $label = (new DateTime($date))->format("d M Y");
}

$dailyTarget = DAILY_SALES_TARGET;

$_today = getBusinessDateToday();
$isLive = match($mode) {
    'monthly' => isset($month)    && $month    === (new DateTime())->format("Y-m"),
    'range'   => isset($toDate)   && $toDate   >= $_today,
    default   => isset($date)     && $date     === $_today,
};
unset($_today);

$ingredients = [];
$qIng = mysqli_query($conn, "
    SELECT ingredient_id, ingredient_name, cost_price, purchase_qty, cost_per_unit
    FROM ingredients
");
while ($r = mysqli_fetch_assoc($qIng)) {
    $purchase_qty  = (float)$r['purchase_qty'];
    $cost_price    = (float)$r['cost_price'];
    $cost_per_unit = (float)$r['cost_per_unit'];
    $unit_cost = $cost_per_unit > 0 ? $cost_per_unit : (($purchase_qty > 0) ? ($cost_price / $purchase_qty) : 0);
    $ingredients[(int)$r['ingredient_id']] = [
        "name" => $r['ingredient_name'],
        "unit_cost" => $unit_cost
    ];
    $ingredients[strtolower(trim($r['ingredient_name']))] = [
        "id" => (int)$r['ingredient_id'],
        "name" => $r['ingredient_name'],
        "unit_cost" => $unit_cost
    ];
}

$startStr = $start->format("Y-m-d H:i:s");
$endStr   = $end->format("Y-m-d H:i:s");

$orderIds = [];
$totalSales = 0;
$orderCount = 0;

$stmt_orders = $conn->prepare("SELECT order_id, total FROM orders WHERE status = 'Completed' AND order_date BETWEEN ? AND ?");
$stmt_orders->bind_param("ss", $startStr, $endStr);
$stmt_orders->execute();
$qOrders = $stmt_orders->get_result();

while ($o = mysqli_fetch_assoc($qOrders)) {
    $id = (int)$o['order_id'];
    $orderIds[] = $id;
    $totalSales += (float)$o['total'];
    $orderCount++;
}
$avgOrder = $orderCount > 0 ? $totalSales / $orderCount : 0;

$totalCOGS = 0;
$totalItemsSold = 0;
$totalProfit = 0;
$margin = 0;
$topProducts = [];
$categorySales = [];

if (count($orderIds) > 0) {
    $inOrder = implode(",", array_map('intval', $orderIds));

    $items = [];
    $productIds = [];

    $qItems = mysqli_query($conn, "
        SELECT oi.order_id, oi.product_id, oi.product_name, oi.milk, oi.quantity, oi.price,
               COALESCE(NULLIF(p.category, ''), 'Uncategorized') AS category
        FROM order_items oi
        LEFT JOIN products p ON p.product_id = oi.product_id
        WHERE oi.order_id IN ($inOrder)
          AND oi.price > 0
    ");

    while ($it = mysqli_fetch_assoc($qItems)) {
        $items[] = $it;
        $pid = (int)$it['product_id'];
        if ($pid > 0) $productIds[$pid] = true;
    }

    $recipes = [];
    if (count($productIds) > 0) {
        $inProduct = implode(",", array_map('intval', array_keys($productIds)));
        $qRec = mysqli_query($conn, "
            SELECT pi.product_id, pi.ingredient_id, pi.amount_used, i.ingredient_name
            FROM product_ingredients pi
            JOIN ingredients i ON i.ingredient_id = pi.ingredient_id
            WHERE pi.product_id IN ($inProduct)
        ");
        while ($r = mysqli_fetch_assoc($qRec)) {
            $pid = (int)$r['product_id'];
            $recipes[$pid][] = [
                'ingredient_id'   => (int)$r['ingredient_id'],
                'ingredient_name' => $r['ingredient_name'],
                'amount_used'     => (float)$r['amount_used']
            ];
        }
    }

    foreach ($items as $it) {
        $pid      = (int)$it['product_id'];
        $qty      = max(1, (int)$it['quantity']);
        $milkType = trim((string)$it['milk']);
        $pname    = (string)$it['product_name'];
        $category = trim((string)$it['category']) ?: 'Uncategorized';
        $itemCost = 0;

        if (isset($recipes[$pid])) {
            foreach ($recipes[$pid] as $rc) {
                $iname  = strtolower(trim($rc['ingredient_name']));
                $amount = (float)$rc['amount_used'] * $qty;
                if ($amount <= 0) continue;

                if (strpos($iname, 'milk') !== false) {
                    $key = strtolower(trim($milkType ?: 'Fresh Milk'));
                    if (isset($ingredients[$key])) {
                        $itemCost += $amount * (float)$ingredients[$key]['unit_cost'];
                    }
                } else {
                    $iid = (int)$rc['ingredient_id'];
                    if (isset($ingredients[$iid])) {
                        $itemCost += $amount * (float)$ingredients[$iid]['unit_cost'];
                    }
                }
            }
        }

        $totalCOGS      += $itemCost;
        $totalItemsSold += $qty;
        $itemRevenue     = (float)($it['price'] ?? 0) * $qty;

        if (!isset($topProducts[$pname])) {
            $topProducts[$pname] = ['qty' => 0, 'cogs' => 0, 'revenue' => 0];
        }
        $topProducts[$pname]['qty']     += $qty;
        $topProducts[$pname]['cogs']    += $itemCost;
        $topProducts[$pname]['revenue'] += $itemRevenue;

        if (!isset($categorySales[$category])) {
            $categorySales[$category] = ['qty' => 0, 'cogs' => 0];
        }
        $categorySales[$category]['qty'] += $qty;
        $categorySales[$category]['cogs'] += $itemCost;
    }

    $totalProfit = $totalSales - $totalCOGS;
    $margin = $totalSales > 0 ? ($totalProfit / $totalSales * 100) : 0;

    uasort($topProducts, fn($a, $b) => $b['qty'] - $a['qty']);
    uasort($categorySales, fn($a, $b) => $b['qty'] - $a['qty']);
}

$hourlyData = [];
$peakHour = null;
if ($mode === 'daily') {
    $qHourly = mysqli_query($conn, "
        SELECT HOUR(order_date) as h, COUNT(*) as cnt, SUM(total) as rev
        FROM orders
        WHERE status = 'Completed'
          AND order_date BETWEEN '$startStr' AND '$endStr'
        GROUP BY HOUR(order_date)
        ORDER BY h ASC
    ");
    $hourMap = [];
    while ($r = mysqli_fetch_assoc($qHourly)) {
        $hourMap[(int)$r['h']] = ['count' => (int)$r['cnt'], 'revenue' => (float)$r['rev']];
    }
    $maxRev = 0;
    for ($h = 6; $h <= 22; $h++) {
        $rev = $hourMap[$h]['revenue'] ?? 0;
        $hourlyData[] = [
            'label'   => sprintf('%02d:00', $h),
            'count'   => $hourMap[$h]['count']   ?? 0,
            'revenue' => $rev,
        ];
        if ($rev > $maxRev) { $maxRev = $rev; $peakHour = date('g:i A', mktime($h, 0, 0)); }
    }
}

$dailyTrendData = [];
if ($mode !== 'daily') {
    $qTrend = mysqli_query($conn, "
        SELECT DATE(order_date) as d, COUNT(*) as cnt, SUM(total) as rev
        FROM orders
        WHERE status = 'Completed'
          AND order_date BETWEEN '$startStr' AND '$endStr'
        GROUP BY DATE(order_date)
        ORDER BY d ASC
    ");
    while ($r = mysqli_fetch_assoc($qTrend)) {
        $dailyTrendData[] = [
            'label'   => date('M d', strtotime($r['d'])),
            'count'   => (int)$r['cnt'],
            'revenue' => (float)$r['rev'],
        ];
    }
}

$paymentMethods = [];
if (count($orderIds) > 0) {
    $idsStr2 = implode(',', $orderIds);
    $qPay = mysqli_query($conn, "
        SELECT payment_method, COUNT(*) as cnt, SUM(total) as rev
        FROM orders
        WHERE order_id IN ($idsStr2)
        GROUP BY payment_method
        ORDER BY rev DESC
    ");
    while ($pm = mysqli_fetch_assoc($qPay)) {
        $paymentMethods[] = [
            'method'  => ucfirst($pm['payment_method']),
            'count'   => (int)$pm['cnt'],
            'revenue' => (float)$pm['rev'],
        ];
    }
}

$refundOrders = [];
$totalRefunded = 0;
$refundCount = 0;
$refundChartData = [];

if (count($orderIds) > 0) {
    $idsStr3 = implode(',', $orderIds);
    $qRef = mysqli_query($conn, "
        SELECT orr.*, o.daily_order_no, o.total AS original_total, o.customer_name
        FROM order_refunds orr
        JOIN orders o ON o.order_id = orr.order_id
        WHERE orr.order_id IN ($idsStr3)
        ORDER BY orr.refunded_at DESC
    ");
    while ($r = mysqli_fetch_assoc($qRef)) {
        $refundOrders[] = $r;
        $totalRefunded += (float)$r['refund_amount'];
        $refundCount++;
    }
}

$remakeOrders = [];
$remakeCount = 0;
$_tbl_check = $conn->query("SHOW TABLES LIKE 'order_remakes'");
if ($_tbl_check && $_tbl_check->num_rows > 0) {
    $qRem = mysqli_query($conn, "
        SELECT rm.id, rm.reason, rm.remade_by, rm.remade_at,
               o.daily_order_no, o.customer_name,
               GROUP_CONCAT(DISTINCT oi.product_name ORDER BY oi.product_name SEPARATOR ', ') AS products
        FROM order_remakes rm
        JOIN orders o ON o.order_id = rm.order_id
        LEFT JOIN order_items oi ON oi.order_id = rm.order_id
        WHERE rm.remade_at BETWEEN '$startStr' AND '$endStr'
        GROUP BY rm.id
        ORDER BY rm.remade_at DESC
    ");
    while ($r = mysqli_fetch_assoc($qRem)) {
        $remakeOrders[] = $r;
        $remakeCount++;
    }
}

$netRevenue = $totalSales - $totalRefunded;

$prevSales = 0;
$prevOrders = 0;
$deltaSales = '';
$deltaOrders = '';

if ($mode === 'daily' && isset($date)) {
    $prevStart = clone $start;
    $prevStart->modify("-1 day");
    $prevEnd = clone $end;
    $prevEnd->modify("-1 day");
} elseif ($mode === 'monthly' && isset($month)) {
    $prevStart = clone $start;
    $prevStart->modify("-1 month");
    $prevEnd = clone $end;
    $prevEnd->modify("-1 month");
} elseif ($mode === 'range' && isset($fromDate)) {
    $rangeLen = $start->diff($end)->days;
    $prevEnd = clone $start;
    $prevStart = clone $prevEnd;
    $prevStart->modify("-" . ($rangeLen + 1) . " days");
} else {
    $prevStart = null;
    $prevEnd = null;
}

if ($prevStart && $prevEnd) {
    $pS = $prevStart->format("Y-m-d H:i:s");
    $pE = $prevEnd->format("Y-m-d H:i:s");
    $qPrev = mysqli_query($conn, "
        SELECT COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev
        FROM orders
        WHERE status = 'Completed' AND order_date BETWEEN '$pS' AND '$pE'
    ");
    $dp = mysqli_fetch_assoc($qPrev);
    $prevOrders = (int)$dp['cnt'];
    $prevSales  = (float)$dp['rev'];

    $deltaSales  = deltaStr($totalSales, $prevSales);
    $deltaOrders = deltaStr($orderCount, $prevOrders);
}

if (count($orderIds) > 0) {
    $idsStr5 = implode(',', $orderIds);
    if ($mode === 'daily' && isset($date)) {
        $qRefChart = mysqli_query($conn, "
            SELECT DATE_FORMAT(refunded_at, '%H:00') AS lbl,
                   COUNT(*) AS cnt,
                   SUM(refund_amount) AS total
            FROM order_refunds
            WHERE order_id IN ($idsStr5)
            GROUP BY lbl ORDER BY lbl ASC
        ");
    } elseif ($mode === 'monthly' && isset($month)) {
        $qRefChart = mysqli_query($conn, "
            SELECT DATE_FORMAT(refunded_at, '%b %d') AS lbl,
                   COUNT(*) AS cnt,
                   SUM(refund_amount) AS total
            FROM order_refunds
            WHERE order_id IN ($idsStr5)
            GROUP BY DATE(refunded_at) ORDER BY refunded_at ASC
        ");
    } else {
        $qRefChart = mysqli_query($conn, "
            SELECT DATE_FORMAT(refunded_at, '%b %d') AS lbl,
                   COUNT(*) AS cnt,
                   SUM(refund_amount) AS total
            FROM order_refunds
            WHERE order_id IN ($idsStr5)
            GROUP BY DATE(refunded_at) ORDER BY refunded_at ASC
        ");
    }
    if ($qRefChart) {
        while ($r = mysqli_fetch_assoc($qRefChart)) {
            $refundChartData[] = $r;
        }
    }
}

$embed_mode = start_embed();
?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
<title>Sales Overview | The Birdnest Cafe</title>
<meta name="viewport" content="width=device-width,initial-scale=1" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    darkMode: 'class',
    important: true,
    theme: { extend: { colors: { accent: '#0d9488', 'accent-light': '#14b8a6', 'accent-dark': '#0f766e' } } }
}
</script>
<?php require __DIR__ . '/_styles.php'; ?>
</head>
<?php require __DIR__ . '/_header.php'; ?>
<?php require __DIR__ . '/_tabs.php'; ?>
<?php require __DIR__ . '/_filters.php'; ?>
<?php require __DIR__ . '/sale_tab/_kpi-cards.php'; ?>
<div id="tab-placeholder" class="card" style="display:none;margin:24px 32px;text-align:center;padding:48px 24px;">
    <i class="fa-solid fa-tools" style="font-size:32px;color:var(--text-xs);margin-bottom:12px;"></i>
    <div style="font-size:16px;font-weight:600;color:var(--text-muted);margin-bottom:6px;">Coming Soon</div>
    <div style="font-size:13px;color:var(--text-xs);">This section is under development and will be available in a future update.</div>
</div>
<div class="report-content">
<!-- Sales tab -->
<?php require __DIR__ . '/sale_tab/_insights.php'; ?>
<?php require __DIR__ . '/sale_tab/_narrative.php'; ?>
<?php require __DIR__ . '/sale_tab/_charts.php'; ?>
<!-- Products tab -->
<?php require __DIR__ . '/products_tab/_products-table.php'; ?>
<!-- Payments tab -->
<?php require __DIR__ . '/payments_tab/_charts.php'; ?>
<?php require __DIR__ . '/payments_tab/_refund-section.php'; ?>
<?php require __DIR__ . '/payments_tab/_remake-section.php'; ?>
</div>
<?php require __DIR__ . '/_scripts.php'; ?>
</div></div>
<?php end_embed($embed_mode); ?>
</body>
</html>
