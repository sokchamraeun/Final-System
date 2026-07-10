<?php
// Calculate unpaid amount
$unpaidAmount = 0;
$unpaidCount = 0;
if (count($orderIds) > 0) {
    $idsStrUnpaid = implode(',', $orderIds);
    $qUnpaid = mysqli_query($conn, "
        SELECT COUNT(*) as cnt, COALESCE(SUM(total), 0) as amount
        FROM orders
        WHERE order_id IN ($idsStrUnpaid) AND status = 'PendingPayment'
    ");
    if ($qUnpaid) {
        $unpaidRow = mysqli_fetch_assoc($qUnpaid);
        $unpaidCount = (int)$unpaidRow['cnt'];
        $unpaidAmount = (float)$unpaidRow['amount'];
    }
}

// Calculate total discount
$totalDiscount = 0;
if (count($orderIds) > 0) {
    $idsStrDiscount = implode(',', $orderIds);
    $qDiscount = mysqli_query($conn, "
        SELECT COALESCE(SUM(discount_amount), 0) as total_discount
        FROM orders
        WHERE order_id IN ($idsStrDiscount)
    ");
    if ($qDiscount) {
        $discountRow = mysqli_fetch_assoc($qDiscount);
        $totalDiscount = (float)$discountRow['total_discount'];
    }
}
?>

<div class="kpi-grid fu" style="animation-delay:.2s" id="kpi-grid" data-tab-content="sales">
  <!-- Card 1 – Total Order Value -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--blue)">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div class="kpi-label">Total Order Value</div>
    <div class="kpi-value" id="kv-total-order" style="color:var(--blue);">$<?= fmtMoney($totalSales) ?></div>
    <div class="kpi-sub">incl. unpaid</div>
  </div>

  <!-- Card 2 – Total Sale -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--emerald)">
      <i class="fa-solid fa-dollar-sign"></i>
    </div>
    <div class="kpi-label">Total Sale</div>
    <div class="kpi-value" id="kv-sales">$<?= fmtMoney($totalSales) ?></div>
    <div class="kpi-sub"><?= $orderCount ?> orders<?php if ($deltaSales): ?> <?= $deltaSales ?><?php endif; ?></div>
  </div>

  <!-- Card 3 – Net Sales -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--teal)">
      <i class="fa-solid fa-wallet"></i>
    </div>
    <div class="kpi-label">Net Sales</div>
    <div class="kpi-value" id="kv-net-sales" style="color:var(--teal);">$<?= fmtMoney($netRevenue) ?></div>
    <div class="kpi-sub">After refund</div>
  </div>

  <!-- Card 4 – Unpaid Amount -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--orange)">
      <i class="fa-solid fa-clock"></i>
    </div>
    <div class="kpi-label">Unpaid Amount</div>
    <div class="kpi-value" id="kv-unpaid" style="color:var(--orange);">$<?= fmtMoney($unpaidAmount) ?></div>
    <div class="kpi-sub"><?= $unpaidCount ?> orders</div>
  </div>

  <!-- Card 5 – Refund Amount -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--red)">
      <i class="fa-solid fa-rotate-left"></i>
    </div>
    <div class="kpi-label">Refund Amount</div>
    <div class="kpi-value" id="kv-refunds" style="color:var(--red);">$<?= fmtMoney($totalRefunded) ?></div>
    <div class="kpi-sub"><?= $refundCount ?> orders</div>
  </div>

  <!-- Card 6 – Total Discount -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--navy)">
      <i class="fa-solid fa-tags"></i>
    </div>
    <div class="kpi-label">Total Discount</div>
    <div class="kpi-value" id="kv-discount" style="color:var(--navy);">$<?= fmtMoney($totalDiscount) ?></div>
    <div class="kpi-sub">Discount/tag given</div>
  </div>

  <!-- Card 7 – Total Profit -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--teal)">
      <i class="fa-solid fa-chart-line"></i>
    </div>
    <div class="kpi-label">Total Profit</div>
    <div class="kpi-value" id="kv-profit" style="color:var(--teal);">$<?= fmtMoney($totalProfit) ?></div>
    <div class="kpi-sub">Cost $<?= fmtMoney($totalCOGS) ?></div>
  </div>

  <!-- Card 8 – Average Order -->
  <div class="kpi-card">
    <div class="kpi-icon-box" style="background:var(--orange)">
      <i class="fa-solid fa-cart-shopping"></i>
    </div>
    <div class="kpi-label">Average Order</div>
    <div class="kpi-value" id="kv-avg" style="color:var(--orange);">$<?= fmtMoney($avgOrder) ?></div>
    <div class="kpi-sub"><?= $totalItemsSold ?> items sold</div>
  </div>
</div>

<?php if ($isLive): ?>
<div class="live-bar" id="live-bar" style="margin:16px 32px 0;" data-tab-content="sales">
  <span class="live-dot pulsing" id="live-dot"></span>
  Live &nbsp;&bull;&nbsp; updated <span id="live-ts">now</span>
</div>
<?php endif; ?>
