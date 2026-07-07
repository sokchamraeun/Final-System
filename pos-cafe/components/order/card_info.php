<?php
if (!isset($order) || !isset($statusClass) || !isset($canAdd)) return;
?>
<div class="info-group">
    <span class="info-label">Order</span>
    <span class="info-value">#<?= (int)$order['daily_order_no'] ?></span>
</div>
<div class="info-group">
    <span class="info-label">Customer</span>
    <span class="info-value small"><?= htmlspecialchars($order['customer_name']) ?></span>
</div>
<div class="info-group">
    <span class="info-label">Total</span>
    <span class="info-value total">$<?= number_format($order['total'], 2) ?></span>
</div>
<div class="info-group">
    <span class="info-label">Status</span>
    <span>
        <span class="status-badge <?= $statusClass ?>">
            <?php
            $icons = ['Preparing'=>'fa-fire','PendingPayment'=>'fa-clock','Paid'=>'fa-check-circle','Refunded'=>'fa-rotate-left'];
            $icon = $icons[$order['status']] ?? 'fa-circle';
            $labels = ['PendingPayment'=>'Pending Payment','Preparing'=>'Preparing','Paid'=>'Paid','Refunded'=>'Refunded'];
            $label = $labels[$order['status']] ?? $order['status'];
            ?>
            <i class="fa-solid <?= $icon ?>"></i>
            <?= htmlspecialchars($label) ?>
        </span>
        <?php if ($canAdd): ?>
        <span class="open-badge"><i class="fa-solid fa-circle-plus"></i> Can Add Items</span>
        <?php endif; ?>
    </span>
</div>
