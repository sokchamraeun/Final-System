<?php declare(strict_types=1);
/* ── Manager dashboard: 10 KPI stat cards (matches design). ──
   Expects: $sales, $total_orders, $orders_today_total, $paid_revenue,
   $paid_orders, $unpaid_amount, $unpaid_amt_count, $pending_new_open,
   $completed_count, $products_count, $products_active, $low_stock,
   $low_stock_value, $customers_count, $profit_today, $margin_pct,
   $cogs_today ── */
$m = fn(float $v): string => '$' . number_format($v, 2);

$cards = [
    ['Total Revenue Today', $m((float)$sales),            'fa-dollar-sign',       'ic-green',  number_format((int)$total_orders) . ' orders today'],
    ['Paid Revenue',        $m((float)$paid_revenue),     'fa-calendar-check',    'ic-teal',   number_format((int)$paid_orders) . ' paid orders'],
    ['Unpaid Amount',       $m((float)$unpaid_amount),    'fa-clock',             'ic-orange', number_format((int)$unpaid_amt_count) . ' unpaid orders'],
    ['Orders Today',        number_format((int)$total_orders), 'fa-cart-shopping','ic-blue',   $m((float)$orders_today_total) . ' total'],
    ['Pending Orders',      number_format((int)$pending_new_open), 'fa-rotate-right','ic-amber','New + Open'],
    ['Completed Orders',    number_format((int)$completed_count), 'fa-circle-check','ic-green', 'Completed today'],
    ['Products',            number_format((int)$products_count), 'fa-box',        'ic-blue',   number_format((int)$products_active) . ' active'],
    ['Ingredient Low Stock',number_format((int)$low_stock),'fa-triangle-exclamation','ic-red', 'Value ' . $m((float)$low_stock_value)],
    ['Cash',     $m((float)$paid_revenue), 'fa-money-bill-wave',     'ic-teal',   number_format((int)$paid_orders) . ' paid'],
    ['Profit Today',        $m((float)$profit_today),     'fa-arrow-trend-up',    'ic-teal',   'Margin ' . number_format((float)$margin_pct, 1) . '% · COGS ' . $m((float)$cogs_today)],
];
?>
<div class="mdash-stats">
  <?php foreach ($cards as [$label, $value, $icon, $tone, $foot]): ?>
  <div class="stat">
    <div class="stat-top">
      <div class="stat-label"><?= e($label) ?></div>
      <div class="stat-ic <?= $tone ?>"><i class="fa-solid <?= $icon ?>"></i></div>
    </div>
    <div class="stat-value"><?= e($value) ?></div>
    <div class="stat-foot"><?= e($foot) ?></div>
  </div>
  <?php endforeach; ?>
</div>
