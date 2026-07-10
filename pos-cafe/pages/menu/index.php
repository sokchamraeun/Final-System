<?php
declare(strict_types=1);
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('find_orders')) { redirect(url('index.php?denied=1')); }

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function cat_anchor_id($key) {
    $slug = strtolower(trim((string)$key));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return 'cat-' . ($slug !== '' ? $slug : 'uncategorized');
}

/* â”€â”€ NAV: view_order.php (Kitchen) is for barista only; admin/manager go to dashboard â”€â”€ */
$_show_kitchen_btn = ($_SESSION['role'] ?? '') === 'barista';

/* â”€â”€ CART CALCULATIONS â”€â”€ */
$cart = $_SESSION['cart'] ?? [];
$cart_count = 0;
$cp_subtotal = 0.0; $cp_min_price = PHP_FLOAT_MAX; $cp_cheapest_idx = -1;
$_cp_fpid = defined('FREE_ITEM_PRODUCT_ID') ? (int)FREE_ITEM_PRODUCT_ID : 0;
$_cp_fname = ''; $_cp_fprice = 0.0; $_cp_fidx = -1;

foreach ($cart as $idx => $item) {
    $q = (int)($item['qty'] ?? 1); $p = (float)($item['price'] ?? 0);
    $cart_count += $q; $cp_subtotal += $p * $q;
    if ($p < $cp_min_price) { $cp_min_price = $p; $cp_cheapest_idx = $idx; }
    if ($_cp_fpid > 0 && (int)($item['product_id'] ?? 0) === $_cp_fpid && $_cp_fidx < 0) {
        $_cp_fidx = $idx; $_cp_fname = $item['product_name'] ?? ''; $_cp_fprice = $p;
    }
}
if ($_cp_fpid > 0 && $_cp_fname === '') {
    $_fp_s = $conn->prepare("SELECT name, price FROM products WHERE product_id = ?");
    if ($_fp_s) { $_fp_s->bind_param("i", $_cp_fpid); $_fp_s->execute();
        if ($_fp_r = $_fp_s->get_result()->fetch_assoc()) { $_cp_fname = $_fp_r['name']; $_cp_fprice = (float)$_fp_r['price']; }
        $_fp_s->close(); }
}
$cp_cheapest_name  = ($cp_cheapest_idx >= 0) ? ($cart[$cp_cheapest_idx]['product_name'] ?? '') : '';
$cp_cheapest_price = ($cp_cheapest_idx >= 0 && $cp_min_price < PHP_FLOAT_MAX) ? $cp_min_price : 0.0;
$cp_free_name  = ($_cp_fpid > 0 && $_cp_fname !== '') ? $_cp_fname : $cp_cheapest_name;
$cp_free_price = ($_cp_fpid > 0 && $_cp_fprice > 0) ? $_cp_fprice : $cp_cheapest_price;
$_cp_free_idx = ($_cp_fpid > 0 && $_cp_fidx >= 0) ? $_cp_fidx : $cp_cheapest_idx;
$cp_buy3 = (BUY_X_GET_1_ENABLED && $cart_count >= BUY_X_COUNT && $cp_min_price < PHP_FLOAT_MAX && $_cp_free_idx >= 0)
    ? floor($cart_count / BUY_X_COUNT) * $cp_free_price : 0.0;

$cp_hh = 0.0;
if (HAPPY_HOUR_ENABLED && (int)date('H') >= HAPPY_HOUR_START && (int)date('H') < HAPPY_HOUR_END)
    $cp_hh = $cp_subtotal * (HAPPY_HOUR_DISCOUNT / 100);

$cp_after = $cp_subtotal - $cp_hh;
$cp_md = $_SESSION['manual_discount'] ?? null;
$cp_manual = 0.0; $cp_manual_label = '';
if ($cp_md && (float)($cp_md['amount'] ?? 0) > 0) {
    $cp_manual = $cp_md['type'] === 'flat'
        ? min((float)$cp_md['amount'], max(0, $cp_after))
        : max(0, $cp_after) * ((float)$cp_md['amount'] / 100.0);
    $r = trim($cp_md['reason'] ?? ''); $cp_manual_label = $r ?: 'Discount';
    if ($cp_md['type'] === 'percent') $cp_manual_label .= ' (' . (int)$cp_md['amount'] . '% off)';
    $cp_after -= $cp_manual;
}
$cp_tax   = $cp_after * (TAX_RATE / 100);
$cp_total = round($cp_after + $cp_tax, 2);

