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

  <div class="header-center"></div>

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
