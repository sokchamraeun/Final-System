<?php
$csrf_token = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$add_to_order_mode = $add_to_order_mode ?? 0;
?>
<script>
const CP_KHR_RATE = <?= defined('KHR_RATE') ? (int)KHR_RATE : 4100 ?>;

// ── Theme ──
(function() {
  var saved = localStorage.getItem('theme') || 'dark';
  if (saved === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
})();

function toggleTheme() {
  var html = document.documentElement, isDark = html.getAttribute('data-theme') === 'dark';
  if (isDark) { html.removeAttribute('data-theme'); localStorage.setItem('theme','light'); document.getElementById('themeIcon').className='fa-solid fa-moon'; }
  else        { html.setAttribute('data-theme','dark'); localStorage.setItem('theme','dark'); document.getElementById('themeIcon').className='fa-solid fa-sun'; }
}
document.addEventListener('DOMContentLoaded', function() {
  if ((localStorage.getItem('theme') || 'dark') === 'dark') document.getElementById('themeIcon').className = 'fa-solid fa-sun';
});

// ── Constants from PHP ──
var CSRF        = '<?= e($csrf_token) ?>';
var BUY_X_COUNT = <?= (int)BUY_X_COUNT ?>;
var ADD_TO_ORDER_MODE = <?= (int)$add_to_order_mode ?>;
var CAFE_TABLES = [];
var CP_STAND_MAX = <?= STAND_COUNT ?>;
var CP_TAX_RATE  = <?= TAX_RATE ?>;
var ROOT_URL     = '<?= ROOT_URL ?>';
var BASE_URL     = '<?= BASE_URL ?>';   // pos-cafe app root (migrated endpoints live here)

// ── Escape HTML for JS-built elements ──
function escH(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── PRODUCT MODAL ──
var product = {}, modalQty = 1, modalUnitPrice = 0;

function openModal(id, name, price, img, cat, desc, badge, hasSizes, sizes, iceLevels, sugarLevels, milkLevels) {
  var p = Number(price) || 0;
  product = { id: id, name: name, price: p, cat: cat };
  modalQty = 1; modalUnitPrice = p;
  document.getElementById('modalImg').src = img;
  var mb = document.getElementById('modalBadge');
  if (mb) { mb.textContent = badge || ''; mb.style.display = badge ? 'flex' : 'none'; }
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalDesc').textContent = desc || '';
  document.getElementById('modalPrice').textContent = '$' + p.toFixed(2);
  document.getElementById('modalQtyDisplay').textContent = '1';
  // ── Ice pills (dynamic from product levels) ──
  buildLevelPills('ice', iceLevels, 'Normal Ice');

  // ── Sugar pills (dynamic from product levels) ──
  buildLevelPills('sugar', sugarLevels, '');

  // ── Milk pills (dynamic from product levels) ──
  buildLevelPills('milk', milkLevels, 'Fresh Milk');

  // ── Size pills (render in given order; default = Medium or first) ──
  var sizeWrap = document.getElementById('optSize');
  var pills = document.getElementById('sizePills');
  pills.innerHTML = '';
  if (hasSizes && Array.isArray(sizes) && sizes.length) {
    sizes.forEach(function(s) {
      var b = document.createElement('button');
      b.className = 'pm-btn';
      b.dataset.group = 'size';
      b.dataset.value = s.code;
      b.dataset.price = s.price;
      b.textContent = s.label + ' $' + Number(s.price).toFixed(2);
      b.onclick = function(){ selectSize(b); };
      pills.appendChild(b);
    });
    // default Medium if present else first
    var def = pills.querySelector('[data-value="M"]') || pills.firstChild;
    if (def) { def.classList.add('active'); modalUnitPrice = Number(def.dataset.price) || p; }
    document.getElementById('modalPrice').textContent = '$' + modalUnitPrice.toFixed(2);
    sizeWrap.style.display = 'block';
  } else {
    sizeWrap.style.display = 'none';
  }

  updateModalTotal();
  document.getElementById('modal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

// Open the product modal using a card's data-product-* attributes (avoids inline-onclick string interpolation)
function openModalFromCard(card) {
  if (!card) return;
  var sizes = [];
  try { sizes = JSON.parse(card.dataset.productSizes || '[]'); } catch (e) { sizes = []; }
  var iceLevels = [];
  try { iceLevels = JSON.parse(card.dataset.productIceLevels || '[]'); } catch (e) { iceLevels = []; }
  var sugarLevels = [];
  try { sugarLevels = JSON.parse(card.dataset.productSugarLevels || '[]'); } catch (e) { sugarLevels = []; }
  var milkLevels = [];
  try { milkLevels = JSON.parse(card.dataset.productMilkLevels || '[]'); } catch (e) { milkLevels = []; }
  openModal(card.dataset.productId, card.dataset.productName||'', Number(card.dataset.productPrice||0), card.dataset.productImage||'', card.dataset.productCategory||'', card.dataset.productDesc||'', card.dataset.productBadge||'', card.dataset.productHasSizes==='1', sizes, iceLevels, sugarLevels, milkLevels);
}

function closeModal() { document.getElementById('modal').style.display = 'none'; document.body.style.overflow = ''; }
function changeQty(delta) { modalQty = Math.max(1, Math.min(10, modalQty + delta)); document.getElementById('modalQtyDisplay').textContent = modalQty; updateModalTotal(); }
function updateModalTotal() { document.getElementById('modalTotalDisplay').textContent = '$' + (modalUnitPrice * modalQty).toFixed(2); }
function selectPill(pill) { pill.closest('.pm-grid').querySelectorAll('.pm-btn').forEach(function(p) { p.classList.remove('active'); }); pill.classList.add('active'); }
function getPillValue(groupId) { var a = document.querySelector('#' + groupId + ' .pm-btn.active'); return a ? a.dataset.value : ''; }

function selectSize(pill) {
  pill.closest('.pm-grid').querySelectorAll('.pm-btn').forEach(function(p){ p.classList.remove('active'); });
  pill.classList.add('active');
  modalUnitPrice = Number(pill.dataset.price) || modalUnitPrice;
  document.getElementById('modalPrice').textContent = '$' + modalUnitPrice.toFixed(2);
  updateModalTotal();
}

// ── Build dynamic level pills (ice, sugar) ──
function buildLevelPills(group, levels, defaultVal) {
  var wrap = document.getElementById('opt' + group.charAt(0).toUpperCase() + group.slice(1));
  var grid = document.getElementById(group + 'Pills');
  grid.innerHTML = '';
  if (!Array.isArray(levels) || levels.length === 0) {
    wrap.style.display = 'none';
    return;
  }
  wrap.style.display = 'block';
  levels.forEach(function(l) {
    var b = document.createElement('button');
    b.className = 'pm-btn' + (l.name === defaultVal ? ' active' : '');
    b.dataset.group = group;
    b.dataset.value = l.name;
    b.textContent = l.name;
    b.onclick = function(){ selectPill(b); };
    grid.appendChild(b);
  });
}

// ── ADD TO CART (from modal) ──
function addToCart() {
  var btn = document.querySelector('.pm-add');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';
  var params = new URLSearchParams({ id: product.id, qty: modalQty, csrf_token: CSRF });
  if (document.getElementById('optIce').style.display !== 'none')  params.append('ice', getPillValue('icePills'));
  if (document.getElementById('optMilk').style.display !== 'none')      params.append('milk',      getPillValue('milkPills'));
  if (document.getElementById('optSize').style.display !== 'none')      params.append('size',      getPillValue('sizePills'));
  if (document.getElementById('optSugar').style.display !== 'none')     params.append('sugar',     getPillValue('sugarPills'));

  fetch(BASE_URL+'/api/cart/add.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body: params.toString() })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) { showToast(data.message || 'Error', 'error'); return; }
      showToast('Added to cart!', 'success');
      closeModal();
      loadCartPanel();
    })
    .catch(function() { showToast('Error adding to cart', 'error'); })
    .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Add to Cart'; });
}

// ── QUICK ADD ──
function quickAdd(productId, price) {
  fetch(BASE_URL+'/api/cart/add.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body: new URLSearchParams({ id: productId, qty: 1, csrf_token: CSRF }).toString() })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) { showToast(data.message || 'Error', 'error'); return; }
      showToast('Added!', 'success');
      loadCartPanel();
    })
    .catch(function() { showToast('Error', 'error'); });
}

// ── LOAD & RENDER CART PANEL ──
function loadCartPanel() {
  fetch(ROOT_URL+'/cart_refresh.php')
    .then(function(r) { return r.json(); })
    .then(function(data) { renderCartPanel(data); })
    .catch(function() {});
}

function renderCartPanel(data) {
  var countEl = document.getElementById('cpCount');
  if (countEl) countEl.textContent = data.count + ' item' + (data.count != 1 ? 's' : '');

  var footer  = document.getElementById('cpFooter');
  var clearBtn = document.getElementById('cpClearBtn');
  var body    = document.getElementById('cpBody');

  if (data.items.length === 0) {
    if (body) body.innerHTML = '<div class="cp-empty" id="cpEmpty"><i class="fa-solid fa-mug-hot"></i><p>Cart is empty</p><small>Tap a drink to add it</small></div>';
    if (footer) footer.style.display = 'none';
    if (clearBtn) clearBtn.style.display = 'none';
    return;
  }

  if (clearBtn) clearBtn.style.display = '';
  if (footer)  footer.style.display = '';

  var itemsHtml = '<div id="cpItems">';
  data.items.forEach(function(item) {
    var meta = [
      item.size_label ? 'Size: '  + item.size_label : '',
      item.sweetness ? 'Sweet: ' + item.sweetness : '',
      item.ice       ? 'Ice: '   + item.ice       : '',
      item.milk      ? 'Milk: '  + item.milk      : '',
      item.sugar     ? 'Sugar: ' + item.sugar     : '',
    ].filter(Boolean).join(' • ');
    itemsHtml += '<div class="cp-item" id="cp-item-' + item.index + '">' +
      '<img class="cp-item-img" src="' + (item.image ? ROOT_URL + '/' + escH(item.image) : '') + '" alt="' + escH(item.product_name) + '" loading="lazy">' +
      '<div class="cp-item-body">' +
        '<div class="cp-item-top">' +
          '<div class="cp-item-name">' + escH(item.product_name) + '</div>' +
          '<button class="cp-item-remove" onclick="cpRemoveItem(' + item.index + ')" title="Remove"><i class="fa-solid fa-xmark"></i></button>' +
        '</div>' +
        (meta ? '<div class="cp-item-meta">' + escH(meta) + '</div>' : '') +
        '<div class="cp-item-bottom">' +
          '<div class="cp-item-price">$<span id="cp-line-' + item.index + '">' + item.price.toFixed(2) + '</span></div>' +
          '<div class="cp-qty">' +
            '<button onclick="cpChangeQty(' + item.index + ',-1)">&minus;</button>' +
            '<input type="number" id="cp-qty-' + item.index + '" value="' + item.qty + '" min="1" onchange="cpSetQty(' + item.index + ',this.value)" onfocus="this.select()" onkeydown="if(event.key===\'Enter\'){event.preventDefault();cpSetQty(' + item.index + ',this.value);this.blur();}">' +
            '<button onclick="cpChangeQty(' + item.index + ',1)">+</button>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  });

  var buy3v = parseFloat(data.buy3);
  itemsHtml += '<div class="cp-free-row" id="cpFreeRow" style="' + (buy3v > 0 ? '' : 'display:none') + '">' +
    '<div class="cp-free-icon">&#x1F381;</div>' +
    '<div>' +
      '<div style="font-size:13px;font-weight:600;color:var(--text,#1a1410);"><span id="cpFreeName">' + escH(data.buy3_name) + '</span> <span class="cp-free-badge">FREE</span></div>' +
      '<div style="font-size:11px;color:var(--text-sec,#5a4a3a);margin:2px 0;">Buy ' + data.buy3_count + ' Get 1 Free</div>' +
      '<div style="font-size:15px;font-weight:700;color:#27ae60;">FREE <s style="color:#aaa;font-size:11px;">was $<span id="cpFreePrice">' + data.buy3_price + '</span></s></div>' +
    '</div>' +
  '</div>';
  itemsHtml += '</div>';

  var manualV = parseFloat(data.manual);
  var hhV     = parseFloat(data.happy_hour);
  var b3V     = parseFloat(data.buy3);

  itemsHtml += '<div class="cp-summary" id="cpSummary">' +
    '<div class="cp-sum-row"><span>Subtotal</span><span id="cpSubtotal">$' + data.subtotal + '</span></div>' +
    '<div class="cp-sum-row discount" id="cpBuy3Row" style="' + (b3V > 0 ? '' : 'display:none') + '">' +
      '<span>&#x1F389; Buy ' + data.buy3_count + ' Get 1 Free</span><span id="cpBuy3Amt">-$' + data.buy3 + '</span>' +
    '</div>' +
    '<div class="cp-sum-row discount" id="cpHHRow" style="' + (hhV > 0 ? '' : 'display:none') + '">' +
      '<span>&#x1F305; Happy Hour (' + data.happy_hour_pct + '% off)</span><span id="cpHHAmt">-$' + data.happy_hour + '</span>' +
    '</div>' +
    '<div class="cp-sum-row discount" id="cpManualRow" style="' + (manualV > 0 ? '' : 'display:none') + '">' +
      '<span id="cpManualLabel">' + escH(data.manual_label) + '</span><span id="cpManualAmt">-$' + data.manual + '</span>' +
    '</div>' +
    '<div class="cp-sum-divider"></div>' +
    '<div class="cp-sum-total"><span class="lbl">Total</span><span class="amt" id="cpTotal">$' + data.total + '</span></div>';

  itemsHtml += '<div id="cpDiscountPanel">';
  if (manualV > 0) {
    itemsHtml += '<button type="button" class="cp-discount-toggle remove" onclick="cpClearDiscount()"><i class="fa-solid fa-xmark"></i> Remove Discount</button>';
  } else {
    itemsHtml += '<button type="button" class="cp-discount-toggle" id="cpAddDiscBtn" onclick="cpOpenDiscount()"><i class="fa-solid fa-tag"></i> Add Discount</button>';
  }
  itemsHtml += '<div id="cpDiscountForm" style="display:none">' +
    '<div class="cp-dtype-row"><button type="button" class="cp-dtype-btn active" id="cpDtypePercent" onclick="cpSetDType(\'percent\')">% Percent</button><button type="button" class="cp-dtype-btn" id="cpDtypeFlat" onclick="cpSetDType(\'flat\')">$ Flat</button></div>' +
    '<div class="cp-disc-inputs"><input type="number" id="cpDiscAmount" placeholder="0" min="0" step="0.01"><input type="text" id="cpDiscReason" placeholder="Reason (e.g. Staff, VIP)" maxlength="100"></div>' +
    '<div class="cp-disc-actions"><button type="button" class="cp-btn-apply" onclick="cpApplyDiscount()"><i class="fa-solid fa-check"></i> Apply</button><button type="button" class="cp-btn-cancel" onclick="cpCloseDiscount()">Cancel</button></div>' +
  '</div></div>';

  itemsHtml += '<div class="cp-sum-row" style="padding-top:2px;"><span>Tax (' + CP_TAX_RATE + '%)</span><span id="cpTax">$' + data.tax + '</span></div>';

  var prevDrinkType    = (document.getElementById('cpOrderTypeInput') || {}).value || 'drink_in';
  var prevCustomerName = (document.getElementById('cpCustomerName')   || {}).value || '';
  var prevTableNumber  = (document.getElementById('cpTableNumber')    || {}).value || '';

  if (!ADD_TO_ORDER_MODE) {
    itemsHtml += '<div class="cp-opt-row" style="margin-top:8px;">' +
      '<button type="button" class="cp-opt-btn active" id="cpBtnDrinkIn" onclick="cpSetDrinkType(\'drink_in\')"><i class="fa-solid fa-mug-hot"></i> Drink In</button>' +
      '<button type="button" class="cp-opt-btn" id="cpBtnDrinkOut" onclick="cpSetDrinkType(\'drink_out\')"><i class="fa-solid fa-bag-shopping"></i> Take Out</button>' +
      '</div>';
  }

  if (ADD_TO_ORDER_MODE) {
    itemsHtml += '<div class="cp-add-order-note"><i class="fa-solid fa-clock-rotate-left"></i> Adding to Pay Later Order #' + ADD_TO_ORDER_MODE + '<small>Payment was already set. Just add items and confirm.</small></div>';
  }

  itemsHtml += '<div class="cp-opt-field" style="margin-top:8px;"><label><i class="fa-regular fa-user"></i> Customer Name</label>' +
    '<input type="text" id="cpCustomerName" placeholder="Leave blank for Guest"></div>';

  itemsHtml += '<div class="cp-opt-field" id="cpTableNumberGroup">' +
    '<label><i class="fa-solid fa-hashtag"></i> Stand Number <span style="font-weight:400;color:var(--text-muted,#9a8070);">(optional)</span></label>' +
    '<div style="display:flex;gap:6px;align-items:center;">' +
    '<input type="text" id="cpTableNumber" name="table_number" maxlength="10" placeholder="e.g. 1, 7, 12..." onblur="cpCheckStand(this.value)" style="flex:1;">' +
    '<button type="button" onclick="cpToggleStandGrid()" style="background:none;border:1px solid var(--border,#e0d4c4);border-radius:8px;padding:8px 10px;font-size:12px;cursor:pointer;color:var(--text-sec,#5a4a3a);font-family:\'Poppins\',sans-serif;"><i class="fa-solid fa-table-cells-large"></i> Grid</button></div>' +
    '<div id="cpStandWarn" class="cp-stand-warn"><i class="fa-solid fa-triangle-exclamation"></i> <span id="cpStandWarnText"></span></div>' +
    '<div id="cpStandGrid"></div></div>';

  var loyaltyStatus = document.getElementById('cpLoyaltyStatus');
  var loyaltyLinked = loyaltyStatus && loyaltyStatus.classList.contains('linked');
  itemsHtml += '<div class="cp-loyalty">' +
    '<div class="cp-loyalty-info"><i class="fa-solid fa-star" style="color:var(--orange, #14b8a6);"></i>' +
    '<span id="cpLoyaltyStatus"' + (loyaltyLinked ? ' class="linked"' : '') + '>' + (loyaltyStatus ? loyaltyStatus.textContent : 'Not linked') + '</span></div>' +
    '<button class="cp-loyalty-btn" onclick="openLoyaltyModal()" id="cpLoyaltyBtn">' + (loyaltyLinked ? '<i class="fa-solid fa-circle-check"></i> Linked' : '<i class="fa-solid fa-credit-card"></i> Link') + '</button>' +
  '</div>';

  itemsHtml += '</div>';

  if (body) body.innerHTML = itemsHtml;

  var totalHidden = document.getElementById('cpTotalHidden');
  if (totalHidden) totalHidden.textContent = '$' + data.total;

  if (!ADD_TO_ORDER_MODE) cpSetDrinkType(prevDrinkType);

  var cnEl = document.getElementById('cpCustomerName');
  if (cnEl && prevCustomerName) cnEl.value = prevCustomerName;
  var tnEl = document.getElementById('cpTableNumber');
  if (tnEl && prevTableNumber) tnEl.value = prevTableNumber;
}

// ── CART ITEM OPERATIONS ──
function cpChangeQty(index, delta) {
  var inp = document.getElementById('cp-qty-' + index);
  if (!inp) return;
  var qty = Math.max(1, parseInt(inp.value) + delta);
  inp.value = qty;
  fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_update=1&index='+index+'&qty='+qty })
    .then(function(r) { return r.json(); })
    .then(function() { loadCartPanel(); });
}

function cpSetQty(index, val) {
  var qty = Math.max(1, parseInt(val) || 1);
  var inp = document.getElementById('cp-qty-' + index);
  if (inp) inp.value = qty;
  fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_update=1&index='+index+'&qty='+qty })
    .then(function(r) { return r.json(); })
    .then(function() { loadCartPanel(); });
}

function cpRemoveItem(index) {
  var row = document.getElementById('cp-item-' + index);
  if (row) { row.style.opacity='0'; row.style.transform='translateX(20px)'; row.style.transition='all .25s'; }
  setTimeout(function() {
    fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove=1&index='+index })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.remaining === 0) { loadCartPanel(); return; }
        loadCartPanel();
      });
  }, 250);
}

function cpClearCart() {
  if (!confirm('Remove all items from the cart?')) return;
  fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_clear=1' })
    .then(function() { loadCartPanel(); });
}

// ── REFRESH SUMMARY FROM AJAX RESPONSE (qty update) ──
function cpRefreshSummaryFromData(data) {
  var set = function(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; };
  set('cpSubtotal', '$' + data.cartSubtotal);
  set('cpTax',      '$' + data.tax);

  var newTotal = '$' + data.cartTotal;
  set('cpTotal', newTotal);
  var st = document.getElementById('cpSubtotal');

  var b3r = document.getElementById('cpBuy3Row');
  if (b3r) b3r.style.display = parseFloat(data.buy3_discount) > 0 ? '' : 'none';
  set('cpBuy3Amt', '-$' + (data.buy3_discount || '0.00'));

  var hhr = document.getElementById('cpHHRow');
  if (hhr) hhr.style.display = parseFloat(data.happy_hour_discount) > 0 ? '' : 'none';
  set('cpHHAmt', '-$' + (data.happy_hour_discount || '0.00'));

  var mdr = document.getElementById('cpManualRow');
  if (mdr) mdr.style.display = parseFloat(data.manual_discount) > 0 ? '' : 'none';
  set('cpManualAmt', '-$' + (data.manual_discount || '0.00'));

  cpCalcChange();
  cpUpdateSplitAmounts();
}

// ── DISCOUNT PANEL ──
var _cpDiscountType = 'percent';
function cpOpenDiscount() {
  var btn = document.getElementById('cpAddDiscBtn');
  if (btn) btn.style.display = 'none';
  var form = document.getElementById('cpDiscountForm');
  if (form) { form.style.display = 'block'; }
  var amtInput = document.getElementById('cpDiscAmount');
  if (amtInput) amtInput.focus();
}
function cpCloseDiscount() {
  var form = document.getElementById('cpDiscountForm');
  if (form) form.style.display = 'none';
  var btn = document.getElementById('cpAddDiscBtn');
  if (btn) btn.style.display = '';
}
function cpSetDType(type) {
  _cpDiscountType = type;
  var p = document.getElementById('cpDtypePercent'), f = document.getElementById('cpDtypeFlat');
  if (p) p.classList.toggle('active', type === 'percent');
  if (f) f.classList.toggle('active', type === 'flat');
  var inp = document.getElementById('cpDiscAmount');
  if (inp) inp.placeholder = type === 'percent' ? '0  (e.g. 10 = 10%)' : '0.00  (e.g. 5.00)';
}
function cpApplyDiscount() {
  var amount = parseFloat(document.getElementById('cpDiscAmount').value) || 0;
  var reason = document.getElementById('cpDiscReason').value.trim();
  if (amount <= 0) { alert('Please enter a discount amount.'); return; }
  fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'ajax_apply_discount=1&type='+encodeURIComponent(_cpDiscountType)+'&amount='+amount+'&reason='+encodeURIComponent(reason) })
    .then(function() { loadCartPanel(); });
}
function cpClearDiscount() {
  fetch(ROOT_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_clear_discount=1' })
    .then(function() { loadCartPanel(); });
}

// ── PAYMENT METHODS ──
function cpTogglePayment(label) {
  var cb = label.querySelector('input[type="checkbox"]');
  var value = cb.value;
  if (value === 'paylater' || value === 'riel') {
    document.querySelectorAll('.cp-pay-pill input[type="checkbox"]').forEach(function(c) { c.checked = false; });
    cb.checked = true;
  } else {
    var pl = document.querySelector('.cp-pay-pill input[value="paylater"]');
    if (pl && pl.checked) pl.checked = false;
    var rielCb = document.querySelector('.cp-pay-pill input[value="riel"]');
    if (rielCb && rielCb.checked) rielCb.checked = false;
    cb.checked = !cb.checked;
  }
  if (value === 'riel' && !cb.checked) {
    var ri = document.getElementById('cpRielReceived');
    if (ri) ri.value = '';
  }
  document.querySelectorAll('.cp-pay-pill').forEach(function(el) {
    el.classList.toggle('selected', el.querySelector('input[type="checkbox"]').checked);
  });
  var selected = cpGetSelected();
  cpUpdateConfirmBtn(selected);
  cpUpdateSplitInputs();
}

function cpGetSelected() {
  var sel = [];
  document.querySelectorAll('.cp-pay-pill input[type="checkbox"]:checked').forEach(function(cb) { sel.push(cb.value); });
  return sel;
}

function cpGetCartTotal() {
  var el = document.getElementById('cpTotal');
  if (!el) return 0;
  return parseFloat(el.textContent.replace('$','').replace(/,/g,'')) || 0;
}

function cpUpdateConfirmBtn(selected) {
  var btn  = document.getElementById('cpConfirmPayBtn');
  var icon = document.getElementById('cpConfirmPayIcon');
  var text = document.getElementById('cpConfirmPayText');
  var cc   = document.getElementById('cpChangeCalc');
  var rc   = document.getElementById('cpRielCalc');
  if (!btn) return;

  btn.className = 'cp-pm-confirm';
  if (cc) cc.classList.remove('visible');
  if (rc) rc.classList.remove('visible');

  if (selected.includes('paylater')) {
    if (icon) icon.className = 'fa-solid fa-clock';
    if (text) text.textContent = 'Place Pay Later Order';
    btn.classList.add('paylater');
  } else if (selected.length > 1) {
    if (icon) icon.className = 'fa-solid fa-layer-group';
    if (text) text.textContent = 'Confirm Split Payment';
    btn.classList.add('split');
    if (selected.includes('cash') && cc) {
      cc.classList.add('visible');
      setTimeout(function() { var cr = document.getElementById('cpCashReceived'); if (cr) cr.focus(); }, 50);
    }
  } else if (selected.includes('riel')) {
    if (icon) icon.className = 'fa-solid fa-coins';
    if (text) text.textContent = 'Confirm Riel Payment';
    btn.classList.add('riel');
    if (rc) {
      rc.classList.add('visible');
      var total = cpGetCartTotal();
      var ri = document.getElementById('cpRielReceived');
      if (ri && !ri.value) ri.value = Math.round(total * CP_KHR_RATE / 100) * 100;
      cpCalcRielChange();
      setTimeout(function() { if (ri) ri.focus(); }, 50);
    }
  } else if (selected.includes('cash')) {
    if (icon) icon.className = 'fa-solid fa-money-bill-wave';
    if (text) text.textContent = 'Confirm Cash Payment';
    btn.classList.add('cash');
    if (cc) cc.classList.add('visible');
    setTimeout(function() { var cr = document.getElementById('cpCashReceived'); if (cr) cr.focus(); }, 50);
  } else if (selected.includes('bakong')) {
    if (icon) icon.className = 'fa-solid fa-qrcode';
    if (text) text.textContent = 'Generate Bakong QR';
    btn.classList.add('bakong');
  } else {
    if (icon) icon.className = 'fa-solid fa-check';
    if (text) text.textContent = 'Confirm Payment';
  }
}

// ── PAYMENT MODAL OPEN/CLOSE ──
function cpOpenPayModal() {
  var total = cpGetCartTotal();
  if (total <= 0) return;
  var sub = total / (1 + CP_TAX_RATE / 100);
  var tax = total - sub;
  document.getElementById('cpPmSubtotal').textContent = '$' + sub.toFixed(2);
  document.getElementById('cpPmTax').textContent = '$' + tax.toFixed(2);
  document.getElementById('cpPmTotal').textContent = '$' + total.toFixed(2);
  var khr = Math.round(total * CP_KHR_RATE / 100) * 100;
  var khrEl = document.getElementById('cpPmKhr');
  if (khrEl) khrEl.textContent = '៛ ' + khr.toLocaleString();
  document.getElementById('cpPayModal').classList.add('active');
}
function cpClosePayModal() {
  document.getElementById('cpPayModal').classList.remove('active');
  document.querySelectorAll('.cp-pay-pill input[type="checkbox"]').forEach(function(c) {
    c.checked = false;
    c.closest('.cp-pay-pill').classList.remove('selected');
  });
  var cr = document.getElementById('cpCashReceived'); if (cr) cr.value = '';
  var ri = document.getElementById('cpRielReceived'); if (ri) ri.value = '';
  cpUpdateConfirmBtn([]);
  cpUpdateSplitInputs();
}
function cpOnConfirmOrderClick() {
  if (typeof ADD_TO_ORDER_MODE !== 'undefined' && ADD_TO_ORDER_MODE) {
    document.getElementById('cpCheckoutForm').requestSubmit();
    return;
  }
  cpOpenPayModal();
}

// ── SPLIT PAYMENT ──
function cpInputToUsd(inp) {
  var val = Math.max(0, parseFloat(inp.value) || 0);
  return inp.dataset.currency === 'khr' ? val / CP_KHR_RATE : val;
}
function cpSetInputUsd(inp, usd) {
  if (inp.dataset.currency === 'khr') {
    inp.value = Math.round(usd * CP_KHR_RATE / 100) * 100;
    var d = inp.parentElement && inp.parentElement.querySelector('.cp-khr-usd');
    if (d) d.textContent = '≈ $' + usd.toFixed(2);
  } else {
    inp.value = usd.toFixed(2);
  }
}

function cpUpdateSplitInputs() {
  var selected = cpGetSelected();
  var si = document.getElementById('cpSplitInputs');
  var sr = document.getElementById('cpSplitRows');
  if (!si || !sr) return;
  if (selected.includes('paylater') || selected.length <= 1) { si.classList.remove('active'); sr.innerHTML = ''; return; }
  si.classList.add('active');
  var total = cpGetCartTotal();
  var each  = Math.floor((total / selected.length) * 100) / 100;
  var rem   = Math.round((total - each * selected.length) * 100) / 100;
  var html  = '';
  selected.forEach(function(m, i) {
    var usd = i === selected.length - 1 ? (each + rem).toFixed(2) : each.toFixed(2);
    if (m === 'riel') {
      var khr = Math.round(parseFloat(usd) * CP_KHR_RATE / 100) * 100;
      html += '<div class="cp-split-row"><label>Riel &#x17DB;</label>' +
        '<div style="display:flex;flex-direction:column;gap:3px;flex:1;">' +
        '<input type="number" step="1" class="cp-split-amount" value="' + khr + '" data-method="riel" data-currency="khr" oninput="cpOnSplitChange(this)">' +
        '<span class="cp-khr-usd" style="font-size:11px;color:#888;">≈ $' + usd + '</span>' +
        '</div></div>';
    } else {
      var lbl = m.charAt(0).toUpperCase() + m.slice(1);
      html += '<div class="cp-split-row"><label>' + lbl + '</label><input type="number" step="0.01" class="cp-split-amount" value="' + usd + '" data-method="' + m + '" oninput="cpOnSplitChange(this)"></div>';
    }
  });
  sr.innerHTML = html;
}

function cpUpdateSplitAmounts() {
  var selected = cpGetSelected();
  if (selected.length < 2) return;
  var total = cpGetCartTotal();
  var each  = Math.floor((total / selected.length) * 100) / 100;
  var rem   = Math.round((total - each * selected.length) * 100) / 100;
  var inputs = document.querySelectorAll('.cp-split-amount');
  inputs.forEach(function(inp, i) {
    cpSetInputUsd(inp, i === inputs.length - 1 ? each + rem : each);
  });
}

function cpOnSplitChange(changedInp) {
  var total = cpGetCartTotal();
  var inputs = Array.from(document.querySelectorAll('.cp-split-amount'));
  var changedUsd = cpInputToUsd(changedInp);
  if (changedInp.dataset.currency === 'khr') {
    var d = changedInp.parentElement && changedInp.parentElement.querySelector('.cp-khr-usd');
    if (d) d.textContent = '≈ $' + changedUsd.toFixed(2);
  }
  var others = inputs.filter(function(inp) { return inp !== changedInp; });
  if (others.length === 1) {
    var remaining = total - changedUsd;
    if (remaining < 0) { cpSetInputUsd(changedInp, total); cpSetInputUsd(others[0], 0); }
    else cpSetInputUsd(others[0], remaining);
  }
}

// ── CHANGE CALCULATOR ──
function cpCalcChange() {
  var received = parseFloat(document.getElementById('cpCashReceived')?.value) || 0;
  var total    = cpGetCartTotal();
  var change   = received - total;
  var el       = document.getElementById('cpChangeAmount');
  if (!el) return;
  if (received === 0) { el.textContent = '$0.00'; el.className = 'change-amount'; return; }
  if (change < 0) { el.textContent = 'Need $' + Math.abs(change).toFixed(2) + ' more'; el.className = 'change-amount not-enough'; }
  else            { el.textContent = '$' + change.toFixed(2); el.className = 'change-amount'; }
}

function cpCalcRielChange() {
  var khr       = parseFloat(document.getElementById('cpRielReceived')?.value) || 0;
  var usdEl     = document.getElementById('cpRielUsdEquiv');
  var changeRow = document.getElementById('cpRielChangeRow');
  var changeEl  = document.getElementById('cpRielChangeKhr');
  if (usdEl) usdEl.textContent = '$' + (khr / CP_KHR_RATE).toFixed(2);
  if (changeRow && changeEl) {
    var orderKhr = Math.round(cpGetCartTotal() * CP_KHR_RATE / 100) * 100;
    var diff     = Math.round(khr) - orderKhr;
    if (khr > 0 && diff < 0) {
      changeEl.textContent = 'Need ៛' + Math.abs(diff).toLocaleString();
      changeEl.className   = 'change-amount not-enough';
      changeRow.style.display = 'flex';
    } else if (khr > 0 && diff >= 0) {
      changeEl.textContent = '៛' + diff.toLocaleString();
      changeEl.className   = 'change-amount';
      changeRow.style.display = 'flex';
    } else {
      changeRow.style.display = 'none';
    }
  }
}

// ── ORDER TYPE ──
function cpSetDrinkType(type) {
  var inp = document.getElementById('cpOrderTypeInput');
  if (inp) inp.value = type;
  var din  = document.getElementById('cpBtnDrinkIn');
  var dout = document.getElementById('cpBtnDrinkOut');
  if (din)  din.classList.toggle('active',  type === 'drink_in');
  if (dout) dout.classList.toggle('active', type === 'drink_out');
  var tg = document.getElementById('cpTableNumberGroup');
  if (tg) tg.style.display = (type === 'drink_in') ? '' : 'none';
  if (type === 'drink_out') {
    var tf = document.getElementById('cpTableNumber');
    if (tf) tf.value = '';
  }
}

// ── CHECKOUT FORM SUBMIT ──
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('cpCheckoutForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var selected = cpGetSelected();
      if (selected.length === 0 && !ADD_TO_ORDER_MODE) { alert('Please select a payment method.'); return; }
      if (ADD_TO_ORDER_MODE) selected = ['paylater'];

      var nameInput = document.getElementById('cpCustomerName');
      var existingName = form.querySelector('input[name="customer_name"]');
      if (!existingName) {
        existingName = document.createElement('input');
        existingName.type = 'hidden'; existingName.name = 'customer_name';
        form.appendChild(existingName);
      }
      existingName.value = nameInput ? nameInput.value : '';

      var tableInput = document.getElementById('cpTableNumber');
      var existingTable = form.querySelector('input[name="table_number"]');
      if (!existingTable) {
        existingTable = document.createElement('input');
        existingTable.type = 'hidden'; existingTable.name = 'table_number';
        form.appendChild(existingTable);
      }
      existingTable.value = (tableInput && document.getElementById('cpOrderTypeInput').value === 'drink_in') ? tableInput.value : '';

      var total = cpGetCartTotal();
      var splits = document.querySelectorAll('.cp-split-amount');
      var selectedAmounts = [];

      if (splits.length > 0 && document.getElementById('cpSplitInputs') && document.getElementById('cpSplitInputs').classList.contains('active')) {
        var sumUsd = 0;
        splits.forEach(function(inp) { sumUsd += cpInputToUsd(inp); });
        if (Math.abs(sumUsd - total) > 0.005) {
          var last = splits[splits.length - 1];
          var diff = total - sumUsd;
          if (last.dataset.currency === 'khr') {
            last.value = Math.max(0, Math.round(((parseFloat(last.value) || 0) + diff * CP_KHR_RATE) / 100) * 100);
          } else {
            last.value = Math.max(0, parseFloat(last.value) + diff).toFixed(2);
          }
        }
        splits.forEach(function(inp) { selectedAmounts.push(parseFloat(inp.value).toFixed(2)); });
      } else {
        selected.forEach(function() { selectedAmounts.push(total.toFixed(2)); });
      }

      var container = document.getElementById('cpPaymentInputs');
      container.innerHTML = '';
      selected.forEach(function(method, i) {
        var usdAmount = selectedAmounts[i] || '0';
        var reference = '';
        if (method === 'riel') {
          var khrInput = selected.length > 1
            ? document.querySelector('.cp-split-amount[data-method="riel"]')
            : document.getElementById('cpRielReceived');
          var khrVal = Math.max(0, parseFloat(khrInput ? khrInput.value : 0) || 0);
          usdAmount = (khrVal / CP_KHR_RATE).toFixed(2);
          reference = Math.round(khrVal).toString();
        }
        if (method === 'cash') {
          var received = parseFloat((document.getElementById('cpCashReceived') || {}).value) || 0;
          if (received > 0) reference = received.toFixed(2);
        }
        var h1 = document.createElement('input'); h1.type='hidden'; h1.name='payment_methods[]'; h1.value=method; container.appendChild(h1);
        var h2 = document.createElement('input'); h2.type='hidden'; h2.name='payment_amounts[]'; h2.value=usdAmount; container.appendChild(h2);
        var h3 = document.createElement('input'); h3.type='hidden'; h3.name='payment_references[]'; h3.value=reference; container.appendChild(h3);
      });
      form.submit();
    });
  }

  var confirmPayBtn = document.getElementById('cpConfirmPayBtn');
  if (confirmPayBtn) {
    confirmPayBtn.addEventListener('click', function() {
      document.getElementById('cpCheckoutForm').requestSubmit();
    });
  }

  var payModal = document.getElementById('cpPayModal');
  if (payModal) {
    payModal.addEventListener('click', function(e) {
      if (e.target === this) cpClosePayModal();
    });
  }
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('cpPayModal').classList.contains('active')) {
      cpClosePayModal();
    }
  });

  document.addEventListener('keydown', function(e) {
    var tag = document.activeElement.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
    if (typeof ADD_TO_ORDER_MODE !== 'undefined' && ADD_TO_ORDER_MODE) {
      if (e.key.toLowerCase() === 'enter') { e.preventDefault(); cpOnConfirmOrderClick(); }
      return;
    }
    var modalOpen = document.getElementById('cpPayModal').classList.contains('active');
    var key = e.key.toLowerCase();
    if (['b','c','p','r'].includes(key)) {
      e.preventDefault();
      if (!modalOpen) cpOpenPayModal();
      var map = { b:'bakong', c:'cash', p:'paylater', r:'riel' };
      cpClickPayMethod(map[key]);
    } else if (key === 'enter') {
      e.preventDefault();
      if (modalOpen) document.getElementById('cpConfirmPayBtn').click();
      else cpOpenPayModal();
    } else if (key === 'escape') {
      if (document.getElementById('cpPayModal').classList.contains('active')) return;
      closeModal();
    }
  });

  var sortSelect = document.getElementById('sortSelect');
  if (sortSelect) sortSelect.addEventListener('change', function() { document.getElementById('searchForm').submit(); });

  var modal = document.getElementById('modal');
  if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });

  document.querySelectorAll('.js-open-product').forEach(function(card) {
    var handler = function() { openModalFromCard(card); };
    card.addEventListener('click', handler);
    card.addEventListener('keydown', function(e) { if (e.key==='Enter'||e.key===' ') { e.preventDefault(); handler(); } });
  });

  var catSections = document.querySelectorAll('.cat-section');
  var catPills    = document.querySelectorAll('.cat-pill[data-target]');
  if (catSections.length && catPills.length) {
    var menuScroll = document.getElementById('menuScroll');
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var id = entry.target.id;
          catPills.forEach(function(pill) { pill.classList.toggle('active', pill.dataset.target === id); });
        }
      });
    }, { threshold: 0.25, root: menuScroll, rootMargin: '-60px 0px -55% 0px' });
    catSections.forEach(function(s) { observer.observe(s); });
  }

  catPills.forEach(function(pill) {
    pill.addEventListener('click', function(e) {
      e.preventDefault();
      var target = document.getElementById(this.dataset.target);
      var scrollEl = document.getElementById('menuScroll');
      if (target && scrollEl) {
        var offset = 60;
        var top = target.offsetTop - offset;
        scrollEl.scrollTop = top;
      }
      catPills.forEach(function(p) { p.classList.remove('active'); });
      pill.classList.add('active');
    });
  });

  var chatInput = document.getElementById('chatInput');
  if (chatInput) chatInput.addEventListener('keypress', function(e) { if (e.key==='Enter') sendChat(); });
});

