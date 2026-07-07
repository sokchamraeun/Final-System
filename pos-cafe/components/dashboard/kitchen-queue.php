<?php
declare(strict_types=1);
/* ── Expects: $kitchen_result (mysqli result) ── */
$krows = [];
if ($kitchen_result && mysqli_num_rows($kitchen_result) > 0) {
    while ($kr = mysqli_fetch_assoc($kitchen_result)) { $krows[] = $kr; }
}
?>
<div class="panel">
  <div class="panel-head">
    <h3>
      <span class="live-dot"></span>
      <i class="fa-solid fa-fire-burner"></i>
      Active Orders
    </h3>
    <div style="display:flex;align-items:center;gap:8px">
      <span class="cnt-badge <?= count($krows) > 0 ? 'on' : '' ?>" id="kitchenCount">
        <?= count($krows) ?> preparing
      </span>
      <button class="refresh-btn" onclick="fetchDashboardData()">
        <i class="fa-solid fa-rotate"></i>
      </button>
    </div>
  </div>
  <div class="kitchen-body" id="kitchenList">
    <?php if (count($krows) > 0): ?>
    <?php foreach ($krows as $kr):
        $mins = floor((time() - strtotime($kr['order_date'])) / 60);
        $tc   = $mins >= 20 ? 'urgent' : ($mins >= 10 ? 'warn' : 'ok');
    ?>
    <div class="k-item">
      <div class="k-no">#<?= (int)$kr['daily_order_no'] ?></div>
      <div class="k-name"><?= e($kr['customer_name']) ?></div>
      <div class="k-total">$<?= number_format((float)$kr['total'], 2) ?></div>
      <div class="k-timer <?= $tc ?>"><?= $mins ?>m</div>
      <span class="k-status-pill"><i class="fa-solid fa-fire-burner"></i> Preparing</span>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="k-empty">
      <i class="fa-solid fa-circle-check"></i>
      <span>All clear — no orders preparing</span>
    </div>
    <?php endif; ?>
  </div>
  <div class="panel-foot">
    <span>Auto-refreshes every 5 s</span>
    <span id="lastUpdated"><?= date("g:i A") ?></span>
  </div>
</div>