/* â”€â”€ LOYALTY â”€â”€ */
$linked_loyalty = null;
$linked_loyalty_id_int = isset($_SESSION['loyalty_card_id']) ? (int)$_SESSION['loyalty_card_id'] : 0;
if ($linked_loyalty_id_int > 0) {
    $lc = $conn->prepare("SELECT loyalty_id, points FROM loyalty_cards WHERE card_id = ?");
    if ($lc) { $lc->bind_param("i", $linked_loyalty_id_int); $lc->execute(); $linked_loyalty = $lc->get_result()->fetch_assoc(); }
}

/* â”€â”€ ADD TO EXISTING ORDER DETECTION â”€â”€ */
$add_to_order_mode = isset($_GET['add_to_order']) ? (int)$_GET['add_to_order'] : 0;

/* â”€â”€ ACTIVE ORDERS COUNT â”€â”€ */
date_default_timezone_set('Asia/Phnom_Penh');
$now_dt    = new DateTime();
$today6am  = (clone $now_dt)->setTime(6, 0, 0);
if ($now_dt < $today6am) $today6am->modify('-1 day');
$day_start = $today6am->format('Y-m-d H:i:s');
$day_end   = (clone $today6am)->modify('+1 day -1 second')->format('Y-m-d H:i:s');
$stmt_ao   = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status IN ('Preparing','PendingPayment') AND order_date >= ? AND order_date <= ?");
$stmt_ao->bind_param('ss', $day_start, $day_end);
$stmt_ao->execute();
$active_orders = (int)$stmt_ao->get_result()->fetch_row()[0];

/* â”€â”€ BEST SELLER â”€â”€ */
$bestSellerName = null;
$bs = mysqli_query($conn, "SELECT product_name FROM order_items GROUP BY product_name ORDER BY SUM(quantity) DESC LIMIT 1");
if ($bs && $r = mysqli_fetch_assoc($bs)) $bestSellerName = $r['product_name'];

/* â”€â”€ TOP SELLERS â”€â”€ */
$top_sellers = [];
$ts_result = mysqli_query($conn, "SELECT p.*, COALESCE(SUM(oi.quantity),0) AS total_sold FROM products p LEFT JOIN order_items oi ON p.product_id = oi.product_id WHERE p.is_available = 1 GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 6");
while ($ts_row = mysqli_fetch_assoc($ts_result)) {
    if ((int)$ts_row['total_sold'] > 0) $top_sellers[] = $ts_row;
}

/* â”€â”€ FETCH ALL PRODUCTS â”€â”€ */
$search_term   = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort          = $_GET['sort'] ?? 'default';
$is_price_sort = ($sort === 'price_low' || $sort === 'price_high');

$query = "SELECT p.*, (SELECT COUNT(*) FROM product_ingredients pi JOIN ingredients i ON pi.ingredient_id = i.ingredient_id WHERE pi.product_id = p.product_id AND i.stock_quantity < pi.amount_used) AS low_count FROM products p WHERE p.is_available = 1";
$query .= " ORDER BY " . ($sort === 'price_low' ? "p.price ASC" : ($sort === 'price_high' ? "p.price DESC" : "p.category, p.name"));
if (!empty($search_term)) {
    $query = "SELECT p.*, (SELECT COUNT(*) FROM product_ingredients pi JOIN ingredients i ON pi.ingredient_id = i.ingredient_id WHERE pi.product_id = p.product_id AND i.stock_quantity < pi.amount_used) AS low_count FROM products p WHERE p.is_available = 1 AND p.name LIKE ?";
    $query .= " ORDER BY " . ($sort === 'price_low' ? "p.price ASC" : ($sort === 'price_high' ? "p.price DESC" : "p.category, p.name"));
    $stmt_search = $conn->prepare($query);
    $like_param  = '%' . $search_term . '%';
    $stmt_search->bind_param("s", $like_param);
    $stmt_search->execute();
    $result = $stmt_search->get_result();
} else {
    $result = mysqli_query($conn, $query);
}

$categories = []; $catIcons = []; $catImages = []; $catSettings = [];
$_cat_res = $conn->query("SELECT slug, name, icon, image, enable_ice, enable_sugar, enable_milk, enable_addons FROM categories WHERE is_active = 1 ORDER BY display_order");
while ($_cat_row = $_cat_res->fetch_assoc()) {
    $categories[$_cat_row['slug']] = $_cat_row['name'];
    $catIcons[$_cat_row['slug']]   = $_cat_row['icon'];
    $catImages[$_cat_row['slug']]  = $_cat_row['image'];
    $catSettings[$_cat_row['slug']] = [
        'enable_ice'    => (int)($_cat_row['enable_ice'] ?? 1),
        'enable_sugar'  => (int)($_cat_row['enable_sugar'] ?? 1),
        'enable_milk'   => (int)($_cat_row['enable_milk'] ?? 1),
        'enable_addons' => (int)($_cat_row['enable_addons'] ?? 1),
    ];
}

