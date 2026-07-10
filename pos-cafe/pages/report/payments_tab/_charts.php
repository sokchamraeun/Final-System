<?php if (!empty($paymentMethods)): ?>
<div class="card" data-tab-content="payments">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-credit-card"></i>
            <span class="section-hdr-title">Payment Methods</span>
        </div>
        <p class="section-desc">How customers paid &mdash; useful for cash drawer reconciliation and understanding payment preferences.</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:center;">
        <div class="chart-wrap" style="height:240px;">
            <canvas id="paymentChart" role="img" aria-label="Sales split by payment method"></canvas>
        </div>
        <div>
            <?php foreach ($paymentMethods as $pm): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-weight:600;color:var(--text);"><?= htmlspecialchars($pm['method']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted);"><?= $pm['count'] ?> order<?= $pm['count']!==1?'s':'' ?></div>
                </div>
                <div style="font-weight:700;color:var(--teal);font-size:16px;">$<?= fmtMoney($pm['revenue']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>