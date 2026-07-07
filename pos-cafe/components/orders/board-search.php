<?php
declare(strict_types=1);
/* Orders board — search/staff/date toolbar, status filter pills, and the table shell. */
?>
<div class="ob-toolbar">
    <div class="ob-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search by order ID, customer, or phone..."
               oninput="searchOrders()" onkeydown="if(event.key==='Escape')clearSearch()">
    </div>
    <select id="staffFilter" class="ob-select" onchange="applyFilters()">
        <option value="">All Staff</option>
    </select>
    <label class="ob-date-btn">
        <i class="fa-regular fa-calendar"></i>
        <input type="date" id="dateFilter" style="border:none;padding:0;font-family:inherit;font-size:13.5px;outline:none;background:transparent;" onchange="loadOrders()">
    </label>
</div>

<!-- Status Tabs -->
<?php $r = $_vo_role ?? ($_SESSION['role'] ?? ''); ?>
<div class="status-tabs" id="statusTabs">
    <?php if ($r !== 'staff' && $r !== 'barista'): ?>
    <button class="status-tab active" data-status="all" onclick="filterStatus('all')">
        All <span class="badge" id="count-all">0</span>
    </button>
    <?php endif; ?>
    <?php if ($r !== 'barista'): ?>
    <button class="status-tab <?= $r === 'staff' ? 'active' : '' ?>" data-status="PendingPayment" onclick="filterStatus('PendingPayment')">
        New <span class="badge" id="count-PendingPayment">0</span>
    </button>
    <?php endif; ?>
    <?php if ($r !== 'staff'): ?>
    <button class="status-tab <?= $r === 'barista' ? 'active' : '' ?>" data-status="Preparing" onclick="filterStatus('Preparing')">
        Open <span class="badge" id="count-Preparing">0</span>
    </button>
    <?php endif; ?>
    <?php if ($r !== 'barista'): ?>
    <button class="status-tab" data-status="Completed" onclick="filterStatus('Completed')">
        Completed <span class="badge" id="count-Completed">0</span>
    </button>
    <?php endif; ?>
    <?php if ($r !== 'barista'): ?>
    <button class="status-tab" data-status="Cancelled" onclick="filterStatus('Cancelled')">
        Cancelled <span class="badge" id="count-Cancelled">0</span>
    </button>
    <?php endif; ?>
    <?php if ($r !== 'barista' && $r !== 'staff'): ?>
    <button class="status-tab" data-status="Refunded" onclick="filterStatus('Refunded')">
        Refunded <span class="badge" id="count-Refunded">0</span>
    </button>
    <?php endif; ?>
</div>

<!-- Orders Table -->
<div class="ob-table-wrap">
    <table class="ob-table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Table</th>
                <th class="right">Items</th>
                <th class="right">Total</th>
                <th>Date</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Method</th>
                <th>Placed By</th>
                <th class="right">Actions</th>
            </tr>
        </thead>
        <tbody id="ordersBody"></tbody>
    </table>
</div>
