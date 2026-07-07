<?php
$cart = $cart ?? [];
$cart_count = $cart_count ?? 0;
$cp_subtotal = $cp_subtotal ?? 0.0;
$cp_buy3 = $cp_buy3 ?? 0.0;
$cp_free_name = $cp_free_name ?? '';
$cp_free_price = $cp_free_price ?? 0.0;
$cp_hh = $cp_hh ?? 0.0;
$cp_manual = $cp_manual ?? 0.0;
$cp_manual_label = $cp_manual_label ?? '';
$cp_after = $cp_after ?? 0.0;
$cp_tax = $cp_tax ?? 0.0;
$cp_total = $cp_total ?? 0.0;
$linked_loyalty = $linked_loyalty ?? null;
$add_to_order_mode = $add_to_order_mode ?? 0;
?>
<aside class="cart-panel" id="cartPanel">

  <div class="cp-header">
    <div class="cp-header-left">
      <i class="fa-solid fa-bag-shopping cp-hdr-icon"></i>
      <span class="cp-hdr-title">Cart</span>
      <span class="cp-hdr-count" id="cpCount"><?= $cart_count ?> item<?= $cart_count != 1 ? 's' : '' ?></span>
    </div>
    <button class="cp-clear-btn" id="cpClearBtn" onclick="cpClearCart()" <?= empty($cart) ? 'style="display:none"' : '' ?>>
      <i class="fa-solid fa-trash-can"></i> Clear
    </button>
  </div>

  <div class="cp-body" id="cpBody">

    <?php if (empty($cart)): ?>
    <div class="cp-empty" id="cpEmpty">
      <i class="fa-solid fa-mug-hot"></i>
      <p>Cart is empty</p>
      <small>Tap a drink to add it</small>
    </div>

    <?php else: ?>
    <div id="cpItems">
      <?php foreach ($cart as $i => $item):
        $qty  = (int)($item['qty'] ?? 1);
        $line = (float)($item['price'] ?? 0) * $qty;
        $meta = array_filter([
          !empty($item['size_label']) ? 'Size: '.$item['size_label']  : '',
          !empty($item['sweetness'])  ? 'Sweet: '.$item['sweetness']  : '',
          !empty($item['ice'])        ? 'Ice: '.$item['ice']          : '',
          !empty($item['milk'])       ? 'Milk: '.$item['milk']        : '',
          !empty($item['sugar'])      ? 'Sugar: '.$item['sugar']      : '',
        ]);
      ?>
      <div class="cp-item" id="cp-item-<?= $i ?>">
        <img class="cp-item-img" src="<?= e(root_url($item['image'] ?? '')) ?>" alt="<?= e($item['product_name'] ?? '') ?>" loading="lazy">
        <div class="cp-item-body">
          <div class="cp-item-top">
            <div class="cp-item-name"><?= e($item['product_name'] ?? '') ?></div>
            <button class="cp-item-remove" onclick="cpRemoveItem(<?= $i ?>)" title="Remove"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <?php if ($meta): ?><div class="cp-item-meta"><?= e(implode(' • ', $meta)) ?></div><?php endif; ?>
          <div class="cp-item-bottom">
            <div class="cp-item-price">$<span id="cp-line-<?= $i ?>"><?= number_format((float)($item['price'] ?? 0), 2) ?></span></div>
            <div class="cp-qty">
              <button onclick="cpChangeQty(<?= $i ?>, -1)">&minus;</button>
              <input type="number" id="cp-qty-<?= $i ?>" value="<?= $qty ?>" min="1" onchange="cpSetQty(<?= $i ?>,this.value)" onfocus="this.select()" onkeydown="if(event.key==='Enter'){event.preventDefault();cpSetQty(<?= $i ?>,this.value);this.blur();}">
              <button onclick="cpChangeQty(<?= $i ?>, 1)">+</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="cp-free-row" id="cpFreeRow" style="<?= $cp_buy3 > 0 ? '' : 'display:none' ?>">
        <div class="cp-free-icon">&#x1F381;</div>
        <div>
          <div style="font-size:13px;font-weight:600;color:var(--text,#1a1410);"><span id="cpFreeName"><?= e($cp_free_name) ?></span> <span class="cp-free-badge">FREE</span></div>
          <div style="font-size:11px;color:var(--text-sec,#5a4a3a);margin:2px 0;">Buy <?= BUY_X_COUNT ?> Get 1 Free</div>
          <div style="font-size:15px;font-weight:700;color:#27ae60;">FREE <s style="color:#aaa;font-size:11px;">was $<span id="cpFreePrice"><?= number_format($cp_free_price, 2) ?></span></s></div>
        </div>
      </div>
    </div>

    <div class="cp-summary" id="cpSummary">
      <div class="cp-sum-row">
        <span>Subtotal</span>
        <span id="cpSubtotal">$<?= number_format($cp_subtotal, 2) ?></span>
      </div>
      <div class="cp-sum-row discount" id="cpBuy3Row" style="<?= $cp_buy3 > 0 ? '' : 'display:none' ?>">
        <span>&#x1F389; Buy <?= BUY_X_COUNT ?> Get 1 Free</span>
        <span id="cpBuy3Amt">-$<?= number_format($cp_buy3, 2) ?></span>
      </div>
      <div class="cp-sum-row discount" id="cpHHRow" style="<?= $cp_hh > 0 ? '' : 'display:none' ?>">
        <span>&#x1F305; Happy Hour (<?= HAPPY_HOUR_DISCOUNT ?>% off)</span>
        <span id="cpHHAmt">-$<?= number_format($cp_hh, 2) ?></span>
      </div>
      <div class="cp-sum-row discount" id="cpManualRow" style="<?= $cp_manual > 0 ? '' : 'display:none' ?>">
        <span id="cpManualLabel"><?= e($cp_manual_label) ?></span>
        <span id="cpManualAmt">-$<?= number_format($cp_manual, 2) ?></span>
      </div>

      <div class="cp-sum-divider"></div>

      <div class="cp-sum-total">
        <span class="lbl">Total</span>
        <span class="amt" id="cpTotal">$<?= number_format($cp_total, 2) ?></span>
      </div>

      <div id="cpDiscountPanel">
        <?php if ($cp_manual > 0): ?>
        <button type="button" class="cp-discount-toggle remove" onclick="cpClearDiscount()">
          <i class="fa-solid fa-xmark"></i> Remove Discount
        </button>
        <?php else: ?>
        <button type="button" class="cp-discount-toggle" id="cpAddDiscBtn" onclick="cpOpenDiscount()">
          <i class="fa-solid fa-tag"></i> Add Discount
        </button>
        <?php endif; ?>
        <div id="cpDiscountForm" style="display:none">
          <div class="cp-dtype-row">
            <button type="button" class="cp-dtype-btn active" id="cpDtypePercent" onclick="cpSetDType('percent')">% Percent</button>
            <button type="button" class="cp-dtype-btn" id="cpDtypeFlat" onclick="cpSetDType('flat')">$ Flat</button>
          </div>
          <div class="cp-disc-inputs">
            <input type="number" id="cpDiscAmount" placeholder="0" min="0" step="0.01">
            <input type="text" id="cpDiscReason" placeholder="Reason (e.g. Staff, VIP)" maxlength="100">
          </div>
          <div class="cp-disc-actions">
            <button type="button" class="cp-btn-apply" onclick="cpApplyDiscount()"><i class="fa-solid fa-check"></i> Apply</button>
            <button type="button" class="cp-btn-cancel" onclick="cpCloseDiscount()">Cancel</button>
          </div>
        </div>
      </div>

      <div class="cp-sum-row" style="padding-top:2px;">
        <span>Tax (<?= TAX_RATE ?>%)</span>
        <span id="cpTax">$<?= number_format($cp_tax, 2) ?></span>
      </div>

      <?php if (!$add_to_order_mode): ?>
      <div class="cp-opt-row" style="margin-top:8px;">
        <button type="button" class="cp-opt-btn active" id="cpBtnDrinkIn" onclick="cpSetDrinkType('drink_in')">
          <i class="fa-solid fa-mug-hot"></i> Drink In
        </button>
        <button type="button" class="cp-opt-btn" id="cpBtnDrinkOut" onclick="cpSetDrinkType('drink_out')">
          <i class="fa-solid fa-bag-shopping"></i> Take Out
        </button>
      </div>
      <?php endif; ?>

      <?php if ($add_to_order_mode > 0): ?>
      <div class="cp-add-order-note">
        <i class="fa-solid fa-clock-rotate-left"></i> Adding to Pay Later Order #<?= $add_to_order_mode ?>
        <small>Payment was already set. Just add items and confirm.</small>
      </div>
      <?php endif; ?>

      <div class="cp-opt-field" style="margin-top:8px;">
        <label><i class="fa-regular fa-user"></i> Customer Name</label>
        <input type="text" id="cpCustomerName" placeholder="Leave blank for Guest">
      </div>

      <div class="cp-opt-field" id="cpTableNumberGroup">
        <label><i class="fa-solid fa-hashtag"></i> Stand Number <span style="font-weight:400;color:var(--text-muted,#9a8070);">(optional)</span></label>
        <div style="display:flex;gap:6px;align-items:center;">
          <input type="text" id="cpTableNumber" name="table_number" maxlength="10" placeholder="e.g. 1, 7, 12..." onblur="cpCheckStand(this.value)" style="flex:1;">
          <button type="button" onclick="cpToggleStandGrid()" style="background:none;border:1px solid var(--border,#e0d4c4);border-radius:8px;padding:8px 10px;font-size:12px;cursor:pointer;color:var(--text-sec,#5a4a3a);font-family:'Poppins',sans-serif;"><i class="fa-solid fa-table-cells-large"></i> Grid</button>
        </div>
        <div id="cpStandWarn" class="cp-stand-warn"><i class="fa-solid fa-triangle-exclamation"></i> <span id="cpStandWarnText"></span></div>
        <div id="cpStandGrid"></div>
      </div>

      <div class="cp-loyalty">
        <div class="cp-loyalty-info">
          <i class="fa-solid fa-star" style="color:var(--orange, #14b8a6);"></i>
          <span id="cpLoyaltyStatus" class="<?= $linked_loyalty ? 'linked' : '' ?>">
            <?php if ($linked_loyalty): ?>
              <?= e($linked_loyalty['loyalty_id']) ?> &mdash; <?= (int)$linked_loyalty['points'] ?> pts
            <?php else: ?>
              Not linked
            <?php endif; ?>
          </span>
        </div>
        <button class="cp-loyalty-btn" onclick="openLoyaltyModal()" id="cpLoyaltyBtn">
          <?= $linked_loyalty ? '<i class="fa-solid fa-circle-check"></i> Linked' : '<i class="fa-solid fa-credit-card"></i> Link' ?>
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="cp-footer" id="cpFooter" <?= empty($cart) ? 'style="display:none"' : '' ?>>
    <div style="display:none" id="cpTotalHidden">$<?= number_format($cp_total, 2) ?></div>

    <form method="post" action="<?= e(root_url('confirm_order.php')) ?>" id="cpCheckoutForm">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="order_type" id="cpOrderTypeInput" value="drink_in">
      <input type="hidden" name="is_add_to_order" value="<?= $add_to_order_mode > 0 ? '1' : '0' ?>">
      <?php if ($add_to_order_mode > 0): ?>
      <input type="hidden" name="add_to_order_id" value="<?= $add_to_order_mode ?>">
      <?php endif; ?>
      <div id="cpPaymentInputs"></div>

      <?php if (!$add_to_order_mode): ?>
      <div class="cp-pay-pills">
        <label class="cp-pay-pill" data-method="bakong" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="bakong"><i class="fa-solid fa-qrcode"></i> Bakong
        </label>
        <label class="cp-pay-pill" data-method="cash" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="cash"><i class="fa-solid fa-money-bill-wave"></i> Cash
        </label>
        <label class="cp-pay-pill" data-method="paylater" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="paylater"><i class="fa-solid fa-clock"></i> Later
        </label>
        <label class="cp-pay-pill" data-method="riel" onclick="cpTogglePayment(this)">
          <input type="checkbox" value="riel"><i class="fa-solid fa-coins"></i> Riel
        </label>
      </div>

      <div class="cp-split-inputs" id="cpSplitInputs"><div id="cpSplitRows"></div></div>

      <div class="cp-change-calc" id="cpRielCalc">
        <label><i class="fa-solid fa-coins" style="color:#e74c3c;"></i> Amount in Riel</label>
        <input type="number" id="cpRielReceived" step="1" min="0" placeholder="0" oninput="cpCalcRielChange()" onfocus="this.select()">
        <div class="cp-change-row"><span class="change-label">USD equiv.</span><span class="change-amount" id="cpRielUsdEquiv">$0.00</span></div>
        <div class="cp-change-row" id="cpRielChangeRow" style="display:none;"><span class="change-label">Change (KHR)</span><span class="change-amount" id="cpRielChangeKhr">&#x17DB;0</span></div>
      </div>

      <div class="cp-change-calc" id="cpChangeCalc">
        <label><i class="fa-solid fa-money-bill-wave" style="color:#55e087;"></i> Amount Received</label>
        <input type="number" id="cpCashReceived" step="0.01" min="0" placeholder="0.00" oninput="cpCalcChange()" onfocus="this.select()">
        <div class="cp-change-row"><span class="change-label">Change</span><span class="change-amount" id="cpChangeAmount">$0.00</span></div>
      </div>
      <?php endif; ?>

      <button type="button" class="cp-confirm-btn<?= $add_to_order_mode ? ' paylater' : '' ?>" id="cpConfirmBtn" onclick="cpOnConfirmOrderClick()">
        <i class="fa-solid fa-<?= $add_to_order_mode ? 'cart-plus' : 'credit-card' ?>" id="cpConfirmIcon"></i>
        <span id="cpConfirmText"><?= $add_to_order_mode ? 'Add to Order #'.$add_to_order_mode : 'Place Order' ?></span>
      </button>
    </form>

    <div class="cp-shortcuts">
      <?php if (!$add_to_order_mode): ?>
      <span><kbd>B</kbd> Bakong</span>
      <span><kbd>C</kbd> Cash</span>
      <span><kbd>P</kbd> Pay Later</span>
      <span><kbd>R</kbd> Riel</span>
      <?php endif; ?>
      <span><kbd>Enter</kbd> Confirm</span>
    </div>
  </div>

</aside>
