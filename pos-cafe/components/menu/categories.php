<?php
$categories = $categories ?? [];
$products = $products ?? [];
$catIcons = $catIcons ?? [];
$catImages = $catImages ?? [];
$search_term = $search_term ?? '';
$sort = $sort ?? 'default';
$top_sellers = $top_sellers ?? [];
$promo_products = $promo_products ?? [];
?>
<div class="cat-bar">
  <form class="cat-search" method="GET" id="searchForm">
    <div class="search-inner">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" placeholder="Search..." value="<?= e($search_term) ?>" id="searchInput" autocomplete="off">
      <?php if (!empty($search_term)): ?>
      <a href="<?= e(url('pages/menu/index.php')) ?>" class="search-clear"><i class="fa-solid fa-xmark"></i></a>
      <?php endif; ?>
    </div>
    <select name="sort" id="sortSelect" class="sort-select">
      <option value="default" <?= $sort==='default'?'selected':'' ?>>Default</option>
      <option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Price: Low &rarr; High</option>
      <option value="price_high" <?= $sort==='price_high'?'selected':'' ?>>Price: High &rarr; Low</option>
    </select>
    <button type="button" class="cp-view-toggle" id="cpViewToggle" title="Toggle view" onclick="cpToggleView()">
      <i class="fa-solid fa-list" id="cpViewIcon"></i>
    </button>
  </form>
  <nav class="cat-nav" id="catNav">
  <a href="#cat-all" class="cat-pill active" data-target="cat-all">
    <span class="cat-pill-img"><i class="fa-solid fa-grip"></i></span>
    <span class="cat-pill-label">All</span>
  </a>
  <?php if (!empty($top_sellers)): ?>
  <a href="#cat-top-sellers" class="cat-pill" data-target="cat-top-sellers">
    <span class="cat-pill-img"><i class="fa-solid fa-fire"></i></span>
    <span class="cat-pill-label">Top Seller</span>
  </a>
  <?php endif; ?>
  <?php if (!empty($promo_products)): ?>
  <a href="#cat-promotions" class="cat-pill" data-target="cat-promotions">
    <span class="cat-pill-img"><i class="fa-solid fa-tags"></i></span>
    <span class="cat-pill-label">Promotion</span>
  </a>
  <?php endif; ?>
  <?php foreach ($categories as $key => $label):
    if (empty($products[$key])) continue;
    $count  = count(array_filter($products[$key], fn($p) => (int)$p['low_count'] === 0));
    $anchor = e(cat_anchor_id($key));
    $icon   = $catIcons[$key] ?? 'fa-circle';
  ?>
  <a href="#<?= $anchor ?>" class="cat-pill" data-target="<?= $anchor ?>">
    <span class="cat-pill-img">
      <?php if (!empty($catImages[$key])): ?>
      <img src="<?= e(root_url($catImages[$key])) ?>" alt="<?= e($label) ?>" loading="lazy">
      <?php else: ?>
      <i class="fa-solid <?= $icon ?>"></i>
      <?php endif; ?>
    </span>
    <span class="cat-pill-label"><?= e($label) ?></span>
    <span class="pill-count"><?= $count ?></span>
  </a>
  <?php endforeach; ?>
</nav>
</div>
