<?php
$categories = $categories ?? [];
$products = $products ?? [];
$catIcons = $catIcons ?? [];
$catImages = $catImages ?? [];
?>
<nav class="cat-nav" id="catNav">
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