function cpClickPayMethod(method) {
  var el = document.querySelector('.cp-pay-method input[value="' + method + '"]');
  if (el) el.closest('.cp-pay-method').click();
}

// ── TOAST ──
function showToast(message, type) {
  type = type || 'success';
  var container = document.getElementById('toast-container');
  var toast = document.createElement('div');
  toast.className = 'toast ' + type;
  var icon = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';
  toast.innerHTML = '<i class="fa-solid ' + icon + '"></i><span>' + message + '</span>';
  container.appendChild(toast);
  requestAnimationFrame(function() { toast.classList.add('show'); });
  setTimeout(function() { toast.classList.remove('show'); setTimeout(function() { toast.remove(); }, 350); }, 2800);
}

// ── CHAT ──
function toggleChat() {
  var box = document.getElementById('chatBox');
  var isOpen = box.style.display === 'flex';
  box.style.display = isOpen ? 'none' : 'flex';
  if (!isOpen) document.getElementById('chatInput').focus();
}

function sendChat() {
  var input   = document.getElementById('chatInput');
  var msg     = input.value.trim();
  if (!msg) return;
  var chat    = document.getElementById('chatMessages');
  var sendBtn = document.getElementById('chatSendBtn');
  var userMsg = document.createElement('div');
  userMsg.className = 'msg-user';
  userMsg.innerHTML = '<div class="bubble">' + msg + '</div><div class="avatar"><i class="fa-solid fa-user"></i></div>';
  chat.appendChild(userMsg);
  chat.scrollTop = chat.scrollHeight;
  input.value = ''; sendBtn.disabled = true;
  fetch(ROOT_URL+'/chatbot.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'message='+encodeURIComponent(msg) })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var botMsg = document.createElement('div');
      botMsg.className = 'msg-bot';
      botMsg.innerHTML = '<div class="avatar"><i class="fa-solid fa-robot"></i></div><div class="bubble">' + (data.reply||'Sorry, I did not catch that.') + '</div>';
      chat.appendChild(botMsg);
      chat.scrollTop = chat.scrollHeight;
    })
    .catch(function() {})
    .finally(function() { sendBtn.disabled = false; });
}

