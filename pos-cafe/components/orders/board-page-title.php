<?php
declare(strict_types=1);
/* Orders board — page title row: heading, subtitle, and the "Clear Local" reset button. */
?>
<div class="dash-header">
    <div>
        <h1 class="ob-title">Orders</h1>
        <p class="ob-subtitle">Manage and track customer orders with clean status and payment tracking</p>
    </div>
    <div class="header-actions">
        <button class="btn-pill btn-clear-local" onclick="clearLocalView()">
            <i class="fa-solid fa-broom"></i> Clear Local
        </button>
    </div>
</div>
