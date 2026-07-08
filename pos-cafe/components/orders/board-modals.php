<?php
declare(strict_types=1);
/* Orders board — order-detail modal (houses the action buttons) plus the
   call/cancel/remake/refund modals + notification audio. */
?>

<style>
/* ------------------------------------------------------------------ */
/* Order Detail Modal — reusable classes for JS-injected body content  */
/* Use these class names when building #detailBody / #detailActions    */
/* ------------------------------------------------------------------ */

.detail-customer-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    border: 1px solid #99f6e4;
    background: #fff;
    border-radius: 1rem;
    padding: 0.85rem 1.25rem;
    margin-bottom: 1.25rem;
    font-size: 0.9rem;
    color: #334155;
}

.detail-customer-bar .customer-name {
    color: #64748b;
}

.detail-customer-bar .customer-name b {
    color: #1e293b;
    font-weight: 700;
}

.detail-customer-bar .print-meta {
    color: #94a3b8;
}

.detail-customer-bar .print-meta b {
    color: #334155;
}

.detail-info-row {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.detail-info-row .info-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    margin-right: 0.5rem;
}

.detail-select {
    border: 1px solid #99f6e4;
    border-radius: 0.75rem;
    padding: 0.4rem 0.9rem;
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f766e;
    background: #fff;
    outline: none;
    cursor: pointer;
}

.detail-select:focus {
    border-color: #14b8a6;
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.15);
}

.detail-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.detail-items-table thead th {
    text-align: left;
    font-size: 0.78rem;
    font-weight: 700;
    color: #475569;
    padding: 0.5rem 0.25rem 0.75rem;
    border-bottom: 2px solid #ccfbf1;
}

.detail-items-table thead th.align-right {
    text-align: right;
}

.detail-items-table tbody td {
    padding: 0.85rem 0.25rem;
    color: #334155;
    vertical-align: top;
}

.detail-items-table tbody td.align-right {
    text-align: right;
}

.detail-items-table .item-name {
    font-weight: 700;
    color: #1e293b;
}

.detail-items-table .subtotal-cell {
    font-weight: 700;
    color: #0f766e;
}

.detail-summary-row {
    display: flex;
    justify-content: flex-end;
    gap: 1.5rem;
    padding: 0.4rem 0.25rem;
    font-size: 0.9rem;
    color: #64748b;
}

.detail-summary-row.total {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    border-top: 1px solid #f1f5f9;
    margin-top: 0.25rem;
    padding-top: 0.75rem;
}

.detail-summary-row.total .amount {
    color: #0f766e;
}

.btn-print-receipt {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    padding: 0.7rem 1.5rem;
    font-weight: 700;
    font-size: 0.9rem;
    color: #fff;
    background: linear-gradient(135deg, #0d9488, #059669);
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-print-receipt:hover {
    filter: brightness(1.05);
    box-shadow: 0 8px 20px -6px rgba(13, 148, 136, 0.5);
    transform: translateY(-1px);
}

.btn-close-order {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 9999px;
    padding: 0.7rem 1.75rem;
    font-weight: 700;
    font-size: 0.9rem;
    color: #334155;
    background: #fff;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-close-order:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
</style>

<!-- Order Detail Modal -->
<div class="detail-modal fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4"
     id="orderDetailModal"
     onclick="if(event.target===this)closeOrderDetail()">

    <div class="detail-modal-content w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl">

        <div class="detail-head flex items-center justify-between bg-gradient-to-r from-teal-600 to-emerald-500 px-7 py-5">

            <h2 class="flex items-center gap-2 text-lg font-bold text-white">
                <i class="fa-solid fa-receipt"></i>
                Order Detail <span id="detailOrderNumber">#</span>
            </h2>

            <button type="button"
                    class="btn-close-detail flex h-9 w-9 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25"
                    onclick="closeOrderDetail()">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <div class="detail-body max-h-[65vh] overflow-y-auto px-7 py-6" id="detailBody"></div>

        <div class="detail-foot flex items-center justify-between border-t border-teal-50 bg-teal-50/50 px-7 py-5" id="detailActions"></div>

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

<!-- Payment Method Modal -->
<div class="pay-modal" id="paymentModal">
    <div class="pay-modal-content">
        <div class="pay-modal-head">
            <h2><i class="fa-solid fa-credit-card"></i> Payment Method</h2>
            <button type="button" class="pay-close-btn" onclick="closePaymentModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="pay-modal-body">
            <div class="pay-order-no" id="payOrderNumber">#001</div>
            <p class="pay-prompt">How is the customer paying?</p>
            <div class="ob-pay-methods">
                <div class="ob-pay-method" data-method="bakong" onclick="selectPayMethod(this,'bakong')">
                    <input type="radio" name="obPayMethod" value="bakong">
                    <i class="ob-pm-ico fa-solid fa-qrcode"></i>
                    <span class="ob-pm-lbl">Bakong</span>
                    <i class="ob-pm-check fa-solid fa-circle-check"></i>
                </div>
                <div class="ob-pay-method" data-method="cash" onclick="selectPayMethod(this,'cash')">
                    <input type="radio" name="obPayMethod" value="cash">
                    <i class="ob-pm-ico fa-solid fa-money-bill-wave"></i>
                    <span class="ob-pm-lbl">Cash</span>
                    <i class="ob-pm-check fa-solid fa-circle-check"></i>
                </div>
                <div class="ob-pay-method" data-method="paylater" onclick="selectPayMethod(this,'paylater')">
                    <input type="radio" name="obPayMethod" value="paylater">
                    <i class="ob-pm-ico fa-solid fa-clock"></i>
                    <span class="ob-pm-lbl">Later</span>
                    <i class="ob-pm-check fa-solid fa-circle-check"></i>
                </div>
                <div class="ob-pay-method" data-method="riel" onclick="selectPayMethod(this,'riel')">
                    <input type="radio" name="obPayMethod" value="riel">
                    <i class="ob-pm-ico fa-solid fa-coins"></i>
                    <span class="ob-pm-lbl">Riel &#x17DB;</span>
                    <i class="ob-pm-check fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div id="obPayButtons">
                <div class="pay-btn-group">
                    <button class="pay-btn-confirm" onclick="confirmPayment()">
                        <i class="fa-solid fa-check"></i> Confirm Payment
                    </button>
                    <button class="pay-btn-cancel" onclick="closePaymentModal()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                </div>
            </div>
            <div id="obQrDisplay" style="display:none;">
                <div class="pay-order-summary" id="obPaySummary"></div>
                <div class="pay-qr-area">
                    <div class="pay-qr-wrap"><img id="obQrImage" src="" alt="Bakong QR"></div>
                    <div class="pay-qr-hint">Scan with Bakong app to pay</div>
                    <div class="pay-qr-status" id="obQrStatus">Waiting for payment...</div>
                    <button class="pay-btn-cancel" onclick="cancelBakongPayment()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                </div>
            </div>
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