// ── LOYALTY MODAL ──
function openLoyaltyModal() {
  document.getElementById('loyaltyModal').style.display = 'flex';
  setTimeout(function() { document.getElementById('loyaltyIdInput').focus(); }, 100);
}
function closeLoyaltyModal() {
  document.getElementById('loyaltyModal').style.display = 'none';
  document.getElementById('loyaltyResult').style.display = 'none';
  document.getElementById('loyaltyError').style.display = 'none';
}

async function lookupLoyalty() {
  var loyaltyId = document.getElementById('loyaltyIdInput').value.trim();
  if (!loyaltyId) { showToast('Please enter a loyalty ID', 'error'); return; }
  try {
    var res  = await fetch(ROOT_URL+'/loyalty_lookup.php?loyalty_id=' + encodeURIComponent(loyaltyId));
    var data = await res.json();
    if (!data.found) {
      document.getElementById('loyaltyResult').style.display = 'none';
      document.getElementById('loyaltyError').style.display = 'block';
      return;
    }
    document.getElementById('loyaltyError').style.display  = 'none';
    document.getElementById('loyaltyResult').style.display = 'block';
    document.getElementById('loyaltyDisplayId').textContent = data.loyalty_id;
    document.getElementById('loyaltyPoints').textContent    = data.points;

    var rewardsHtml = data.rewards.map(function(reward) {
      var can = data.points >= reward.points_required;
      return '<div style="padding:10px;border-radius:7px;border:1px solid ' + (can?'var(--orange, #14b8a6)':'var(--border,#e0d4c4)') + ';text-align:center;background:' + (can?'rgba(20,184,166,.08)':'rgba(0,0,0,.02)') + ';">' +
        '<div style="font-weight:600;color:var(--text,#1a1410);font-size:12px;">' + escH(reward.reward_name) + '</div>' +
        '<div style="font-size:11px;color:var(--text-sec,#5a4a3a);">' + reward.points_required + ' pts</div>' +
        (can ? '<button onclick="redeemReward(\'' + escH(reward.reward_name) + '\',' + reward.points_required + ')" style="margin-top:4px;padding:3px 10px;border-radius:50px;border:none;background:var(--orange, #14b8a6);color:#000;font-weight:600;font-size:10px;cursor:pointer;font-family:\'Poppins\',sans-serif;">Redeem</button>'
             : '<button disabled style="margin-top:4px;padding:3px 10px;border-radius:50px;border:none;background:#ccc;color:#666;font-weight:600;font-size:10px;cursor:not-allowed;font-family:\'Poppins\',sans-serif;">Need ' + reward.points_required + ' pts</button>') +
      '</div>';
    }).join('');
    document.getElementById('loyaltyRewards').innerHTML = rewardsHtml;

    var historyHtml = data.history.map(function(h) {
      var sign = h.points_change > 0 ? '+' : '';
      var color = h.points_change > 0 ? '#55e087' : '#e74c3c';
      return '<div style="display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px solid var(--border,#e0d4c4);">' +
        '<span style="color:var(--text-sec,#5a4a3a);">' + h.type.charAt(0).toUpperCase()+h.type.slice(1) + (h.reward_name?' - '+h.reward_name:'') + '</span>' +
        '<span style="color:'+color+';font-weight:600;">' + sign + h.points_change + '</span></div>';
    }).join('');
    document.getElementById('loyaltyHistory').innerHTML = historyHtml || '<div style="text-align:center;color:var(--text-muted,#9a8070);">No history yet</div>';

    var statusEl = document.getElementById('cpLoyaltyStatus');
    var btnEl    = document.getElementById('cpLoyaltyBtn');
    if (statusEl) { statusEl.className = 'linked'; statusEl.innerHTML = data.loyalty_id + ' &mdash; ' + data.points + ' pts'; }
    if (btnEl)    btnEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> Linked';
  } catch(err) { showToast('Error looking up loyalty card', 'error'); }
}

