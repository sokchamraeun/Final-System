<?php
$add_to_order_mode = $add_to_order_mode ?? 0;
?>
<div id="cpPayModal" class="cp-paymodal">
  <div class="cp-paymodal-card" id="cpPayModalCard">
    <div class="cp-paymodal-head">
      <h3><i class="fa-solid fa-credit-card" style="color:var(--orange, #14b8a6);"></i> Payment</h3>
      <button type="button" class="cp-paymodal-close" id="cpPayModalClose" onclick="cpClosePayModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="cp-pm-items" id="cpPmItems"></div>

    <div class="cp-pm-breakdown">
      <div class="cp-pm-row"><span>Subtotal</span><span id="cpPmSubtotal">$0.00</span></div>
      <?php if ((float)TAX_RATE > 0): ?>
      <div class="cp-pm-row" id="cpPmTaxRow"><span>Tax (<?= (int)TAX_RATE ?>%)</span><span id="cpPmTax">$0.00</span></div>
      <?php endif; ?>
    </div>
    <div class="cp-pm-hero">
      <span class="cp-pm-hero-label">Total due</span>
      <span class="cp-pm-hero-amt" id="cpPmTotal">$0.00</span>
      <span class="cp-pm-hero-khr" id="cpPmKhr">&#x17DB; 0</span>
    </div>
    <div id="cpPayModalBody">
      <?php if (!$add_to_order_mode): ?>
      <div class="cp-pm-methods-label">How is the customer paying?</div>
      <div class="cp-pay-methods" id="cpPayMethods">
        <div class="cp-pay-method" data-method="bakong" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="bakong">
          <i class="cp-pm-ico fa-solid fa-qrcode"></i><span class="cp-pm-lbl">Bakong</span>
          <i class="cp-pm-check fa-solid fa-circle-check"></i>
        </div>
        <div class="cp-pay-method" data-method="cash" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="cash">
          <i class="cp-pm-ico fa-solid fa-money-bill-wave"></i><span class="cp-pm-lbl">Cash</span>
          <i class="cp-pm-check fa-solid fa-circle-check"></i>
        </div>
        <div class="cp-pay-method" data-method="paylater" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="paylater">
          <i class="cp-pm-ico fa-solid fa-clock"></i><span class="cp-pm-lbl">Later</span>
          <i class="cp-pm-check fa-solid fa-circle-check"></i>
        </div>
        <div class="cp-pay-method" data-method="riel" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="riel">
          <i class="cp-pm-ico fa-solid fa-coins"></i><span class="cp-pm-lbl">Riel &#x17DB;</span>
          <i class="cp-pm-check fa-solid fa-circle-check"></i>
        </div>
      </div>
      <div class="cp-split-inputs" id="cpSplitInputs"><div id="cpSplitRows"></div></div>

      <div class="cp-change-calc" id="cpRielCalc">
        <label><i class="fa-solid fa-coins" style="color:#e74c3c;margin-right:4px;"></i> Amount in Riel (KHR)</label>
        <input type="number" id="cpRielReceived" step="1" min="0" placeholder="0" oninput="cpCalcRielChange()" onfocus="this.select()">
        <div class="cp-change-row">
          <span class="change-label">USD Equivalent</span>
          <span class="change-amount" id="cpRielUsdEquiv">$0.00</span>
        </div>
        <div class="cp-change-row" id="cpRielChangeRow" style="display:none;">
          <span class="change-label">Change (KHR)</span>
          <span class="change-amount" id="cpRielChangeKhr">&#x17DB;0</span>
        </div>
      </div>

      <div class="cp-change-calc" id="cpChangeCalc">
        <label><i class="fa-solid fa-money-bill-wave" style="color:#55e087;margin-right:4px;"></i> Amount Received</label>
        <input type="number" id="cpCashReceived" step="0.01" min="0" placeholder="0.00" oninput="cpCalcChange()" onfocus="this.select()">
        <div class="cp-change-row">
          <span class="change-label">Change to give back</span>
          <span class="change-amount" id="cpChangeAmount">$0.00</span>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <button type="button" class="cp-pm-confirm" id="cpConfirmPayBtn">
      <i class="fa-solid fa-check" id="cpConfirmPayIcon"></i> <span id="cpConfirmPayText">Confirm Payment</span>
    </button>
  </div>
</div>

