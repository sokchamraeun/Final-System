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
var product = {}, modalQty = 1, modalUnitPrice = 0, modalAddonPrice = 0;
var ADDONS_LIST = [
  { name: 'No Add On', price: 0 },
  { name: 'Pearl', price: 0.50 },
  { name: 'Coffee Jelly', price: 1.00 },
  { name: 'Tapioca', price: 0.50 },
  { name: 'Jelly', price: 0.50 },
  { name: 'Whipped Cream', price: 0.75 }
];
var selectedAddons = [];

function openModal(id, name, price, img, cat, desc, badge, hasSizes, sizes, iceLevels, sugarLevels, milkLevels) {
  var p = Number(price) || 0;
  product = { id: id, name: name, price: p, cat: cat };
  modalQty = 1; modalUnitPrice = p; modalAddonPrice = 0; selectedAddons = [];
  document.getElementById('modalImg').src = img;
  document.getElementById('modalName').textContent = name;
  document.getElementById('modalQtyDisplay').textContent = '1';
  document.getElementById('pmAddText').textContent = 'Add to Order';
  var badgeEl = document.getElementById('modalBadge');
  if (badge && badge.trim()) {
    badgeEl.textContent = badge.trim();
    badgeEl.style.display = 'inline-flex';
  } else {
    badgeEl.style.display = 'none';
  }

  buildLevelPills('ice', iceLevels, 'Normal Ice');
  buildLevelPills('sugar', sugarLevels, '');
  buildLevelPills('milk', milkLevels, 'Fresh Milk');

  var sizePills = document.getElementById('sizePills');
  sizePills.innerHTML = '';
  var sizeWrap = document.getElementById('optSize');
  if (hasSizes && Array.isArray(sizes) && sizes.length) {
    sizes.forEach(function(s) {
      var b = document.createElement('button');
      b.className = 'pm-btn2';
      b.dataset.group = 'size';
      b.dataset.value = s.code;
      b.dataset.price = s.price;
      b.innerHTML = s.label + ' <span class="pm-price-tag">$' + Number(s.price).toFixed(2) + '</span>';
      b.onclick = function(){ selectSize(b); };
      sizePills.appendChild(b);
    });
    var def = sizePills.querySelector('[data-value="M"]') || sizePills.firstChild;
    if (def) { def.classList.add('active'); modalUnitPrice = Number(def.dataset.price) || p; }
    sizeWrap.style.display = 'block';
  } else {
    sizeWrap.style.display = 'none';
  }

  renderAddons();
  updateModalTotal();
  document.getElementById('modal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

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

function changeQty(delta) {
  modalQty = Math.max(1, Math.min(10, modalQty + delta));
  document.getElementById('modalQtyDisplay').textContent = modalQty;
  document.getElementById('pmAddText').textContent = 'Add to Order (' + modalQty + ' item' + (modalQty > 1 ? 's' : '') + ')';
  updateModalTotal();
}

function updateModalTotal() {
  var unitPrice = modalUnitPrice + modalAddonPrice;
  document.getElementById('modalUnitPrice').textContent = '$' + unitPrice.toFixed(2);
  document.getElementById('modalTotalDisplay').textContent = '$' + (unitPrice * modalQty).toFixed(2);
  document.getElementById('pmAddText').textContent = 'Add to Order (' + modalQty + ' item' + (modalQty > 1 ? 's' : '') + ')';
}

function selectPill(pill) {
  var grid = pill.closest('[class*="pm-grid"]');
  if (grid) grid.querySelectorAll('.pm-btn2').forEach(function(p) { p.classList.remove('active'); });
  pill.classList.add('active');
}

function getPillValue(groupId) {
  var a = document.querySelector('#' + groupId + ' .pm-btn2.active');
  return a ? a.dataset.value : '';
}

function selectSize(pill) {
  var grid = pill.closest('[class*="pm-grid"]');
  if (grid) grid.querySelectorAll('.pm-btn2').forEach(function(p){ p.classList.remove('active'); });
  pill.classList.add('active');
  modalUnitPrice = Number(pill.dataset.price) || modalUnitPrice;
  updateModalTotal();
}

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
    b.className = 'pm-btn2' + (l.name === defaultVal ? ' active' : '');
    b.dataset.group = group;
    b.dataset.value = l.name;
    b.textContent = l.name;
    b.onclick = function(){ selectPill(b); };
    grid.appendChild(b);
  });
}

// ── ADD-ONS ──
function renderAddons() {
  var container = document.getElementById('addonPills');
  if (!container) return;
  container.innerHTML = '';
  selectedAddons = [];
  ADDONS_LIST.forEach(function(a, i) {
    var b = document.createElement('button');
    b.className = 'pm-addon-btn' + (i === 0 ? ' active' : '');
    b.textContent = a.name + (a.price > 0 ? ' +$' + a.price.toFixed(2) : '');
    b.dataset.index = i;
    b.onclick = function(){ toggleAddon(i); };
    container.appendChild(b);
  });
  if (ADDONS_LIST.length > 0) selectedAddons.push(0);
}

