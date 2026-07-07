<?php
declare(strict_types=1);
/* ── Expects: $sales, $sales_trend, $trend_class, $trend_icon, $total_orders, $completed_count, $items_sold ── */
$_sales_trend    = $sales_trend    ?? 0;
$_trend_class    = $trend_class    ?? 'flat';
$_trend_icon     = $trend_icon     ?? 'fa-minus';
$_completed_count = $completed_count ?? 0;
?>
<div class="kpi-row fu" style="animation-delay:.1s">
  <div class="kpi-card c-amber">
    <i class="kpi-watermark fa-solid fa-dollar-sign"></i>
    <div class="kpi-label">Today's Revenue</div>
    <div class="kpi-value">$<span id="kpiRevenue"><?= number_format($sales, 2) ?></span></div>
    <?php if ($_sales_trend != 0): ?>
    <span class="kpi-pill <?= $_trend_class ?>">
      <i class="fa-solid <?= $_trend_icon ?>"></i>
      <?= abs($_sales_trend) ?>% vs yesterday
    </span>
    <?php else: ?>
    <span class="kpi-pill flat"><i class="fa-solid fa-minus"></i> No data yesterday</span>
    <?php endif; ?>
  </div>
  <div class="kpi-card c-green">
    <i class="kpi-watermark fa-solid fa-receipt"></i>
    <div class="kpi-label">Orders Today</div>
    <div class="kpi-value"><span id="kpiOrders"><?= (int)$total_orders ?></span></div>
    <span class="kpi-pill flat">
      <i class="fa-solid fa-circle-check"></i>
      <?= $_completed_count ?> completed
    </span>
  </div>
  <div class="kpi-card c-blue">
    <i class="kpi-watermark fa-solid fa-mug-hot"></i>
    <div class="kpi-label">Items Served</div>
    <div class="kpi-value"><span id="kpiItems"><?= (int)$items_sold ?></span></div>
    <span class="kpi-pill flat">
      <i class="fa-solid fa-box-open"></i> from completed orders
    </span>
  </div>
</div>
