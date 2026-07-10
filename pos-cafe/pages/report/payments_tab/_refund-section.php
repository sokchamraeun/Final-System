<?php if (count($refundChartData) > 0): ?>
<div class="card" data-tab-content="payments">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-rotate-left" style="color:var(--refund-color)"></i>
            <span class="section-hdr-title" style="color:var(--refund-color)">Refund Analytics</span>
        </div>
        <p class="section-desc">Refund frequency over time &mdash; bars show count, line shows total amount refunded. Helps identify patterns or staff issues.</p>
    </div>

    <div class="chart-grid">
        <div class="chart-wrap refund-chart" style="grid-column: 1 / -1;">
            <div class="chart-title">
                <i class="fa-solid fa-chart-area"></i> Refunds Over Time
            </div>
            <canvas id="refundChart" role="img" aria-label="Refunds over the selected period"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (count($refundOrders) > 0): ?>
<div class="card" data-tab-content="payments">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-list" style="color:var(--refund-color)"></i>
            <span class="section-hdr-title" style="color:var(--refund-color)">Refunded Orders</span>
            <span class="section-hdr-badge"><?= $refundCount ?> order<?= $refundCount !== 1 ? 's' : '' ?></span>
        </div>
        <p class="section-desc">Full list of refunds processed in this period &mdash; original amount, refund amount, stated reason, and who processed it.</p>
    </div>

    <div class="refund-table-wrapper">
        <table class="refund-table" id="refundTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Original Total</th>
                    <th>Refund Amount</th>
                    <th>Reason</th>
                    <th>Refunded By</th>
                    <th>Refunded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($refundOrders as $r): ?>
                <tr>
                    <td>#<?= $r['daily_order_no'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td>$<?= fmtMoney($r['original_total']) ?></td>
                    <td class="refund-amount">-$<?= fmtMoney($r['refund_amount']) ?></td>
                    <td class="refund-reason">"<?= htmlspecialchars($r['refund_reason']) ?>"</td>
                    <td class="refunded-by"><?= htmlspecialchars($r['refunded_by']) ?></td>
                    <td style="font-size:13px; color:var(--text-muted);">
                        <?= date('M d, g:i A', strtotime($r['refunded_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="report-pager" id="refundPager"></div>
</div>
<?php endif; ?>
