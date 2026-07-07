<?php
declare(strict_types=1);
/* Orders board — order-detail modal (houses the action buttons) plus the
   call/cancel/remake/refund modals + notification audio. */
?>
<!-- Order Detail Modal -->
<div class="detail-modal" id="orderDetailModal" onclick="if(event.target===this)closeOrderDetail()">
    <div class="detail-modal-content">
        <div class="detail-head">
            <h2><i class="fa-solid fa-receipt"></i> <span id="detailOrderNumber">Order</span></h2>
            <button type="button" class="btn-close-detail" onclick="closeOrderDetail()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="detail-body" id="detailBody"></div>
        <div class="detail-foot" id="detailActions"></div>
    </div>
</div>

<!-- Call Notification Modal -->
<div class="call-modal" id="callModal">
    <div class="call-modal-content">
        <h2><i class="fa-solid fa-bell"></i> Order Ready!</h2>
        <div class="order-number" id="callOrderNumber">#001</div>
        <p id="callCustomerName">Customer</p>
        <button class="btn-dismiss" onclick="dismissCall()">Dismiss</button>
    </div>
</div>

<!-- Cancel Modal -->
<div class="cancel-modal" id="cancelModal">
    <div class="cancel-modal-content">
        <h2><i class="fa-solid fa-ban"></i> Cancel Order</h2>
        <div class="order-number" id="cancelOrderNumber">#001</div>
        <p>Please provide a reason for cancellation:</p>
        <textarea id="cancelReason" placeholder="Why is this order being cancelled?"></textarea>
        <div class="btn-group">
            <button class="btn-cancel-yes" onclick="confirmCancel()">
                <i class="fa-solid fa-check"></i> Yes, Cancel
            </button>
            <button class="btn-cancel-no" onclick="closeCancelModal()">
                <i class="fa-solid fa-times"></i> Back
            </button>
        </div>
    </div>
</div>

<!-- Remake Modal -->
<div class="refund-modal" id="remakeModal">
    <div class="refund-modal-content" style="max-width:520px;">
        <h2 style="color:#0f766e;"><i class="fa-solid fa-repeat"></i> Remake Order</h2>
        <div class="order-number" id="remakeOrderNumber">#001</div>
        <p style="margin-bottom: 14px;">Adjust the drink and enter the reason:</p>
        <div id="remakeAdjustments"></div>
        <div class="form-group">
            <label>Reason for Remake</label>
            <textarea id="remakeReason" placeholder="e.g. Too sweet, wrong milk, customer not satisfied..."></textarea>
        </div>
        <div class="btn-group">
            <button class="btn-refund-yes" onclick="confirmRemake()">
                <i class="fa-solid fa-repeat"></i> Log Remake
            </button>
            <button class="btn-refund-no" onclick="closeRemakeModal()">
                <i class="fa-solid fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="refund-modal" id="refundModal">
    <div class="refund-modal-content">
        <h2><i class="fa-solid fa-rotate-left"></i> Refund Order</h2>
        <div class="order-number" id="refundOrderNumber">#001</div>
        <p>Enter refund details:</p>

        <div class="form-group">
            <label>Refund Amount ($)</label>
            <input type="number" step="0.01" id="refundAmount" value="">
        </div>

        <div class="form-group">
            <label>Reason for Refund</label>
            <textarea id="refundReason" placeholder="Why is this order being refunded?"></textarea>
        </div>

        <div class="btn-group">
            <button class="btn-refund-yes" onclick="confirmRefund()">
                <i class="fa-solid fa-check"></i> Process Refund
            </button>
            <button class="btn-refund-no" onclick="closeRefundModal()">
                <i class="fa-solid fa-times"></i> Cancel
            </button>
        </div>
    </div>
</div>

<!-- Audio Notifications -->
<audio id="bell">
    <source src="<?= e(root_url('audio/bell.wav')) ?>" type="audio/wav">
</audio>

<audio id="action">
    <source src="<?= e(root_url('audio/order_arrived.wav')) ?>" type="audio/wav">
</audio>

<audio id="callSound">
    <source src="<?= e(root_url('audio/order_arrived.wav')) ?>" type="audio/wav">
</audio>
