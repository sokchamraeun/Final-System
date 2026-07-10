<?php declare(strict_types=1);
/* ── Manager dashboard: Recent Orders table (matches design). ──
   Expects: $recent_orders (mysqli result), $filter_status.
   Payment & Status render as styled pills (display-only); the
   row's "View" opens the full order. ── */
$hasRows = $recent_orders && mysqli_num_rows($recent_orders) > 0;

/* Status → pill tone + label */
$statusTone = function (string $s): array {
    switch ($s) {
        case 'Completed':      return ['green',  'Completed'];
        case 'Preparing':      return ['blue',   'Preparing'];
        case 'Paid':           return ['green',  'Paid'];
        case 'PendingPayment': return ['amber',  'New'];
        case 'Cancelled':      return ['red',    'Cancelled'];
        case 'Refunded':       return ['purple', 'Refunded'];
        default:               return ['slate',  $s !== '' ? $s : 'Unknown'];
    }
};
/* Payment → pill tone + label */
$payTone = function (array $o): array {
    $pm = (string)($o['payment_method'] ?? '');
    $st = (string)($o['status'] ?? '');
    if ($pm === 'paylater') return $st === 'Completed' ? ['green', 'Paid'] : ['purple', 'Pay Later'];
    if ($st === 'PendingPayment') return ['red', 'Unpaid'];
    if ($st === 'Cancelled') return ['slate', '—'];
    return ['green', 'Paid'];
};
?>
<div class="mcard">
  <div class="mcard-head" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
    <div>
      <h3>Recent Orders</h3>
      <p>Latest transactions needing attention</p>
    </div>
    <?php if (!$filter_status): ?>
    <a href="<?= e(url('orders/board')) ?>" class="ro-view" style="margin-top:2px;">View all <i class="fa-solid fa-arrow-right"></i></a>
    <?php endif; ?>
  </div>

  <div class="ro-wrap">
    <table class="ro-tbl">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th class="ro-num">Items</th>
          <th style="text-align:right;">Total</th>
          <th>Payment</th>
          <th>Status</th>
          <th style="text-align:right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($hasRows): ?>
        <?php while ($ro = mysqli_fetch_assoc($recent_orders)):
            [$stTone, $stLabel] = $statusTone((string)$ro['status']);
            [$pyTone, $pyLabel] = $payTone($ro);
            $cust = trim((string)$ro['customer_name']) !== '' ? $ro['customer_name'] : 'Guest';
        ?>
        <tr>
          <td class="ro-id">#<?= (int)$ro['daily_order_no'] ?></td>
          <td><?= e($cust) ?></td>
          <td class="ro-num"><?= (int)$ro['item_count'] ?></td>
          <td class="ro-total" style="text-align:right;">$<?= number_format((float)$ro['total'], 2) ?></td>
          <td><span class="pill <?= $pyTone ?>"><?= e($pyLabel) ?> <i class="fa-solid fa-chevron-down"></i></span></td>
          <td><span class="pill <?= $stTone ?>"><?= e($stLabel) ?> <i class="fa-solid fa-chevron-down"></i></span></td>
          <td style="text-align:right;">
            <a class="ro-view" href="#" onclick="event.preventDefault();openRecentOrderDetail(<?= (int)$ro['order_id'] ?>)">View <i class="fa-solid fa-chevron-right"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr><td colspan="7"><div class="ro-empty"><i class="fa-regular fa-rectangle-list"></i> &nbsp;No orders today yet</div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
