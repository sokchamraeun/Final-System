<div id="modal" class="pm-overlay">
  <div class="pm-card">

    <div class="pm-header">
      <img id="modalImg" class="pm-thumb" src="" alt="">
      <div class="pm-header-info">
        <div class="pm-name-wrap">
          <div class="pm-name" id="modalName"></div>
          <div class="pm-badge" id="modalBadge"></div>
        </div>
        <div class="pm-subtitle">Customize your drink</div>
      </div>
      <button class="pm-close2" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="pm-body2" id="pmBody2">

      <div id="optSize" class="pm-section2" style="display:none">
        <div class="pm-label">Select Size</div>
        <div class="pm-grid3" id="sizePills"></div>
      </div>

      <div class="pm-two-col">
        <div id="optIce" class="pm-section2" style="display:none">
          <div class="pm-label">Ice Level</div>
          <div class="pm-grid2" id="icePills"></div>
        </div>
        <div id="optSugar" class="pm-section2" style="display:none">
          <div class="pm-label">Sugar Level</div>
          <div class="pm-grid2" id="sugarPills"></div>
        </div>
      </div>

      <div id="optMilk" class="pm-section2" style="display:none">
        <div class="pm-label">Milk</div>
        <div class="pm-grid2" id="milkPills"></div>
      </div>

      <div id="optAddons" style="display:none">
        <hr class="pm-divider">
        <div class="pm-section2">
          <div class="pm-label">Add-ons</div>
          <div class="pm-addons" id="addonPills"></div>
        </div>
      </div>

    </div>

    <div class="pm-footer2">
      <div class="pm-price-summary">
        <div class="pm-price-row">
          <span>Unit Price</span>
          <span id="modalUnitPrice">$0.00</span>
        </div>
        <div class="pm-price-row pm-price-total">
          <span>Total Amount</span>
          <span id="modalTotalDisplay">$0.00</span>
        </div>
      </div>
      <button class="pm-add2" id="pmAddBtn" onclick="addToCart()">
        <i class="fa-solid fa-cart-plus"></i> <span id="pmAddText">Add to Order</span>
      </button>
      <div class="pm-qty-row">
        <button onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
        <span id="modalQtyDisplay">1</span>
        <button onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
      </div>
    </div>

  </div>
</div>
