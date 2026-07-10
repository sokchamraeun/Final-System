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
$cp_promo_discount = 0;
foreach ($cart as $_pi) {
    $_pct = (int)($_pi['discount_pct'] ?? 0);
    if ($_pct > 0) {
        $_pp = (float)($_pi['price'] ?? 0);
        $_pq = (int)($_pi['qty'] ?? 1);
        $cp_promo_discount += ($_pp / (1 - $_pct / 100) - $_pp) * $_pq;
    }
}
$cp_original_subtotal = $cp_subtotal + $cp_promo_discount;
?>
<aside class="cart-panel" id="cartPanel">

  <div class="cp-header">
    <div class="cp-header-left">
      <i class="fa-solid fa-receipt cp-hdr-icon"></i>
      <span class="cp-hdr-title">Current Order</span>
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
          !empty($item['addons'])     ? $item['addons']               : '',
        ]);
        $itemPct  = (int)($item['discount_pct'] ?? 0);
        $itemNew  = (float)($item['price'] ?? 0);
        $itemOld  = $itemPct > 0 ? $itemNew / (1 - $itemPct / 100) : null;
      ?>
      <div class="cp-item" id="cp-item-<?= $i ?>">
        <div class="cp-item-img-wrap">
          <?php if ($itemPct > 0): ?><span class="cp-item-ribbon"><?= (int)$itemPct ?>%</span><?php endif; ?>
          <img class="cp-item-img" src="<?= e(root_url($item['image'] ?? '')) ?>" alt="<?= e($item['product_name'] ?? '') ?>" loading="lazy">
        </div>
        <div class="cp-item-body">
          <div class="cp-item-top">
            <div class="cp-item-name"><?= e($item['product_name'] ?? '') ?></div>
            <button class="cp-item-remove" onclick="cpRemoveItem(<?= $i ?>)" title="Remove"><i class="fa-solid fa-trash-can"></i></button>
          </div>
          <?php if ($meta): ?>
          <div class="cp-item-meta">
            <?php foreach ($meta as $m): ?><span class="cp-item-tag"><?= e($m) ?></span><?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="cp-item-bottom">
            <div class="cp-item-price-row">
              <?php if ($itemOld !== null): ?><span class="cp-item-price-old">$<?= number_format($itemOld * $qty, 2) ?></span><?php endif; ?>
              <div class="cp-item-price<?= $itemOld !== null ? ' discounted' : '' ?>">$<span id="cp-line-<?= $i ?>"><?= number_format($line, 2) ?></span></div>
            </div>
            <div class="cp-qty">
              <button onclick="cpChangeQty(<?= $i ?>, -1)">&minus;</button>
              <input type="number" id="cp-qty-<?= $i ?>" value="<?= $qty ?>" min="1" onchange="cpSetQty(<?= $i ?>,this.value)" onfocus="this.select()" onkeydown="if(event.key==='Enter'){event.preventDefault();cpSetQty(<?= $i ?>,this.value);this.blur();}">
              <button onclick="cpChangeQty(<?= $i ?>, 1)">+</button>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cp-summary" id="cpSummary">
      <div class="cp-sum-row">
        <span>Subtotal</span>
        <span id="cpSubtotal">$<?= number_format($cp_original_subtotal, 2) ?></span>
      </div>
      <div class="cp-sum-row discount" id="cpPromoRow" style="<?= $cp_promo_discount > 0 ? '' : 'display:none' ?>">
        <span><i class="fa-solid fa-tag"></i> Promo Discount</span>
        <span id="cpPromoAmt">-$<?= number_format($cp_promo_discount, 2) ?></span>
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

      <?php if ((float)TAX_RATE > 0): ?>
      <div class="cp-sum-row" style="padding-top:2px;" id="cpTaxRow">
        <span>Tax (<?= TAX_RATE ?>%)</span>
        <span id="cpTax">$<?= number_format($cp_tax, 2) ?></span>
      </div>
      <?php endif; ?>

      <?php if (!$add_to_order_mode): ?>
      <div class="cp-opt-row" style="margin-top:8px;">
        <button style="border-radius: 4px;" type="button" class="cp-opt-btn active" id="cpBtnDrinkIn" onclick="cpSetDrinkType('drink_in')">
          <i class="fa-solid fa-mug-hot"></i> Drink In
        </button>
        <button style="border-radius: 4px;" type="button" class="cp-opt-btn" id="cpBtnDrinkOut" onclick="cpSetDrinkType('drink_out')">
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

      <div class="cp-opt-row-fields" style="display:flex;gap:8px;margin-top:8px;">
        <div class="cp-opt-field" style="margin:0;flex:1;">
          <label><i class="fa-regular fa-user"></i> Customer Name</label>
          <input style="border-radius:4px;" type="text" id="cpCustomerName" placeholder="Leave blank for Guest">
        </div>
        <div class="cp-opt-field" id="cpTableNumberGroup" style="margin:0;flex:1;">
          <label><i class="fa-solid fa-hashtag"></i> Stand <span style="font-weight:400;color:var(--text-muted,#9a8070);">(opt)</span></label>
          <select id="cpTableNumber" name="table_number" onchange="cpCheckStand(this.value)" style="width:100%;padding:7px 10px;border-radius:4px;font-size:13px;outline:none;border:1.5px solid var(--border,#e0d4c4);background:var(--bg,#f4efe9);color:var(--text,#1a1410);font-family:'Poppins',sans-serif;cursor:pointer;">
            <option value="">Select stand</option>
            <?php for ($i = 1; $i <= STAND_COUNT; $i++): ?>
            <option value="<?= $i ?>">Stand #<?= $i ?></option>
            <?php endfor; ?>
          </select>
          <div id="cpStandWarn" class="cp-stand-warn"><i class="fa-solid fa-triangle-exclamation"></i> <span id="cpStandWarnText"></span></div>
        </div>
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

    <form method="post" action="<?= e(BASE_URL . '/confirm_order.php') ?>" id="cpCheckoutForm">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="order_type" id="cpOrderTypeInput" value="drink_in">
      <input type="hidden" name="is_add_to_order" value="<?= $add_to_order_mode > 0 ? '1' : '0' ?>">
      <?php if ($add_to_order_mode > 0): ?>
      <input type="hidden" name="add_to_order_id" value="<?= $add_to_order_mode ?>">
      <?php endif; ?>
      <div id="cpPaymentInputs"></div>

      <button type="button" class="cp-confirm-btn<?= $add_to_order_mode ? ' paylater' : '' ?>" id="cpConfirmBtn" onclick="cpOnConfirmOrderClick()">
        <i class="fa-solid fa-<?= $add_to_order_mode ? 'cart-plus' : 'credit-card' ?>" id="cpConfirmIcon"></i>
        <span id="cpConfirmText"><?= $add_to_order_mode ? 'Add to Order #'.$add_to_order_mode : 'Place Order' ?></span>
      </button>
    </form>
  </div>

</aside>
