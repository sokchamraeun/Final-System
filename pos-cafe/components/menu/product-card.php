<?php
$p = $p ?? [];
$bestSellerName = $bestSellerName ?? null;
$sizesByProduct = $sizesByProduct ?? [];
$iceByProduct   = $iceByProduct   ?? [];
$sugarByProduct = $sugarByProduct ?? [];
$milkByProduct  = $milkByProduct  ?? [];
$addonsByProduct = $addonsByProduct ?? [];

/* ── Discount badge (e.g. "30% Off") drives a struck-through original price.
   Purely a display computation — products.price already holds the real,
   charged amount; nothing here changes what the cart bills. ── */
$badgeText   = (string) ($p['badge_text'] ?? '');
$discountPct = 0;
if ($badgeText !== '' && preg_match('/(\d{1,2})\s*%/', $badgeText, $m)) {
    $discountPct = min(90, (int) $m[1]);
}
$cardPrice    = (float) $p['price'];
$cardOldPrice = $discountPct > 0 ? $cardPrice / (1 - $discountPct / 100) : null;

$cardSizes = $sizesByProduct[(int) $p['product_id']] ?? [];
$sizeLine  = $cardSizes
    ? implode(' / ', array_map(fn($s) => $s['label'], $cardSizes))
    : 'Regular';
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
     data-product-addons='<?= htmlspecialchars(json_encode($addonsByProduct[(int)$p['product_id']] ?? []), ENT_QUOTES) ?>'
     data-is-bestseller="<?= $p['name']===$bestSellerName?'1':'0' ?>"
     role="button" tabindex="0">
  <div class="card-img">
    <?php if ($discountPct > 0): ?><span class="product-badge badge-discount"><?= (int) $discountPct ?>% OFF</span>
    <?php elseif (!empty($p['badge_text'])): ?><span class="product-badge"><?= e($p['badge_text']) ?></span><?php endif; ?>
    <?php if ($p['name']===$bestSellerName): ?><span class="badge-bestseller">&#x2605; Best Seller</span><?php endif; ?>
    <img src="<?= e(root_url($p['image'])) ?>" loading="lazy" alt="<?= e($p['name']) ?>">
  </div>
  <div class="card-info">
    <div class="card-name"><?= e($p['name']) ?></div>
    <div class="card-size-line">Size: <?= e($sizeLine) ?></div>
    <div class="card-bottom">
      <div class="card-price-row">
        <?php if ($cardSizes): ?>
          <span class="card-price"><?= implode(' / ', array_map(fn($s) => '$' . number_format($s['price'], 0), $cardSizes)) ?></span>
        <?php else: ?>
          <?php if ($cardOldPrice !== null): ?><span class="card-price-old">$<?= number_format($cardOldPrice, 2) ?></span><?php endif; ?>
          <span class="card-price<?= $cardOldPrice !== null ? ' discounted' : '' ?>">$<?= number_format($cardPrice, 2) ?></span>
        <?php endif; ?>
      </div>
      <?php if ((int)($p['has_sizes'] ?? 0) === 1): ?>
      <button class="btn-add-full" onclick="event.stopPropagation(); openModalFromCard(this.closest('.product-card'));"><i class="fa-solid fa-plus"></i> Add</button>
      <?php else: ?>
      <button class="btn-add-full" onclick="event.stopPropagation(); quickAdd(<?= (int)$p['product_id'] ?>, <?= (float)$p['price'] ?>);"><i class="fa-solid fa-plus"></i> Add</button>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