async function redeemReward(rewardName, pointsRequired) {
  var loyaltyId = document.getElementById('loyaltyDisplayId').textContent;
  if (!confirm('Redeem ' + rewardName + ' for ' + pointsRequired + ' points?')) return;
  try {
    var res  = await fetch(ROOT_URL+'/loyalty_redeem.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'loyalty_id='+encodeURIComponent(loyaltyId)+'&reward_name='+encodeURIComponent(rewardName) });
    var data = await res.json();
    if (data.success) {
      document.getElementById('loyaltyPoints').textContent = data.new_points;
      showToast('✅ ' + data.message, 'success');
    } else { showToast(data.message || 'Error redeeming reward', 'error'); }
  } catch(e) { showToast('Error redeeming reward', 'error'); }
}

// ── Stand number picker ──
function cpToggleStandGrid() {
  var grid = document.getElementById('cpStandGrid');
  if (!grid) return;
  if (grid.style.display !== 'none') { grid.style.display = 'none'; return; }
  grid.innerHTML = '<div style="text-align:center;padding:10px;color:#888;font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>';
  grid.style.display = 'block';
  fetch(ROOT_URL+'/get_stands.php')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var active = data.stands || {};
      var cells = '';
      for (var i = 1; i <= CP_STAND_MAX; i++) {
        var key = String(i);
        var info = active[key];
        if (info) {
          var tip = 'Order #' + info.order_no + (info.customer ? ' (' + info.customer + ')' : '') + ' — ' + info.status;
          cells += '<div title="' + tip.replace(/"/g,'&quot;') + '" style="display:flex;align-items:center;justify-content:center;height:36px;border-radius:7px;font-size:13px;font-weight:600;cursor:not-allowed;background:rgba(231,76,60,.18);color:#ff6b6b;border:1px solid rgba(231,76,60,.35);position:relative;">' + i + '<span style="position:absolute;top:-3px;right:-3px;width:8px;height:8px;border-radius:50%;background:#ef4444;border:1px solid #1a1a1a;"></span></div>';
        } else {
          cells += '<div onclick="cpPickStand(' + i + ')" style="display:flex;align-items:center;justify-content:center;height:36px;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer;background:rgba(62,207,112,.15);color:#3ecf70;border:1px solid rgba(62,207,112,.3);transition:transform .1s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'\'">' + i + '</div>';
        }
      }
      grid.innerHTML =
        '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">' + cells + '</div>' +
        '<div style="margin-top:8px;font-size:10px;color:#666;display:flex;gap:12px;">' +
        '<span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:rgba(62,207,112,.15);border:1px solid rgba(62,207,112,.3);vertical-align:middle;margin-right:3px;"></span>Free</span>' +
        '<span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:rgba(231,76,60,.18);border:1px solid rgba(231,76,60,.35);vertical-align:middle;margin-right:3px;"></span>In use</span>' +
        '</div>';
    })
    .catch(function() {
      grid.innerHTML = '<div style="text-align:center;padding:10px;color:#ef4444;font-size:12px;">Could not load stands</div>';
    });
}

function cpPickStand(num) {
  var inp = document.getElementById('cpTableNumber');
  if (inp) { inp.value = num; cpCheckStand(String(num)); }
  var grid = document.getElementById('cpStandGrid');
  if (grid) grid.style.display = 'none';
}

function cpCheckStand(val) {
  var warn = document.getElementById('cpStandWarn');
  if (!warn) return;
  val = (val || '').trim();
  if (!val) { warn.style.display = 'none'; return; }
  fetch(ROOT_URL+'/check_stand.php?stand=' + encodeURIComponent(val))
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.in_use) {
        document.getElementById('cpStandWarnText').textContent =
          'Stand ' + val + ' is in use by Order #' + data.order_no +
          (data.customer ? ' (' + data.customer + ')' : '') + ' – ' + data.status;
        warn.style.display = 'flex';
      } else {
        warn.style.display = 'none';
      }
    })
    .catch(function() { warn.style.display = 'none'; });
}

</script>
