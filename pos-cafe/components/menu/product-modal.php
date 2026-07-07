<div id="modal" class="modal pm-overlay">
  <div class="pm-card">
    <button class="pm-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>

    <div class="pm-top-row">
      <div class="pm-img-wrap">
        <img id="modalImg" class="pm-img" src="" alt="">
      </div>
      <div class="pm-head-row">
        <h2 class="pm-title" id="modalName"></h2>
        <div class="pm-price-pill" id="modalPrice">$0.00</div>
      </div>
    </div>

    <div class="pm-body">
      <p class="pm-desc" id="modalDesc"></p>

      <div id="optSize" class="pm-section" style="display:none">
        <div class="pm-section-title"><span class="pm-bullet"></span> Size</div>
        <div class="pm-grid" id="sizePills"></div>
      </div>

      <div id="optIce" class="pm-section" style="display:none">
        <div class="pm-section-title"><span class="pm-bullet"></span> Ice</div>
        <div class="pm-grid" id="icePills"></div>
      </div>

      <div id="optSugar" class="pm-section" style="display:none">
        <div class="pm-section-title"><span class="pm-bullet"></span> Sugar</div>
        <div class="pm-grid" id="sugarPills"></div>
      </div>

      <div id="optMilk" class="pm-section" style="display:none">
        <div class="pm-section-title"><span class="pm-bullet"></span> Milk</div>
        <div class="pm-grid" id="milkPills"></div>
      </div>

      <div class="pm-qty-box">
        <div class="pm-qty-inner">
          <span class="pm-qty-btn" onclick="changeQty(-1)">&minus;</span>
          <span class="pm-qty-val" id="modalQtyDisplay">1</span>
          <span class="pm-qty-btn" onclick="changeQty(1)">+</span>
        </div>
      </div>

      <div style="height:20px"></div>
    </div>

    <div class="pm-bottom">
      <div class="pm-bottom-left">
        <div class="pm-total-lbl">Total</div>
        <div class="pm-total-val" id="modalTotalDisplay">$0.00</div>
      </div>
      <button class="pm-add" onclick="addToCart()">
        <i class="fa-solid fa-cart-plus"></i> Add to Cart
      </button>
    </div>
  </div>
</div>
