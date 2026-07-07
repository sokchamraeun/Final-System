<?php
declare(strict_types=1);
/* ── Expects: $top_selling_result (mysqli result) ── */
$srows = [];
if ($top_selling_result) {
    while ($sr = mysqli_fetch_assoc($top_selling_result)) { $srows[] = $sr; }
}
$maxs = count($srows) > 0 ? (int)max(array_column($srows, 'total_sold')) : 1;
?>
<div class="panel">
  <div class="panel-head">
    <h3><i class="fa-solid fa-trophy"></i> Top Sellers</h3>
    <span style="font-size:11px;color:var(--text-muted)">All time</span>
  </div>
  <div class="sellers-body">
    <?php if (count($srows) > 0): ?>
    <?php foreach ($srows as $si => $sr): $pct = $maxs > 0 ? round($sr['total_sold'] / $maxs * 100) : 0; ?>
    <div class="seller-row">
      <div class="s-rank <?= $si === 0 ? 'gold' : '' ?>"><?= $si + 1 ?></div>
      <img class="s-img" src="<?= e($sr['image']) ?>" alt="" onerror="this.style.visibility='hidden'">
      <div class="s-info">
        <div class="s-name"><?= e($sr['name']) ?></div>
        <div class="s-track"><div class="s-bar" style="width:<?= $pct ?>%"></div></div>
      </div>
      <div class="s-count"><?= (int)$sr['total_sold'] ?></div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="k-empty">
      <i class="fa-regular fa-chart-bar"></i>
      <span>No sales data yet</span>
    </div>
    <?php endif; ?>
  </div>
</div>
