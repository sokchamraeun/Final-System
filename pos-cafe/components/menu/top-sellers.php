<?php
$top_sellers = $top_sellers ?? [];
$bestSellerName = $bestSellerName ?? null;
$sizesByProduct = $sizesByProduct ?? [];
$iceByProduct   = $iceByProduct   ?? [];
$sugarByProduct = $sugarByProduct ?? [];
$milkByProduct  = $milkByProduct  ?? [];
?>
<?php if (!empty($top_sellers)): ?>
<section class="top-sellers">
  <div class="section-header">
    <h2><i class="fa-solid fa-fire" style="color:#e74c3c;"></i> Top Sellers</h2>
  </div>
  <div class="product-grid">
    <?php foreach ($top_sellers as $idx => $t): ?>
    <div class="product-card js-open-product"
         data-product-id="<?= (int)$t['product_id'] ?>"
         data-product-name="<?= e($t['name']) ?>"
         data-product-price="<?= e($t['price']) ?>"
         data-product-image="<?= e(root_url($t['image'])) ?>"
         data-product-category="<?= e($t['category']) ?>"
         data-product-desc="<?= e($t['description']) ?>"
         data-product-badge="<?= e($t['badge_text'] ?? '') ?>"
         data-product-has-sizes="<?= (int)($t['has_sizes'] ?? 0) ?>"
     data-product-sizes='<?= htmlspecialchars(json_encode($sizesByProduct[(int)$t['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-ice-levels='<?= htmlspecialchars(json_encode($iceByProduct[(int)$t['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-sugar-levels='<?= htmlspecialchars(json_encode($sugarByProduct[(int)$t['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-milk-levels='<?= htmlspecialchars(json_encode($milkByProduct[(int)$t['product_id']] ?? []), ENT_QUOTES) ?>'
     data-is-bestseller="<?= $t['name']===$bestSellerName?'1':'0' ?>"
         role="button" tabindex="0">
      <div class="card-img">
        <?php if (!empty($t['badge_text'])): ?>
        <span class="product-badge"><?= e($t['badge_text']) ?></span>
        <?php endif; ?>
        <img src="<?= e(root_url($t['image'])) ?>" loading="lazy" alt="<?= e($t['name']) ?>">
      </div>
      <div class="card-info">
        <div class="card-name"><?= e($t['name']) ?></div>
        <div class="seller-rank"><?= $idx===0 ? '&#x1F3C6; #1 Seller' : '&#x1F525; Top Pick' ?></div>
        <hr class="card-divider">
        <div class="card-bottom">
          <div class="card-price">$<?= number_format($t['price'], 2) ?></div>
          <?php if ((int)($t['has_sizes'] ?? 0) === 1): ?>
          <button class="btn-add-circle" onclick="event.stopPropagation(); openModalFromCard(this.closest('.product-card'));">+</button>
          <?php else: ?>
          <button class="btn-add-circle" onclick="event.stopPropagation(); quickAdd(<?= (int)$t['product_id'] ?>, <?= (float)$t['price'] ?>);">+</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
