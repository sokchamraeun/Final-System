<?php
$p = $p ?? [];
$bestSellerName = $bestSellerName ?? null;
$sizesByProduct = $sizesByProduct ?? [];
$iceByProduct   = $iceByProduct   ?? [];
$sugarByProduct = $sugarByProduct ?? [];
$milkByProduct  = $milkByProduct  ?? [];
?>
<?php if ($p['low_count'] > 0): ?>
<div class="product-card disabled">
  <div class="card-img"><img src="<?= e(root_url($p['image'])) ?>" loading="lazy" alt="<?= e($p['name']) ?>"><div class="out-of-stock"><span>Out of Stock</span></div></div>
  <div class="card-info"><div class="card-name"><?= e($p['name']) ?></div><div class="card-price" style="color:#dc3545">Unavailable</div></div>
</div>
<?php else: ?>
<div class="product-card js-open-product"
     data-product-id="<?= (int)$p['product_id'] ?>"
     data-product-name="<?= e($p['name']) ?>"
     data-product-price="<?= e($p['price']) ?>"
     data-product-image="<?= e(root_url($p['image'])) ?>"
     data-product-category="<?= e($p['category']) ?>"
     data-product-desc="<?= e($p['description']) ?>"
     data-product-badge="<?= e($p['badge_text'] ?? '') ?>"
     data-product-has-sizes="<?= (int)($p['has_sizes'] ?? 0) ?>"
     data-product-sizes='<?= htmlspecialchars(json_encode($sizesByProduct[(int)$p['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-ice-levels='<?= htmlspecialchars(json_encode($iceByProduct[(int)$p['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-sugar-levels='<?= htmlspecialchars(json_encode($sugarByProduct[(int)$p['product_id']] ?? []), ENT_QUOTES) ?>'
     data-product-milk-levels='<?= htmlspecialchars(json_encode($milkByProduct[(int)$p['product_id']] ?? []), ENT_QUOTES) ?>'
     data-is-bestseller="<?= $p['name']===$bestSellerName?'1':'0' ?>"
     role="button" tabindex="0">
  <div class="card-img">
    <?php if (!empty($p['badge_text'])): ?><span class="product-badge"><?= e($p['badge_text']) ?></span><?php endif; ?>
    <?php if ($p['name']===$bestSellerName): ?><span class="badge-bestseller">&#x2605; Best Seller</span><?php endif; ?>
    <img src="<?= e(root_url($p['image'])) ?>" loading="lazy" alt="<?= e($p['name']) ?>">
  </div>
  <div class="card-info">
    <div class="card-name"><?= e($p['name']) ?></div>
    <div class="card-desc"><?= e($p['description']) ?></div>
    <hr class="card-divider">
    <div class="card-bottom">
      <div class="card-price">$<?= number_format($p['price'], 2) ?></div>
      <?php if ((int)($p['has_sizes'] ?? 0) === 1): ?>
      <button class="btn-add-circle" onclick="event.stopPropagation(); openModalFromCard(this.closest('.product-card'));">+</button>
      <?php else: ?>
      <button class="btn-add-circle" onclick="event.stopPropagation(); quickAdd(<?= (int)$p['product_id'] ?>, <?= (float)$p['price'] ?>);">+</button>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