$products = []; $flat_products = []; $promo_products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $products[$row['category']][] = $row;
    $flat_products[] = $row;
    if (!empty($row['badge_text'])) $promo_products[] = $row;
}

/* â”€â”€ SIZES PER PRODUCT (for sized products: has_sizes=1) â”€â”€ */
$sizesByProduct = [];
if (!empty($flat_products)) {
    $sz_res = $conn->query("SELECT product_id, size_code, label, price, promo_pct FROM product_sizes ORDER BY product_id, sort_order ASC");
    while ($sz_res && $sz_row = $sz_res->fetch_assoc()) {
        $sizesByProduct[(int)$sz_row['product_id']][] = [
            'code'      => $sz_row['size_code'],
            'label'     => $sz_row['label'],
            'price'     => (float)$sz_row['price'],
            'promo_pct' => min(90, (int)($sz_row['promo_pct'] ?? 0)),
        ];
    }
}

/* ── ICE, SUGAR & MILK LEVELS PER PRODUCT ── */
$iceByProduct   = [];
$sugarByProduct = [];
$milkByProduct  = [];
if (!empty($flat_products)) {
    $il_res = $conn->query("SELECT pil.product_id, il.id, il.name, p.category FROM product_ice_levels pil JOIN ice_levels il ON il.id = pil.ice_level_id JOIN products p ON p.product_id = pil.product_id");
    while ($il_res && $il_row = $il_res->fetch_assoc()) {
        $cat = $il_row['category'] ?? '';
        if (($catSettings[$cat]['enable_ice'] ?? 1) === 0) continue;
        $iceByProduct[(int)$il_row['product_id']][] = ['id' => (int)$il_row['id'], 'name' => $il_row['name']];
    }
    $sl_res = $conn->query("SELECT psl.product_id, sl.id, sl.name, p.category FROM product_sugar_levels psl JOIN sugar_levels sl ON sl.id = psl.sugar_level_id JOIN products p ON p.product_id = psl.product_id");
    while ($sl_res && $sl_row = $sl_res->fetch_assoc()) {
        $cat = $sl_row['category'] ?? '';
        if (($catSettings[$cat]['enable_sugar'] ?? 1) === 0) continue;
        $sugarByProduct[(int)$sl_row['product_id']][] = ['id' => (int)$sl_row['id'], 'name' => $sl_row['name']];
    }
    $ml_res = $conn->query("SELECT pml.product_id, ml.id, ml.name, p.category FROM product_milk_levels pml JOIN milk_levels ml ON ml.id = pml.milk_level_id JOIN products p ON p.product_id = pml.product_id");
    while ($ml_res && $ml_row = $ml_res->fetch_assoc()) {
        $cat = $ml_row['category'] ?? '';
        if (($catSettings[$cat]['enable_milk'] ?? 1) === 0) continue;
        $milkByProduct[(int)$ml_row['product_id']][] = ['id' => (int)$ml_row['id'], 'name' => $ml_row['name']];
    }
}

