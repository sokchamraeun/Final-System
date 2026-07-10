<?php if ($orderCount > 0):
    $marginHealth = $margin >= 50 ? 'excellent' : ($margin >= 30 ? 'healthy' : 'below the 30% target');
    $marginClass  = $margin >= 50 ? 'good' : ($margin >= 30 ? 'ok' : 'warn');

    if ($refundCount > 0) {
        $refundRate2 = round($refundCount / $orderCount * 100, 1);
        $refundSentence = " <strong>{$refundCount}</strong> " . ($refundCount === 1 ? 'order was' : 'orders were') . " refunded, totalling <strong>\$" . fmtMoney($totalRefunded) . "</strong> ({$refundRate2}% refund rate), bringing the take-home revenue to <strong>\$" . fmtMoney($netRevenue) . "</strong>.";
    } else {
        $refundSentence = " No refunds were issued, so the full <strong>\$" . fmtMoney($netRevenue) . "</strong> is the take-home revenue.";
    }

    $peakSentence = ($mode === 'daily' && $peakHour) ? " The busiest sales hour was <strong>{$peakHour}</strong>." : '';

    $topProdSentence = '';
    if (!empty($topProducts)) {
        $tp   = array_key_first($topProducts);
        $tqty = (int)$topProducts[$tp]['qty'];
        $topProdSentence = " Best-selling item: <strong>" . htmlspecialchars($tp) . "</strong> with <strong>{$tqty}</strong> " . ($tqty === 1 ? 'unit' : 'units') . " sold.";
    }
?>
<div class="card" data-tab-content="sales">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-file-lines"></i>
            <span class="section-hdr-title">Period Summary</span>
            <span class="section-hdr-badge">&nbsp;<?= htmlspecialchars($label) ?></span>
        </div>
        <p class="section-desc">Auto-generated plain-English overview of this period's performance.</p>
    </div>
    <p class="report-summary" id="live-summary">
        During <strong><?= htmlspecialchars($label) ?></strong>, the caf&eacute; completed <strong><?= $orderCount ?></strong> <?= $orderCount === 1 ? 'order' : 'orders' ?><?= $totalItemsSold > 0 ? ', serving <strong>' . $totalItemsSold . '</strong> items,' : '' ?> totalling <strong>$<?= fmtMoney($totalSales) ?></strong> in sales (avg <strong>$<?= fmtMoney($avgOrder) ?></strong> per order). After ingredient costs of <strong>$<?= fmtMoney($totalCOGS) ?></strong>, the gross profit was <strong>$<?= fmtMoney($totalProfit) ?></strong> &mdash; a margin of <strong class="<?= $marginClass ?>"><?= number_format($margin, 1) ?>%</strong>, which is <?= $marginHealth ?>.<?= $refundSentence ?><?= $peakSentence ?><?= $topProdSentence ?>
    </p>
</div>
<?php endif; ?>
