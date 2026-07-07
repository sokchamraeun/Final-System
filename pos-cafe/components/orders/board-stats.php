<?php
declare(strict_types=1);
/* Orders board — stat-card row. Values are computed client-side from the
   loaded order set (see computeStats() in board-actions.php) so they always
   reflect exactly what's currently shown/filtered, same as the table below. */
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Shown Orders</span>
            <span class="stat-icon c-blue"><i class="fa-solid fa-list"></i></span>
        </div>
        <div class="stat-value" id="stat-shown">0</div>
        <div class="stat-desc">After search and filter</div>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Paid</span>
            <span class="stat-icon c-green"><i class="fa-solid fa-check"></i></span>
        </div>
        <div class="stat-value" id="stat-paid">0</div>
        <div class="stat-desc">Paid orders loaded</div>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Unpaid</span>
            <span class="stat-icon c-amber"><i class="fa-solid fa-clock"></i></span>
        </div>
        <div class="stat-value" id="stat-unpaid">0</div>
        <div class="stat-desc">Need follow up</div>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Refunded</span>
            <span class="stat-icon c-red"><i class="fa-solid fa-rotate-left"></i></span>
        </div>
        <div class="stat-value" id="stat-refunded">0</div>
        <div class="stat-desc">Refunded orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-head">
            <span class="stat-label">Loaded Revenue</span>
            <span class="stat-icon c-teal"><i class="fa-solid fa-dollar-sign"></i></span>
        </div>
        <div class="stat-value" id="stat-revenue">$0.00</div>
        <div class="stat-desc">Current page / polling data</div>
    </div>
</div>
