<?php
$top_sellers = $top_sellers ?? [];
$bestSellerName = $bestSellerName ?? null;
$sizesByProduct = $sizesByProduct ?? [];
$iceByProduct   = $iceByProduct   ?? [];
$sugarByProduct = $sugarByProduct ?? [];
$milkByProduct  = $milkByProduct  ?? [];
$addonsByProduct = $addonsByProduct ?? [];
?>
<?php if (!empty($top_sellers)): ?>
<section class="top-sellers cat-section" id="cat-top-sellers">
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
     data-product-addons='<?= htmlspecialchars(json_encode($addonsByProduct[(int)$t['product_id']] ?? []), ENT_QUOTES) ?>'
     data-is-bestseller="<?= $t['name']===$bestSellerName?'1':'0' ?>"
         role="button" tabindex="0">
      <?php
        $tsBadge = (string) ($t['badge_text'] ?? '');
        $tsPct   = 0;
        if ($tsBadge !== '' && preg_match('/(\d{1,2})\s*%/', $tsBadge, $tsm)) $tsPct = min(90, (int) $tsm[1]);
        $tsPrice = (float) $t['price'];
        $tsOld   = $tsPct > 0 ? $tsPrice / (1 - $tsPct / 100) : null;
        $tsSizes = $sizesByProduct[(int) $t['product_id']] ?? [];
        $tsSizeLine = $tsSizes ? implode(' / ', array_map(fn($s) => $s['label'], $tsSizes)) : 'Regular';
      ?>
      <div class="card-img">
        <?php if ($tsPct > 0): ?><span class="product-badge badge-discount"><?= (int) $tsPct ?>% OFF</span>
        <?php elseif ($tsBadge !== ''): ?><span class="product-badge"><?= e($tsBadge) ?></span><?php endif; ?>
        <img src="<?= e(root_url($t['image'])) ?>" loading="lazy" alt="<?= e($t['name']) ?>">
      </div>
      <div class="card-info">
        <div class="card-name"><?= e($t['name']) ?></div>
        <div class="seller-rank"><?= $idx===0 ? '&#x1F3C6; #1 Seller' : '&#x1F525; Top Pick' ?></div>
        <div class="card-size-line">Size: <?= e($tsSizeLine) ?></div>
        <div class="card-bottom">
          <div class="card-price-row">
            <?php if ($tsSizes): ?>
              <span class="card-price"><?= implode(' / ', array_map(fn($s) => '$' . number_format($s['price'], 0), $tsSizes)) ?></span>
            <?php else: ?>
              <?php if ($tsOld !== null): ?><span class="card-price-old">$<?= number_format($tsOld, 2) ?></span><?php endif; ?>
              <span class="card-price<?= $tsOld !== null ? ' discounted' : '' ?>">$<?= number_format($tsPrice, 2) ?></span>
            <?php endif; ?>
          </div>
          <?php if ((int)($t['has_sizes'] ?? 0) === 1): ?>
          <button class="btn-add-full" onclick="event.stopPropagation(); openModalFromCard(this.closest('.product-card'));"><i class="fa-solid fa-plus"></i> Add</button>
          <?php else: ?>
          <button class="btn-add-full" onclick="event.stopPropagation(); quickAdd(<?= (int)$t['product_id'] ?>, <?= (float)$t['price'] ?>);"><i class="fa-solid fa-plus"></i> Add</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