function toggleAddon(idx) {
  var btns = document.querySelectorAll('.pm-addon-btn');
  if (idx === 0) {
    btns.forEach(function(b, i) {
      b.classList.toggle('active', i === 0);
      if (i > 0) selectedAddons = selectedAddons.filter(function(s) { return s !== i; });
    });
    selectedAddons = [0];
  } else {
    var noBtn = btns[0];
    if (noBtn) noBtn.classList.remove('active');
    selectedAddons = selectedAddons.filter(function(s) { return s !== 0; });
    var btn = btns[idx];
    if (btn) btn.classList.toggle('active');
    var isActive = btn ? btn.classList.contains('active') : false;
    if (isActive) {
      if (selectedAddons.indexOf(idx) === -1) selectedAddons.push(idx);
    } else {
      selectedAddons = selectedAddons.filter(function(s) { return s !== idx; });
    }
    if (selectedAddons.length === 0) {
      if (noBtn) noBtn.classList.add('active');
      selectedAddons.push(0);
    }
  }
  calcAddonPrice();
}

function calcAddonPrice() {
  modalAddonPrice = 0;
  selectedAddons.forEach(function(idx) {
    modalAddonPrice += ADDONS_LIST[idx].price;
  });
  updateModalTotal();
}

function getSelectedAddons() {
  return selectedAddons.map(function(idx) { return ADDONS_LIST[idx].name; }).filter(function(n) { return n !== 'No Add On'; });
}

