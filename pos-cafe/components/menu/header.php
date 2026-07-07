<?php
$search_term = $search_term ?? '';
$sort = $sort ?? 'default';
$active_orders = $active_orders ?? 0;
$add_to_order_mode = $add_to_order_mode ?? 0;
$_show_kitchen_btn = ($_SESSION['role'] ?? '') === 'barista';
?>
<header class="menu-header">
  <div class="header-left">
    <button class="btn-nav" onclick="sbToggle()" title="Toggle navigation menu" style="display:flex">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>

  <div class="header-center">
    <form class="search-form" method="GET" id="searchForm">
      <div class="search-inner">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" placeholder="Search drinks..." value="<?= e($search_term) ?>" id="searchInput" autocomplete="off">
        <?php if (!empty($search_term)): ?>
        <a href="<?= e(url('pages/menu/index.php')) ?>" class="search-clear"><i class="fa-solid fa-xmark"></i></a>
        <?php endif; ?>
      </div>
      <select name="sort" id="sortSelect" class="sort-select">
        <option value="default" <?= $sort==='default'?'selected':'' ?>>Default</option>
        <option value="price_low" <?= $sort==='price_low'?'selected':'' ?>>Price: Low â†’ High</option>
        <option value="price_high" <?= $sort==='price_high'?'selected':'' ?>>Price: High â†’ Low</option>
      </select>
    </form>
  </div>

  <div class="header-right">
    <div class="brand">
      <img src="<?= e(root_url('images/Newlogo.jpg')) ?>" alt="Logo">
      <span class="brand-name">Bird's Nest</span>
    </div>
    <button id="chatToggle" onclick="toggleChat()" title="AI Assistant">
      <i class="fa-solid fa-robot"></i>
    </button>
    <button class="btn-theme" id="themeToggle" onclick="toggleTheme()" title="Toggle theme">
      <i class="fa-solid fa-moon" id="themeIcon"></i>
    </button>
  </div>
</header>
