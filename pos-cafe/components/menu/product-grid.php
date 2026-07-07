<?php
$is_price_sort = $is_price_sort ?? false;
$flat_products = $flat_products ?? [];
$products = $products ?? [];
$categories = $categories ?? [];
$catIcons = $catIcons ?? [];
$sort = $sort ?? 'default';
$bestSellerName = $bestSellerName ?? null;
$sizesByProduct = $sizesByProduct ?? [];
$iceByProduct   = $iceByProduct   ?? [];
$sugarByProduct = $sugarByProduct ?? [];
$search_term = $search_term ?? '';

if (!function_exists('cat_anchor_id')) {
    function cat_anchor_id($key) {
        $slug = strtolower(trim((string)$key));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return 'cat-' . ($slug !== '' ? $slug : 'uncategorized');
    }
}
?>
<?php if ($is_price_sort && !empty($flat_products)): ?>
  <div class="cat-header" style="margin-top:20px;">
    <div class="cat-title-text">
      <h2><?= $sort==='price_low' ? 'Price: Low to High' : 'Price: High to Low' ?></h2>
    </div>
  </div>
  <div class="product-grid">
    <?php foreach ($flat_products as $p): ?>
      <?php $P = $p; component('menu/product-card', ['p' => $P, 'bestSellerName' => $bestSellerName, 'sizesByProduct' => $sizesByProduct, 'iceByProduct' => $iceByProduct, 'sugarByProduct' => $sugarByProduct]) ?>
    <?php endforeach; ?>
  </div>
<?php elseif (!empty($products)): ?>
  <?php foreach ($categories as $key => $label):
    if (empty($products[$key])) continue;
    $anchor = cat_anchor_id($key);
    $icon   = $catIcons[$key] ?? 'fa-circle';
  ?>
  <section class="cat-section" id="<?= e($anchor) ?>">
    <div class="cat-header">
      <div class="cat-icon"><i class="fa-solid <?= $icon ?>"></i></div>
      <div class="cat-title-text">
        <h2><?= e($label) ?></h2>
        <?php $_in_stock = count(array_filter($products[$key], fn($p) => (int)$p['low_count'] === 0)); ?>
        <span><?= $_in_stock ?> item<?= $_in_stock!==1?'s':'' ?></span>
      </div>
    </div>
    <div class="product-grid">
    <?php foreach ($products[$key] as $p): ?>
      <?php $P = $p; component('menu/product-card', ['p' => $P, 'bestSellerName' => $bestSellerName, 'sizesByProduct' => $sizesByProduct, 'iceByProduct' => $iceByProduct, 'sugarByProduct' => $sugarByProduct]) ?>
    <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
<?php else: ?>
  <div class="empty-state">
    <i class="fa-solid fa-mug-hot"></i>
    <h3>No items found</h3>
    <p>Try a different search term.</p>
    <?php if (!empty($search_term)): ?>
    <a href="<?= e(url('pages/menu/index.php')) ?>" class="btn-clear-search">Clear search</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