// ── ADD TO CART (from modal) ──
function addToCart() {
  var btn = document.getElementById('pmAddBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span id="pmAddText">Adding...</span>';
  var params = new URLSearchParams({ id: product.id, qty: modalQty, csrf_token: CSRF });
  if (document.getElementById('optIce').style.display !== 'none')  params.append('ice', getPillValue('icePills'));
  if (document.getElementById('optMilk').style.display !== 'none')      params.append('milk',      getPillValue('milkPills'));
  if (document.getElementById('optSize').style.display !== 'none')      params.append('size',      getPillValue('sizePills'));
  if (document.getElementById('optSugar').style.display !== 'none')     params.append('sugar',     getPillValue('sugarPills'));
  var addons = getSelectedAddons();
  if (addons.length) params.append('addons', addons.join(','));

  fetch(BASE_URL+'/api/cart/add.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'}, body: params.toString() })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) { showToast(data.message || 'Error', 'error'); return; }
      showToast('Added to cart!', 'success');
      closeModal();
      loadCartPanel();
    })
    .catch(function() { showToast('Error adding to cart', 'error'); })
    .finally(function() { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-cart-plus"></i> <span id="pmAddText">Add to Order</span>'; });
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
  fetch(BASE_URL+'/cart_refresh')
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
    var metaParts = [
      item.size_label ? 'Size: '  + item.size_label : '',
      item.sweetness ? 'Sweet: ' + item.sweetness : '',
      item.ice       ? 'Ice: '   + item.ice       : '',
      item.milk      ? 'Milk: '  + item.milk      : '',
      item.sugar     ? 'Sugar: ' + item.sugar     : '',
      item.addons    ? item.addons                 : '',
    ].filter(Boolean);
    var metaHtml = metaParts.length ? '<div class="cp-item-meta">' + metaParts.map(function(m) { return '<span class="cp-item-tag">' + escH(m) + '</span>'; }).join('') + '</div>' : '';
    var discountPct = parseInt(item.discount_pct) || 0;
    var oldPrice = discountPct > 0 ? item.price / (1 - discountPct / 100) : null;
    itemsHtml += '<div class="cp-item" id="cp-item-' + item.index + '">' +
      '<div class="cp-item-img-wrap">' +
        (discountPct > 0 ? '<span class="cp-item-ribbon">' + discountPct + '%</span>' : '') +
        '<img class="cp-item-img" src="' + (item.image ? ROOT_URL + '/' + escH(item.image) : '') + '" alt="' + escH(item.product_name) + '" loading="lazy">' +
      '</div>' +
      '<div class="cp-item-body">' +
        '<div class="cp-item-top">' +
          '<div class="cp-item-name">' + escH(item.product_name) + '</div>' +
          '<button class="cp-item-remove" onclick="cpRemoveItem(' + item.index + ')" title="Remove"><i class="fa-solid fa-xmark"></i></button>' +
        '</div>' +
        metaHtml +
        '<div class="cp-item-bottom">' +
          '<div class="cp-item-price-row">' +
            (oldPrice !== null ? '<span class="cp-item-price-old">$' + (oldPrice * item.qty).toFixed(2) + '</span>' : '') +
            '<div class="cp-item-price' + (oldPrice !== null ? ' discounted' : '') + '">$<span id="cp-line-' + item.index + '">' + (item.price * item.qty).toFixed(2) + '</span></div>' +
          '</div>' +
          '<div class="cp-qty">' +
            '<button onclick="cpChangeQty(' + item.index + ',-1)">&minus;</button>' +
            '<input type="number" id="cp-qty-' + item.index + '" value="' + item.qty + '" min="1" onchange="cpSetQty(' + item.index + ',this.value)" onfocus="this.select()" onkeydown="if(event.key===\'Enter\'){event.preventDefault();cpSetQty(' + item.index + ',this.value);this.blur();}">' +
            '<button onclick="cpChangeQty(' + item.index + ',1)">+</button>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  });

  itemsHtml += '</div>';

  var promoV = parseFloat(data.promo_discount);
  var manualV = parseFloat(data.manual);
  var hhV     = parseFloat(data.happy_hour);
  itemsHtml += '<div class="cp-summary" id="cpSummary">' +
    '<div class="cp-sum-row"><span>Subtotal</span><span id="cpSubtotal">$' + data.original_subtotal + '</span></div>' +
    '<div class="cp-sum-row discount" id="cpPromoRow" style="' + (promoV > 0 ? '' : 'display:none') + '">' +
      '<span><i class="fa-solid fa-tag"></i> Promo Discount</span><span id="cpPromoAmt">-$' + data.promo_discount + '</span>' +
    '</div>' +
    '<div class="cp-sum-row discount" id="cpHHRow" style="' + (hhV > 0 ? '' : 'display:none') + '">' +
      '<span>&#x1F305; Happy Hour (' + data.happy_hour_pct + '% off)</span><span id="cpHHAmt">-$' + data.happy_hour + '</span>' +
    '</div>' +
    '<div class="cp-sum-row discount" id="cpManualRow" style="' + (manualV > 0 ? '' : 'display:none') + '">' +
      '<span id="cpManualLabel">' + escH(data.manual_label) + '</span><span id="cpManualAmt">-$' + data.manual + '</span>' +
    '</div>' +
    '<div class="cp-sum-divider"></div>' +
    '<div class="cp-sum-total"><span class="lbl">Total</span><span class="amt" id="cpTotal">$' + data.total + '</span></div>';

  if (parseFloat(CP_TAX_RATE) > 0) {
    itemsHtml += '<div class="cp-sum-row" style="padding-top:2px;" id="cpTaxRow"><span>Tax (' + CP_TAX_RATE + '%)</span><span id="cpTax">$' + data.tax + '</span></div>';
  }

  var prevDrinkType    = (document.getElementById('cpOrderTypeInput') || {}).value || 'drink_in';
  var prevCustomerName = (document.getElementById('cpCustomerName')   || {}).value || '';
  var prevTableNumber  = (document.getElementById('cpTableNumber')    || {}).value || '';

  if (!ADD_TO_ORDER_MODE) {
    itemsHtml += '<div class="cp-opt-row" style="margin-top:8px;">' +
      '<button style="border-radius:4px;" type="button" class="cp-opt-btn active" id="cpBtnDrinkIn" onclick="cpSetDrinkType(\'drink_in\')"><i class="fa-solid fa-mug-hot"></i> Drink In</button>' +
      '<button style="border-radius:4px;" type="button" class="cp-opt-btn" id="cpBtnDrinkOut" onclick="cpSetDrinkType(\'drink_out\')"><i class="fa-solid fa-bag-shopping"></i> Take Out</button>' +
      '</div>';
  }

  if (ADD_TO_ORDER_MODE) {
    itemsHtml += '<div class="cp-add-order-note"><i class="fa-solid fa-clock-rotate-left"></i> Adding to Pay Later Order #' + ADD_TO_ORDER_MODE + '<small>Payment was already set. Just add items and confirm.</small></div>';
  }

  itemsHtml += '<div class="cp-opt-row-fields" style="display:flex;gap:8px;margin-top:8px;">' +
    '<div class="cp-opt-field" style="margin:0;flex:1;"><label><i class="fa-regular fa-user"></i> Customer Name</label>' +
    '<input style="border-radius:4px;" type="text" id="cpCustomerName" placeholder="Leave blank for Guest"></div>' +
    '<div class="cp-opt-field" id="cpTableNumberGroup" style="margin:0;flex:1;">' +
    '<label><i class="fa-solid fa-hashtag"></i> Stand <span style="font-weight:400;color:var(--text-muted,#9a8070);">(opt)</span></label>' +
    '<select id="cpTableNumber" name="table_number" onchange="cpCheckStand(this.value)" style="width:100%;padding:7px 10px;border-radius:4px;font-size:13px;outline:none;border:1.5px solid var(--border,#e0d4c4);background:var(--bg,#f4efe9);color:var(--text,#1a1410);font-family:\'Poppins\',sans-serif;cursor:pointer;">' +
    '<option value="">Select stand</option>';
  for (var si = 1; si <= CP_STAND_MAX; si++) {
    itemsHtml += '<option value="' + si + '">Stand #' + si + '</option>';
  }
  itemsHtml += '</select>' +
    '<div id="cpStandWarn" class="cp-stand-warn"><i class="fa-solid fa-triangle-exclamation"></i> <span id="cpStandWarnText"></span></div>' +
    '</div></div>';

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
  fetch(BASE_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_update=1&index='+index+'&qty='+qty })
    .then(function(r) { return r.json(); })
    .then(function() { loadCartPanel(); });
}

function cpSetQty(index, val) {
  var qty = Math.max(1, parseInt(val) || 1);
  var inp = document.getElementById('cp-qty-' + index);
  if (inp) inp.value = qty;
  fetch(BASE_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_update=1&index='+index+'&qty='+qty })
    .then(function(r) { return r.json(); })
    .then(function() { loadCartPanel(); });
}

function cpRemoveItem(index) {
  var row = document.getElementById('cp-item-' + index);
  if (row) { row.style.opacity='0'; row.style.transform='translateX(20px)'; row.style.transition='all .25s'; }
  setTimeout(function() {
    fetch(BASE_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_remove=1&index='+index })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.remaining === 0) { loadCartPanel(); return; }
        loadCartPanel();
      });
  }, 250);
}

function cpClearCart() {
  if (!confirm('Remove all items from the cart?')) return;
  fetch(BASE_URL+'/cart.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'ajax_clear=1' })
    .then(function() { loadCartPanel(); });
}

// ── REFRESH SUMMARY FROM AJAX RESPONSE (qty update) ──
function cpRefreshSummaryFromData(data) {
  var set = function(id, val) { var el = document.getElementById(id); if (el) el.textContent = val; };
  set('cpSubtotal', '$' + data.cartSubtotal);
  set('cpTax',      '$' + data.tax);
  var txr = document.getElementById('cpTaxRow');
  if (txr) txr.style.display = parseFloat(data.tax) > 0 ? '' : 'none';

  var newTotal = '$' + data.cartTotal;
  set('cpTotal', newTotal);
  var st = document.getElementById('cpSubtotal');

  var pr = document.getElementById('cpPromoRow');
  if (pr) pr.style.display = parseFloat(data.promo_discount) > 0 ? '' : 'none';
  set('cpPromoAmt', '-$' + (data.promo_discount || '0.00'));

  var hhr = document.getElementById('cpHHRow');
  if (hhr) hhr.style.display = parseFloat(data.happy_hour_discount) > 0 ? '' : 'none';
  set('cpHHAmt', '-$' + (data.happy_hour_discount || '0.00'));

  var mdr = document.getElementById('cpManualRow');
  if (mdr) mdr.style.display = parseFloat(data.manual_discount) > 0 ? '' : 'none';
  set('cpManualAmt', '-$' + (data.manual_discount || '0.00'));

  cpCalcChange();
  cpUpdateSplitAmounts();
}



// ── PAYMENT METHODS ──
function cpTogglePayment(label) {
  var cb = label.querySelector('input[type="checkbox"]');
  var value = cb.value;
  if (value === 'paylater' || value === 'riel') {
    document.querySelectorAll('.cp-pay-method input[type="checkbox"]').forEach(function(c) { c.checked = false; });
    cb.checked = true;
  } else {
    var pl = document.querySelector('.cp-pay-method input[value="paylater"]');
    if (pl && pl.checked) pl.checked = false;
    var rielCb = document.querySelector('.cp-pay-method input[value="riel"]');
    if (rielCb && rielCb.checked) rielCb.checked = false;
    cb.checked = !cb.checked;
  }
  if (value === 'riel' && !cb.checked) {
    var ri = document.getElementById('cpRielReceived');
    if (ri) ri.value = '';
  }
  document.querySelectorAll('.cp-pay-method').forEach(function(el) {
    el.classList.toggle('selected', el.querySelector('input[type="checkbox"]').checked);
  });
  var selected = cpGetSelected();
  cpUpdateConfirmBtn(selected);
  cpUpdateSplitInputs();
}

function cpGetSelected() {
  var sel = [];
  document.querySelectorAll('.cp-pay-method input[type="checkbox"]:checked').forEach(function(cb) { sel.push(cb.value); });
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
  var pmtr = document.getElementById('cpPmTaxRow');
  if (pmtr) pmtr.style.display = tax > 0.001 ? '' : 'none';
  var pmt = document.getElementById('cpPmTax');
  if (pmt) pmt.textContent = '$' + tax.toFixed(2);
  document.getElementById('cpPmTotal').textContent = '$' + total.toFixed(2);
  var khr = Math.round(total * CP_KHR_RATE / 100) * 100;
  var khrEl = document.getElementById('cpPmKhr');
  if (khrEl) khrEl.textContent = '៛ ' + khr.toLocaleString();

  var itemsHtml = '';
  document.querySelectorAll('#cpItems .cp-item').forEach(function(item) {
    var name = item.querySelector('.cp-item-name').textContent;
    var qtyInput = item.querySelector('input[id^="cp-qty-"]');
    var qty = qtyInput ? qtyInput.value : '1';
    var priceSpan = item.querySelector('[id^="cp-line-"]');
    var price = priceSpan ? priceSpan.textContent : '0.00';
    var meta = item.querySelector('.cp-item-meta');
    var metaText = meta ? ' <span class="cp-pm-item-meta">' + escH(meta.textContent.replace(/\s+/g, ' ').trim()) + '</span>' : '';
    itemsHtml += '<div class="cp-pm-item">' +
      '<span class="cp-pm-item-name">' + escH(name) + metaText + '</span>' +
      '<span class="cp-pm-item-right"><span class="cp-pm-item-qty">x' + qty + '</span><span class="cp-pm-item-price">$' + price + '</span></span>' +
    '</div>';
  });
  var itemsContainer = document.getElementById('cpPmItems');
  if (itemsContainer) itemsContainer.innerHTML = itemsHtml;

  document.getElementById('cpPayModal').classList.add('active');
}
function cpClosePayModal() {
  document.getElementById('cpPayModal').classList.remove('active');
  document.querySelectorAll('.cp-pay-method input[type="checkbox"]').forEach(function(c) {
    c.checked = false;
    c.closest('.cp-pay-method').classList.remove('selected');
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
      // AJAX submit via XMLHttpRequest (avoids multipart/form-data issues with some servers)
      var subBtn = document.getElementById('cpConfirmPayBtn');
      if (subBtn) subBtn.disabled = true;
      var payForm = form;
      var ajaxUrl = payForm.action + (payForm.action.indexOf('?') > -1 ? '&' : '?') + 'ajax=1';
      var xhr = new XMLHttpRequest();
      xhr.open('POST', ajaxUrl, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      // Collect form data as URL-encoded string
      var formData = new FormData(payForm);
      var params = [];
      formData.forEach(function(v, k) {
        if (typeof v === 'string') {
          params.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
        }
      });
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (subBtn) subBtn.disabled = false;
        var ct = xhr.getResponseHeader('Content-Type') || '';
        if (ct.indexOf('application/json') !== -1) {
          try {
            var data = JSON.parse(xhr.responseText);
            if (data.success) {
              cpClosePayModal();
              cpShowReceipt(data);
            } else {
              alert(data.error || 'Order failed');
            }
          } catch(e) {
            alert('Error: Invalid JSON response');
          }
        } else {
          alert('Error: Expected JSON, got ' + xhr.status + ' ' + xhr.statusText + '. First 200 chars:\n' + xhr.responseText.substring(0, 200));
        }
      };
      xhr.onerror = function() {
        if (subBtn) subBtn.disabled = false;
        alert('Error: Network request failed');
      };
      xhr.send(params.join('&'));
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
    if (modalOpen && ['b','c','p','r'].includes(key)) {
      e.preventDefault();
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
    }, { threshold: 0.25, root: menuScroll, rootMargin: '-90px 0px -55% 0px' });
    catSections.forEach(function(s) { observer.observe(s); });
  }

  catPills.forEach(function(pill) {
    pill.addEventListener('click', function(e) {
      e.preventDefault();
      var target = document.getElementById(this.dataset.target);
      var scrollEl = document.getElementById('menuScroll');
      if (target && scrollEl) {
        var offset = 90;
        var top = target.offsetTop - offset;
        scrollEl.scrollTop = top;
      }
      catPills.forEach(function(p) { p.classList.remove('active'); });
      pill.classList.add('active');
    });
  });

  var chatInput = document.getElementById('chatInput');
  if (chatInput) chatInput.addEventListener('keypress', function(e) { if (e.key==='Enter') sendChat(); });

  // ── AUTO-SEARCH (client-side filter) ──
  var searchInput = document.getElementById('searchInput');
  var searchForm = document.getElementById('searchForm');
  if (searchInput && searchForm) {
    searchForm.addEventListener('submit', function(e) { e.preventDefault(); });
    searchInput.addEventListener('input', function() {
      var q = this.value.trim().toLowerCase();
      document.querySelectorAll('.product-card[data-product-name]').forEach(function(c) {
        var name = (c.getAttribute('data-product-name') || '').toLowerCase();
        c.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
      });
    });
  }

  // ── GRID/LIST VIEW TOGGLE ──
  var cpView = localStorage.getItem('cpMenuView') || 'grid';
  function cpApplyView(v) {
    document.querySelectorAll('.product-grid').forEach(function(g) { g.classList.toggle('list-view', v === 'list'); });
    var icon = document.getElementById('cpViewIcon');
    if (icon) icon.className = v === 'list' ? 'fa-solid fa-grip' : 'fa-solid fa-list';
  }
  cpApplyView(cpView);
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
  fetch(BASE_URL+'/chatbot', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'message='+encodeURIComponent(msg) })
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
    var res  = await fetch(BASE_URL+'/loyalty_lookup?loyalty_id=' + encodeURIComponent(loyaltyId));
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
    var res  = await fetch(BASE_URL+'/loyalty_redeem', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'loyalty_id='+encodeURIComponent(loyaltyId)+'&reward_name='+encodeURIComponent(rewardName) });
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
  fetch(BASE_URL+'/get_stands')
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
  fetch(BASE_URL+'/check_stand?stand=' + encodeURIComponent(val))
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

// ── RECEIPT MODAL ──
var cpLastReceiptData = null;
function cpShowReceipt(data) {
  var o = data.order || data;
  cpLastReceiptData = o;
  if (o.csrf_token) { CSRF = o.csrf_token; var ctf = document.querySelector('#cpCheckoutForm input[name="csrf_token"]'); if (ctf) ctf.value = CSRF; }
  document.getElementById('cpRcptNo').textContent = '#' + o.daily_no;
  var statusEl = document.getElementById('cpRcptStatus');
  statusEl.textContent = o.status;
  statusEl.className = 'cp-rf-value cp-rf-badge';
  if (o.status === 'PendingPayment') statusEl.style.background = '#fef3c7'; else statusEl.style.background = '#dbeafe';
  document.getElementById('cpRcptCustomer').textContent = o.customer_name || 'Guest';
  document.getElementById('cpRcptCashier').textContent = o.employee_name || '-';
  document.getElementById('cpRcptType').textContent = o.order_type === 'drink_out' ? 'Drink Out' : 'Drink In';
  var tableRow = document.getElementById('cpRcptTableRow');
  if (o.table_number) {
    tableRow.style.display = '';
    document.getElementById('cpRcptTable').textContent = o.table_number;
  } else {
    tableRow.style.display = 'none';
  }

  // Items
  var itemsEl = document.getElementById('cpRcptItems');
  itemsEl.innerHTML = '';
  (o.items || []).forEach(function(item) {
    var div = document.createElement('div');
    div.className = 'cp-receipt-item';
    var opts = [];
    if (item.sweetness) opts.push('Sweetness: ' + item.sweetness);
    if (item.ice) opts.push('Ice: ' + item.ice);
    if (item.sugar) opts.push('Sugar: ' + item.sugar);
    if (item.milk) opts.push('Milk: ' + item.milk);
    if (item.size_label) opts.push(item.size_label);
    div.innerHTML =
      '<div class="cp-receipt-item-left">' +
        '<div class="cp-receipt-item-name">' + escH(item.product_name) + '</div>' +
        (opts.length ? '<div class="cp-receipt-item-opts">' + escH(opts.join(' | ')) + '</div>' : '') +
      '</div>' +
      '<div class="cp-receipt-item-right">' +
        '<span class="cp-receipt-item-qty">x' + item.quantity + '</span>' +
        '<span class="cp-receipt-item-price">$' + parseFloat(item.price).toFixed(2) + '</span>' +
      '</div>';
    itemsEl.appendChild(div);
  });

  // Totals
  var sub = (o.subtotal !== undefined) ? parseFloat(o.subtotal) : 0;
  if (!sub) (o.items || []).forEach(function(item) { sub += parseFloat(item.price) * parseInt(item.quantity); });
  var promo = parseFloat(o.promotion_discount) || 0;
  var manual = parseFloat(o.manual_discount) || 0;
  var tax = parseFloat(o.tax_rate) || 0;
  var total = parseFloat(o.total) || 0;
  var afterDisc = sub - promo - manual;
  if (afterDisc < 0) afterDisc = 0;
  var taxAmt = tax > 0 ? afterDisc * (tax / 100) : 0;
  var totalsEl = document.getElementById('cpRcptTotals');
  var tHtml = '';
  tHtml += '<div class="cp-receipt-total-row"><span>Subtotal</span><span>$' + sub.toFixed(2) + '</span></div>';
  if (promo > 0) tHtml += '<div class="cp-receipt-total-row discount"><span>Promo Discount</span><span>-$' + promo.toFixed(2) + '</span></div>';
  if (manual > 0) {
    var lbl = o.manual_discount_reason || 'Cashier Discount';
    tHtml += '<div class="cp-receipt-total-row discount"><span>' + escH(lbl) + '</span><span>-$' + manual.toFixed(2) + '</span></div>';
  }
  if (tax > 0) tHtml += '<div class="cp-receipt-total-row tax"><span>Tax (' + tax + '%)</span><span>$' + taxAmt.toFixed(2) + '</span></div>';
  tHtml += '<div class="cp-receipt-total-row total"><span>Total</span><span>$' + total.toFixed(2) + '</span></div>';
  totalsEl.innerHTML = tHtml;

  // Payments
  var paysEl = document.getElementById('cpRcptPayments');
  paysEl.innerHTML = '';
  var hasBakong = false;
  (o.payments || []).forEach(function(p) {
    var icon = '';
    if (p.method === 'bakong') { icon = '<i class="fa-solid fa-qrcode" style="margin-right:3px;"></i>'; hasBakong = true; }
    else if (p.method === 'cash') icon = '<i class="fa-solid fa-money-bill-wave" style="margin-right:3px;"></i>';
    else if (p.method === 'paylater') icon = '<i class="fa-solid fa-clock" style="margin-right:3px;"></i>';
    else if (p.method === 'riel') icon = '<i class="fa-solid fa-coins" style="margin-right:3px;"></i>';
    var d = document.createElement('div');
    d.className = 'cp-receipt-pay-row';
    d.innerHTML = '<span>' + icon + p.method.charAt(0).toUpperCase() + p.method.slice(1) + '</span><span>$' + parseFloat(p.amount).toFixed(2) + '</span>';
    paysEl.appendChild(d);
  });
  // Bakong: show QR code inline + start payment polling
  var cpBakongInterval = null;
  if (hasBakong) {
    document.getElementById('cpBtnCancelOrder').style.display = '';
    // show fallback hint after 20s
    if (o.qr_url) {
      var qrWrap = document.createElement('div');
      qrWrap.id = 'cpBakongQrWrap';
      qrWrap.className = 'cp-receipt-qr-wrap';
      qrWrap.innerHTML = '<img src="' + o.qr_url + '" alt="Bakong QR" style="width:200px;height:200px;display:block;margin:8px auto;border-radius:8px;">'
        + '<div style="text-align:center;font-size:10px;color:var(--text-muted,#9a8070);">Scan with Bakong app to pay</div>'
        + '<div id="cpBakongStatus" style="text-align:center;font-size:11px;margin-top:4px;color:var(--text-muted,#9a8070);">Waiting for payment...</div>';
      paysEl.appendChild(qrWrap);
      // start polling
      cpBakongInterval = setInterval(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', BASE_URL + '/check_payment.php?order_id=' + o.order_id, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function() {
          try {
            var d = JSON.parse(xhr.responseText);
            if (d.paid && !qrWrap._paid) {
              qrWrap._paid = true;
              clearInterval(cpBakongInterval);
              cpBakongInterval = null;
              document.getElementById('cpReceiptModal').dataset.bakongInterval = '';
              var card = document.querySelector('.cp-receipt-card');
              if (card) { card.classList.add('cp-receipt-paid'); setTimeout(function(){ card.classList.remove('cp-receipt-paid'); }, 800); }
              var st = document.getElementById('cpBakongStatus');
              if (st) st.textContent = '';
              var img = qrWrap.querySelector('img');
              if (img) img.style.opacity = '0.3';
              var chk = document.createElement('div');
              chk.className = 'cp-paid-badge';
              chk.innerHTML = '<i class="fa-solid fa-circle-check cp-paid-icon"></i> PAID';
              qrWrap.appendChild(chk);
              var statusEl = document.getElementById('cpRcptStatus');
              if (statusEl) { statusEl.textContent = 'Completed'; statusEl.style.background = '#22c55e'; statusEl.style.color = '#fff'; }
            } else if (d.error) {
              clearInterval(cpBakongInterval);
              cpBakongInterval = null;
              document.getElementById('cpReceiptModal').dataset.bakongInterval = '';
              var st = document.getElementById('cpBakongStatus');
              if (st) st.textContent = d.message || 'Verification unavailable';
            }
          } catch(e) {}
        };
        xhr.onerror = function() {
          clearInterval(cpBakongInterval);
          cpBakongInterval = null;
          document.getElementById('cpReceiptModal').dataset.bakongInterval = '';
        };
        xhr.send();
      }, 2000);
    } else {
      var qrLink = document.createElement('a');
      qrLink.href = BASE_URL + '/payment.php?order_id=' + o.order_id;
      qrLink.target = '_blank';
      qrLink.className = 'cp-receipt-qr-btn';
      qrLink.innerHTML = '<i class="fa-solid fa-qrcode"></i> Open Payment Page';
      paysEl.appendChild(qrLink);
    }
  }

  document.getElementById('cpReceiptModal').classList.add('active');
  // store interval for cleanup
  document.getElementById('cpReceiptModal').dataset.bakongInterval = cpBakongInterval || '';

  if (!hasBakong) document.getElementById('cpBtnCancelOrder').style.display = 'none';
  // Update button for add-to-order
  var newBtn = document.querySelector('.cp-receipt-btn-new');
  if (data.added_to_order) {
    newBtn.innerHTML = '<i class="fa-solid fa-arrow-left"></i> Back to Menu';
  } else {
    newBtn.innerHTML = '<i class="fa-solid fa-plus"></i> New Order';
  }
}