<!-- Receipt Modal -->
<div id="cpReceiptModal" class="cp-receipt-modal">
  <div class="cp-receipt-card">
    <div class="cp-receipt-head">
      <h3><i class="fa-solid fa-receipt" style="color:var(--orange,#14b8a6);"></i> Order Receipt</h3>
      <button type="button" class="cp-receipt-close" onclick="cpCloseReceipt()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="cp-receipt-body" id="cpReceiptBody">
      <div class="cp-receipt-shop">
        <div class="cp-receipt-name">The Bird Nest Cafe</div>
        <div class="cp-receipt-info">Phnom Penh, Cambodia</div>
      </div>
      <div class="cp-receipt-divider"></div>
      <div class="cp-receipt-header">
        <div class="cp-receipt-field"><span class="cp-rf-label">Receipt #</span><span class="cp-rf-value" id="cpRcptNo">-</span></div>
        <div class="cp-receipt-field"><span class="cp-rf-label">Status</span><span class="cp-rf-value cp-rf-badge" id="cpRcptStatus">-</span></div>
        <div class="cp-receipt-field"><span class="cp-rf-label">Customer</span><span class="cp-rf-value" id="cpRcptCustomer">-</span></div>
        <div class="cp-receipt-field"><span class="cp-rf-label">Cashier</span><span class="cp-rf-value" id="cpRcptCashier">-</span></div>
        <div class="cp-receipt-field"><span class="cp-rf-label">Type</span><span class="cp-rf-value" id="cpRcptType">-</span></div>
        <div class="cp-receipt-field" id="cpRcptTableRow" style="display:none"><span class="cp-rf-label">Table</span><span class="cp-rf-value" id="cpRcptTable">-</span></div>
      </div>
      <div class="cp-receipt-divider"></div>
      <div class="cp-receipt-items" id="cpRcptItems"></div>
      <div class="cp-receipt-divider"></div>
      <div class="cp-receipt-totals" id="cpRcptTotals"></div>
      <div class="cp-receipt-payments" id="cpRcptPayments"></div>
    </div>
    <div class="cp-receipt-actions" id="cpReceiptActions">
      <button type="button" class="cp-receipt-btn cp-receipt-btn-print" onclick="cpPrintReceipt()"><i class="fa-solid fa-print"></i> Print</button>
      <button type="button" class="cp-receipt-btn cp-receipt-btn-cancel" id="cpBtnCancelOrder" style="display:none" onclick="cpCancelAndContinue()"><i class="fa-solid fa-ban"></i> Cancel & Continue</button>
      <button type="button" class="cp-receipt-btn cp-receipt-btn-switch" id="cpBtnSwitchPayment" style="display:none" onclick="cpOpenSwitchModal()"><i class="fa-solid fa-arrows-rotate"></i> Switch Payment</button>
      <button type="button" class="cp-receipt-btn cp-receipt-btn-new" onclick="cpNewOrder()"><i class="fa-solid fa-plus"></i> New Order</button>
    </div>
  </div>
</div>

<!-- Switch Payment Method Modal -->
<div id="cpSwitchModal" class="cp-switch-modal">
  <div class="cp-switch-card">
    <div class="cp-switch-head">
      <h3><i class="fa-solid fa-arrows-rotate" style="color:var(--orange, #14b8a6);"></i> Switch Payment Method</h3>
      <button type="button" class="cp-switch-close" onclick="cpCloseSwitchModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <p class="cp-switch-desc">Choose a different payment method for this order:</p>
    <div class="cp-switch-methods">
      <div class="cp-pay-method selected" data-method="cash" onclick="cpConfirmSwitchMethod(this, 'cash')">
        <input type="radio" name="switch_method" value="cash" checked>
        <i class="cp-pm-ico fa-solid fa-money-bill-wave"></i><span class="cp-pm-lbl">Cash</span>
        <i class="cp-pm-check fa-solid fa-circle-check"></i>
      </div>
      <div class="cp-pay-method" data-method="riel" onclick="cpConfirmSwitchMethod(this, 'riel')">
        <input type="radio" name="switch_method" value="riel">
        <i class="cp-pm-ico fa-solid fa-coins"></i><span class="cp-pm-lbl">Riel &#x17DB;</span>
        <i class="cp-pm-check fa-solid fa-circle-check"></i>
      </div>
      <div class="cp-pay-method" data-method="paylater" onclick="cpConfirmSwitchMethod(this, 'paylater')">
        <input type="radio" name="switch_method" value="paylater">
        <i class="cp-pm-ico fa-solid fa-clock"></i><span class="cp-pm-lbl">Pay Later</span>
        <i class="cp-pm-check fa-solid fa-circle-check"></i>
      </div>
    </div>
    <button type="button" class="cp-pm-confirm" id="cpConfirmSwitchBtn" onclick="cpDoSwitchPayment()">
      <i class="fa-solid fa-check"></i> <span>Confirm</span>
    </button>
  </div>
</div>