/* ── ADD-ONS PER PRODUCT ── */
$addonsByProduct = [];
if (!empty($flat_products)) {
    $ad_res = $conn->query("SELECT pa.product_id, a.addon_id, a.name, a.price, p.category FROM product_addons pa JOIN addons a ON a.addon_id = pa.addon_id JOIN products p ON p.product_id = pa.product_id WHERE a.is_active = 1");
    while ($ad_res && $ad_row = $ad_res->fetch_assoc()) {
        $cat = $ad_row['category'] ?? '';
        if (($catSettings[$cat]['enable_addons'] ?? 1) === 0) continue;
        $addonsByProduct[(int)$ad_row['product_id']][] = ['id' => (int)$ad_row['addon_id'], 'name' => $ad_row['name'], 'price' => (float)$ad_row['price']];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>(function(){if((localStorage.getItem('theme')||'dark')!=='light')document.documentElement.setAttribute('data-theme','dark');})();</script>
  <title>POS | Bird's Nest Coffee</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: ['selector', '[data-theme="dark"]'],
      theme: {
        extend: {
          fontFamily: { sans: ['Poppins', 'sans-serif'] },
          colors: {
            brand: {
              DEFAULT: '#14b8a6',
              dark:    '#0d9488',
              light:   '#5eead4',
            },
          },
        },
      },
    };
  </script>
  <?php component('menu/styles') ?>
</head>
<body>

<?php require POS_ROOT . '/components/sidebar/index.php'; ?>
<div class="flex flex-1 flex-col min-w-0 overflow-hidden lg:ml-[260px]">

<?php if (!empty($_SESSION['stock_warning'])): $__sw = $_SESSION['stock_warning']; unset($_SESSION['stock_warning']); ?>
<div id="stockWarn" style="max-width:1200px;margin:14px auto 0;padding:13px 16px;display:flex;gap:12px;align-items:flex-start;
     background:rgba(209,144,75,.10);border:1px solid rgba(209,144,75,.40);border-radius:12px;color:#d1904b;font-size:13.5px;line-height:1.5;">
  <i class="fa-solid fa-triangle-exclamation" style="margin-top:2px;"></i>
  <div style="flex:1;">
    <strong>Low stock on the last order.</strong> It was completed, but these ingredients ran short â€” restock soon:
    <ul style="margin:6px 0 0;padding-left:18px;">
      <?php foreach ($__sw as $line): ?>
      <li><?= htmlspecialchars($line) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss"
          style="background:none;border:none;color:#d1904b;cursor:pointer;font-size:15px;line-height:1;">&times;</button>
</div>
<?php endif; ?>

<?php component('menu/header', ['search_term' => $search_term, 'sort' => $sort, 'active_orders' => $active_orders, 'add_to_order_mode' => $add_to_order_mode]) ?>

<?php if ($add_to_order_mode > 0): ?>
<div class="add-order-banner">
  <i class="fa-solid fa-cart-plus"></i>
  Adding to Order #<?= $add_to_order_mode ?> &nbsp;&middot;&nbsp;
  <a href="<?= e(root_url('cart_paylater.php')) ?>" style="color:inherit;font-weight:700;text-decoration:underline;">View Cart &amp; Confirm</a>
</div>
<?php endif; ?>

<div class="pos-layout">
  <div class="menu-panel" id="menuPanel">
      <?php component('menu/categories', ['categories' => $categories, 'products' => $products, 'catIcons' => $catIcons, 'catImages' => $catImages, 'search_term' => $search_term, 'sort' => $sort, 'top_sellers' => $top_sellers, 'promo_products' => $promo_products]) ?>
    <div class="menu-scroll" id="menuScroll">
      <main class="menu-main">
        <?php component('menu/top-sellers', ['top_sellers' => $top_sellers, 'bestSellerName' => $bestSellerName, 'sizesByProduct' => $sizesByProduct, 'iceByProduct' => $iceByProduct, 'sugarByProduct' => $sugarByProduct, 'addonsByProduct' => $addonsByProduct]) ?>
        <?php component('menu/product-grid', ['is_price_sort' => $is_price_sort, 'flat_products' => $flat_products, 'products' => $products, 'promo_products' => $promo_products, 'top_sellers' => $top_sellers, 'categories' => $categories, 'catIcons' => $catIcons, 'sort' => $sort, 'bestSellerName' => $bestSellerName, 'sizesByProduct' => $sizesByProduct, 'iceByProduct' => $iceByProduct, 'sugarByProduct' => $sugarByProduct, 'addonsByProduct' => $addonsByProduct, 'search_term' => $search_term]) ?>
      </main>
    </div>
  </div>
  <?php component('menu/cart-panel', [
    'cart' => $cart, 'cart_count' => $cart_count, 'cp_subtotal' => $cp_subtotal,
    'cp_buy3' => $cp_buy3, 'cp_free_name' => $cp_free_name, 'cp_free_price' => $cp_free_price,
    'cp_hh' => $cp_hh, 'cp_manual' => $cp_manual, 'cp_manual_label' => $cp_manual_label,
    'cp_after' => $cp_after, 'cp_tax' => $cp_tax, 'cp_total' => $cp_total,
    'linked_loyalty' => $linked_loyalty, 'add_to_order_mode' => $add_to_order_mode,
  ]) ?>
</div>

<?php component('menu/product-modal') ?>
<?php component('menu/payment-modal', ['add_to_order_mode' => $add_to_order_mode]) ?>
<div id="toast-container"></div>
<?php component('menu/chat') ?>
<?php component('menu/loyalty-modal') ?>
<?php component('menu/scripts', [
  'csrf_token' => $_SESSION['csrf_token'],
  'add_to_order_mode' => $add_to_order_mode
]) ?>

</div>

</body>
</html>
