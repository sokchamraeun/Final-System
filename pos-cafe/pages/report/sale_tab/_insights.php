<?php if ($orderCount > 0): ?>
<div class="insights-row" data-tab-content="sales">
    <?php if (!empty($topProducts)): $topProdName = array_key_first($topProducts); ?>
    <div class="insight-chip" id="ic-bestseller">
        <i class="fa-solid fa-trophy"></i>
        <div><div class="ic-label">Best Seller</div><div class="ic-val" id="ic-bestseller-val"><?= htmlspecialchars($topProdName) ?> &mdash; <?= (int)$topProducts[$topProdName]['qty'] ?> sold</div></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($categorySales)): $topCatName = array_key_first($categorySales); ?>
    <div class="insight-chip" id="ic-topcat">
        <i class="fa-solid fa-tags"></i>
        <div><div class="ic-label">Top Category</div><div class="ic-val" id="ic-topcat-val"><?= htmlspecialchars($topCatName) ?> &mdash; <?= (int)$categorySales[$topCatName]['qty'] ?> items</div></div>
    </div>
    <?php endif; ?>
    <div class="insight-chip <?= $margin >= 30 ? 'ic-good' : 'ic-warn' ?>" id="ic-margin">
        <i class="fa-solid fa-percent"></i>
        <div><div class="ic-label">Profit Margin</div><div class="ic-val" id="ic-margin-val"><?= number_format($margin, 1) ?>% &mdash; <?= $margin >= 30 ? 'healthy' : 'below 30% target' ?></div></div>
    </div>
    <?php if ($mode === 'daily' && $peakHour): ?>
    <div class="insight-chip" id="ic-peak">
        <i class="fa-solid fa-fire"></i>
        <div><div class="ic-label">Busiest Hour</div><div class="ic-val" id="ic-peak-val"><?= $peakHour ?> &mdash; most orders this hour</div></div>
    </div>
    <?php endif; ?>
    <?php if ($refundCount > 0): $refundRate = $orderCount > 0 ? $refundCount / $orderCount * 100 : 0; ?>
    <div class="insight-chip ic-alert" id="ic-refrate">
        <i class="fa-solid fa-rotate-left"></i>
        <div><div class="ic-label">Refund Rate</div><div class="ic-val" id="ic-refrate-val"><?= number_format($refundRate, 1) ?>% of orders &mdash; <?= $refundCount ?> refunded</div></div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
