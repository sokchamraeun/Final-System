<?php
if (!isset($order) || !isset($timeAgo) || !isset($isOverdue) || !isset($overdueLabel)) return;
?>
<div class="card-bottom">
    <div class="card-meta">
        <span><i class="fa-solid fa-clock"></i> <?= $timeAgo ?> &nbsp;·&nbsp; <?= date("d M, g:i A", strtotime($order['order_date'])) ?></span>
        <span><i class="fa-solid fa-credit-card"></i> <?= htmlspecialchars(ucfirst($order['payment_method'])) ?></span>
        <span class="table-edit-wrap" data-order="<?= $order['order_id'] ?>">
            <i class="fa-solid fa-ticket" style="color:var(--accent);"></i>
            <span class="table-label" style="color:var(--accent);"><?= !empty($order['table_number']) ? 'Stand ' . htmlspecialchars($order['table_number']) : 'No stand' ?></span>
            <button class="table-edit-btn" title="Change stand"><i class="fa-solid fa-pen-to-square"></i></button>
            <span class="table-input-wrap" style="display:none;">
                <input class="table-input" type="text" value="<?= htmlspecialchars($order['table_number'] ?? '') ?>" placeholder="e.g. 7" maxlength="10">
                <button class="table-save-btn">Save</button>
                <button class="table-cancel-btn">✕</button>
            </span>
        </span>
        <?php if ($order['is_open'] == 1): ?>
        <span style="color:var(--accent);"><i class="fa-solid fa-door-open"></i> Order is open</span>
        <?php else: ?>
        <span style="color:var(--text-muted);"><i class="fa-solid fa-door-closed"></i> Order closed</span>
        <?php endif; ?>
    </div>
    <?php if ($isOverdue): ?>
    <div class="overdue-warning">
        <i class="fa-solid fa-triangle-exclamation"></i> Unpaid for <?= $overdueLabel ?> — follow up with customer
    </div>
    <?php endif; ?>
</div>
