<?php if ($remakeCount > 0): ?>
<div class="card" data-tab-content="payments">
    <div class="section-hdr">
        <div class="section-hdr-row">
            <i class="fa-solid fa-repeat" style="color:var(--warning);"></i>
            <span class="section-hdr-title" style="color:var(--warning);">Remade Orders</span>
            <span class="section-hdr-badge"><?= $remakeCount ?> remake<?= $remakeCount !== 1 ? 's' : '' ?></span>
        </div>
        <p class="section-desc">Drinks that were remade due to quality issues &mdash; useful for spotting recurring problems with specific drinks or baristas.</p>
    </div>

    <div class="refund-table-wrapper">
        <table class="refund-table" id="remakeTable">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Drinks</th>
                    <th>Reason</th>
                    <th>Logged By</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($remakeOrders as $r): ?>
                <tr>
                    <td>#<?= $r['daily_order_no'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td style="color:var(--text);"><?= htmlspecialchars($r['products'] ?? '&mdash;') ?></td>
                    <td class="refund-reason">"<?= htmlspecialchars($r['reason']) ?>"</td>
                    <td class="refunded-by"><?= htmlspecialchars($r['remade_by']) ?></td>
                    <td style="font-size:13px; color:var(--text-muted);">
                        <?= date('M d, g:i A', strtotime($r['remade_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="report-pager" id="remakePager"></div>
</div>
<?php endif; ?>