function cpCloseReceipt() {
  document.getElementById('cpReceiptModal').classList.remove('active');
  var iv = document.getElementById('cpReceiptModal').dataset.bakongInterval;
  if (iv) { clearInterval(parseInt(iv)); document.getElementById('cpReceiptModal').dataset.bakongInterval = ''; }
}

function cpNewOrder() {
  cpCloseReceipt();
  var newBtn = document.querySelector('.cp-receipt-btn-new');
  if (newBtn && newBtn.textContent.indexOf('Back') !== -1) {
    window.location.href = BASE_URL + '/menu';
  } else {
    location.reload();
  }
}

function cpCancelAndContinue() {
  var o = cpLastReceiptData;
  if (!o || !o.order_id) return;

  var btn = document.getElementById('cpBtnCancelOrder');
  if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cancelling...'; }

  var xhr = new XMLHttpRequest();
  xhr.open('POST', BASE_URL + '/cancel_order.php?order_id=' + o.order_id, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.onload = function() {
    try {
      var r = JSON.parse(xhr.responseText);
      if (r.ok) {
        // restore cart backup
        var x2 = new XMLHttpRequest();
        x2.open('GET', BASE_URL + '/restore_cart.php', true);
        x2.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        x2.onload = function() {
          cpCloseReceipt();
          if (window.cpRefreshCart) cpRefreshCart();
          else location.reload();
        };
        x2.send();
      } else {
        alert(r.error || 'Failed to cancel order');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ban"></i> Cancel & Continue'; }
      }
    } catch(e) {
      alert('Failed to cancel order');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ban"></i> Cancel & Continue'; }
    }
  };
  xhr.onerror = function() {
    alert('Network error');
    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-ban"></i> Cancel & Continue'; }
  };
  xhr.send('cancel_reason=Payment+cancelled+by+customer&restore_stock=1');
}

function cpPrintReceipt() {
  var body = document.getElementById('cpReceiptBody');
  if (!body) return;
  var clone = body.cloneNode(true);
  clone.querySelectorAll('.cp-paid-badge').forEach(function(el) { el.remove(); });
  var printWin = window.open('', '_blank', 'width=380,height=600');
  if (!printWin) return;
  printWin.document.write(
    '<!DOCTYPE html><html><head><meta charset="utf-8">'
    + '<title>Receipt #' + (document.getElementById('cpRcptNo').textContent || '') + '</title>'
    + '<style>'
    + 'body{margin:0;padding:16px;font-family:"Courier New",monospace;font-size:12px;color:#000;background:#fff;}'
    + '.cp-receipt-shop{text-align:center;margin-bottom:8px;}'
    + '.cp-receipt-name{font-size:16px;font-weight:700;}'
    + '.cp-receipt-info{font-size:11px;color:#555;}'
    + '.cp-receipt-divider{border-top:1px dashed #999;margin:8px 0;}'
    + '.cp-receipt-header{display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;}'
    + '.cp-receipt-field{display:flex;justify-content:space-between;font-size:11px;}'
    + '.cp-rf-label{color:#666;}'
    + '.cp-rf-value{font-weight:600;}'
    + '.cp-rf-badge{background:#dbeafe;padding:0 6px;border-radius:3px;}'
    + '.cp-receipt-item{display:flex;justify-content:space-between;padding:3px 0;font-size:11px;}'
    + '.cp-receipt-item-name{font-weight:600;}'
    + '.cp-receipt-item-opts{font-size:10px;color:#666;}'
    + '.cp-receipt-item-right{text-align:right;white-space:nowrap;}'
    + '.cp-receipt-item-qty{margin-right:6px;color:#666;}'
    + '.cp-receipt-total-row{display:flex;justify-content:space-between;padding:2px 0;font-size:11px;}'
    + '.cp-receipt-total-row.discount{color:#e53935;}'
    + '.cp-receipt-total-row.total{font-size:14px;font-weight:700;border-top:2px solid #000;padding-top:4px;margin-top:2px;}'
    + '.cp-receipt-pay-row{display:flex;justify-content:space-between;padding:2px 0;font-size:11px;}'
    + '.cp-receipt-qr-wrap{text-align:center;margin-top:8px;}'
    + '.cp-receipt-qr-wrap img{width:160px;height:160px;}'
    + '@media print{body{margin:0;padding:12px;}}'
    + '</style></head><body>'
    + clone.innerHTML
    + '<div style="text-align:center;margin-top:12px;font-size:10px;color:#999;">Thank you for your purchase!</div>'
    + '</body></html>'
  );
  printWin.document.close();
  printWin.onload = function() { printWin.print(); };
}

function cpToggleView() {
  var grids = document.querySelectorAll('.product-grid');
  var current = localStorage.getItem('cpMenuView') || 'grid';
  var next = current === 'grid' ? 'list' : 'grid';
  grids.forEach(function(g) { g.classList.toggle('list-view', next === 'list'); });
  var icon = document.getElementById('cpViewIcon');
  if (icon) icon.className = next === 'list' ? 'fa-solid fa-grip' : 'fa-solid fa-list';
  localStorage.setItem('cpMenuView', next);
}

</script>
