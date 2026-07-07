<?php
declare(strict_types=1);
/* ============================================================
   Unified sidebar — the only sidebar file.
   Used by all pages: included directly from root legacy files
   and loaded by app.php for pos-cafe pages.
   Tailwind design, permission-gated, collapsible groups,
   live clock, self-contained badge-count queries.
   ============================================================ */
if (defined('SIDEBAR_RENDERED')) return;
define('SIDEBAR_RENDERED', true);

/* ── Self-bootstrap view helpers (e, url, root_url, component…) when this
   sidebar is included from a legacy page that only loaded the root
   auth.php/config.php and never booted pos-cafe/config/app.php — e.g.
   profile.php, report.php, reconciliation_report.php.
   No-op inside the pos-cafe app, where these already exist. ── */
if (!function_exists('e')) {
    if (!defined('POS_ROOT')) define('POS_ROOT', dirname(__DIR__, 2));  // .../pos-cafe
    if (!defined('APP_ROOT')) define('APP_ROOT', dirname(POS_ROOT));
    if (!defined('ROOT_URL')) define('ROOT_URL', '/FinalSystem');
    if (!defined('BASE_URL')) define('BASE_URL', ROOT_URL . '/pos-cafe');
    if (!defined('APP_NAME')) define('APP_NAME', "Bird's Nest POS");
    require_once POS_ROOT . '/includes/helpers.php';
}

$isPosCafe = defined('POS_ROOT') && class_exists('Auth');

/* ── DB connection (accessed through globals when called via component()) ── */
$conn = $GLOBALS['conn'] ?? null;

/* ── Role info fallback (for legacy pages that don't set it) ── */
if (!isset($_cur_role_name) || !isset($_cur_role_color)) {
    $_cur_role = $_SESSION['role'] ?? 'staff';
    $_cur_role_info = null;
    if ($conn && $r = $conn->query("SELECT name, color FROM roles WHERE slug='" . $conn->real_escape_string($_cur_role) . "' LIMIT 1")) {
        $_cur_role_info = $r->fetch_assoc();
    }
    $_cur_role_name  = $_cur_role_name  ?? ($_cur_role_info['name']  ?? ucwords(str_replace('_', ' ', $_cur_role)));
    $_cur_role_color = $_cur_role_color ?? ($_cur_role_info['color'] ?? '#d1904b');
}

/* ── Profile ── */
if ($isPosCafe) {
    $profileName      = Auth::name();
    $profileRoleName  = Auth::roleMeta()['name'] ?? '';
    $profileRoleColor = Auth::roleMeta()['color'] ?? '#d1904b';
    $profileInitials  = Auth::initials();
} else {
    $profileName      = $admin_name   ?? ($_SESSION['username']  ?? 'User');
    $profileRoleName  = $_cur_role_name  ?? '';
    $profileRoleColor = $_cur_role_color ?? '#d1904b';
    $profileInitials  = strtoupper(substr($profileName, 0, 1));
}

/* ── Badge counts (lazy-load from DB if not already set by the page) ── */
if ($conn) {
    if (!isset($low_stock)) {
        $r = $conn->query("SELECT COUNT(*) FROM ingredients WHERE stock_quantity < minimum_stock");
        $low_stock = (int)$r->fetch_row()[0];
    }
    if (!isset($unpaid_count)) {
        $r = $conn->query("SELECT COUNT(*) FROM orders WHERE status='PendingPayment' AND DATE(order_date)=CURDATE()");
        $unpaid_count = (int)$r->fetch_row()[0];
    }
    if (!isset($paylater_count)) {
        $r = $conn->query("SELECT COUNT(*) FROM orders WHERE payment_method='paylater' AND status IN ('Preparing','PendingPayment','Completed')");
        $paylater_count = (int)$r->fetch_row()[0];
    }
    if (!isset($pending_count) || !isset($preparing_count)) {
        $bd = new DateTime();
        $business_date = (int)$bd->format('H') < 6 ? $bd->modify('-1 day')->format('Y-m-d') : $bd->format('Y-m-d');
        $st = $conn->prepare("SELECT status, COUNT(*) FROM orders WHERE business_date=? GROUP BY status");
        $st->bind_param('s', $business_date);
        $st->execute();
        $res = $st->get_result();
        $sc = [];
        while ($row = $res->fetch_row()) $sc[$row[0]] = $row[1];
        $pending_count   = $pending_count   ?? ($sc['PendingPayment'] ?? 0);
        $preparing_count = $preparing_count ?? ($sc['Preparing'] ?? 0);
    }
    if (!isset($sales)) {
        $r = $conn->query("SELECT IFNULL(SUM(total),0) FROM orders WHERE DATE(order_date)=CURDATE() AND status='Completed'");
        $sales = (float)$r->fetch_row()[0];
    }
    if (!isset($total_orders)) {
        $r = $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date)=CURDATE()");
        $total_orders = (int)$r->fetch_row()[0];
    }
    if (!isset($_recon_alerts)) {
        $_recon_alerts = 0;
        if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin','manager','supervisor'])) {
            $r = $conn->query("SELECT COUNT(*) FROM cash_counts WHERE shift_date=CURDATE() AND ABS(difference)>=0.01");
            if ($r) $_recon_alerts = (int)$r->fetch_row()[0];
        }
    }
    if (!isset($_unread_ann)) {
        $_unread_ann = 0;
        if (isset($_SESSION['user_id'])) {
            $st = $conn->prepare("SELECT COUNT(*) FROM announcements a
                WHERE a.is_active = 1
                  AND (a.expires_at IS NULL OR a.expires_at >= CURDATE())
                  AND NOT EXISTS (SELECT 1 FROM announcement_reads r WHERE r.announcement_id = a.id AND r.user_id = ?)");
            $st->bind_param('i', $_SESSION['user_id']);
            $st->execute();
            $st->bind_result($_unread_ann);
            $st->fetch();
            $st->close();
        }
    }
}

$sb_low_stock    = (int)($low_stock    ?? 0);
$sb_unpaid       = (int)($unpaid_count ?? 0);
$sb_paylater     = (int)($paylater_count ?? 0);
$sb_pending      = (int)($pending_count ?? 0);
$sb_preparing    = (int)($preparing_count ?? 0);
$sb_recon_alerts = (int)($_recon_alerts ?? 0);
$sb_unread_ann   = (int)($_unread_ann ?? 0);
$sb_sales        = (float)($sales ?? 0);
$sb_total_orders = (int)($total_orders ?? 0);

/* ── Active page detection ── */
$sb_cur          = $navActive ?? '';
$sb_request_uri  = $_SERVER['REQUEST_URI'] ?? '';
if (!$sb_cur) {
    /* Extract module from clean URL (e.g. /pos-cafe/dashboard, /pos-cafe/products/edit?id=5) */
    if (preg_match('#/pos-cafe/([\w-]+)#', $sb_request_uri, $m)) {
        $sb_cur = $m[1];
    } else {
        $sb_cur = basename($_SERVER['PHP_SELF'] ?? '');
    }
}
$sb_groups       = [
    'overview'       => ['dashboard'],
    'orders'         => ['orders', 'orders-board'],
    'operations'     => ['stands'],
    'loyalty'        => ['loyalty'],
    'catalog'        => ['products', 'categories', 'inventory', 'recipes', 'ingredients', 'levels'],
    'reconciliation' => ['stock'],
    'procurement'    => ['suppliers', 'purchase-orders'],
    'analytics'      => ['report', 'reports'],
    'staff'          => ['employees', 'roles', 'attendance', 'announcements', 'settings', 'admins'],
];
$sb_active_group = null;
foreach ($sb_groups as $g => $pages) {
    if (in_array($sb_cur, $pages, true)) { $sb_active_group = $g; break; }
}
if ($sb_active_group === null) $sb_active_group = 'overview';
$sb_open  = fn($g) => $g === $sb_active_group;
$sb_act   = fn($href) => $sb_cur === $href ? ' active' : '';
$_can_stands = in_array($_SESSION['role'] ?? '', ['admin', 'manager', 'staff'], true);
?>
<aside id="appSidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-[242px] -translate-x-full flex-col bg-[#111111] text-slate-300 transition-transform duration-300 max-h-screen lg:translate-x-0"
       style="font-family:'Poppins',sans-serif">

  <style>
    :root{--sb-w:242px}
    @media(min-width:1024px){
      .vo-main-col{ margin-left:242px; }
      .back-btn{ left:calc(22px + var(--sb-w))!important; }
      .sc-bar{ left:calc(20px + var(--sb-w))!important; }
    }
    [data-theme="light"] #appSidebar{background:#ffffff!important;border-right:1px solid #e2e5ea;}
    .sb-scroll::-webkit-scrollbar{display:none}
    .sb-scroll{scrollbar-width:none}
    #appSidebar .group-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#888;padding:14px 14px 6px;opacity:.85;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;border-radius:7px;transition:opacity .2s,color .2s}
    #appSidebar .group-label:hover{opacity:1;color:#f5f5f5}
    #appSidebar .group-label.open{opacity:1;color:#d1904b}
    #appSidebar .nav-chev{font-size:9px;flex-shrink:0;margin-left:4px;transition:transform .28s}
    #appSidebar .group-label.open .nav-chev{transform:rotate(90deg)}
    #appSidebar .group-items{overflow:hidden;max-height:400px;transition:max-height .32s,opacity .22s;opacity:1}
    #appSidebar .group-items.collapsed{max-height:0!important;opacity:0;pointer-events:none}
    #appSidebar .nav-link{display:flex;align-items:center;gap:10px;padding:9px 14px;border-radius:7px;color:#888;font-size:13.5px;font-weight:500;transition:background .18s,color .18s,box-shadow .18s,border-color .18s;margin-bottom:2px;border:1px solid transparent;position:relative;text-decoration:none}
    #appSidebar .nav-link:hover{background:linear-gradient(90deg,rgba(209,144,75,.13) 0%,rgba(209,144,75,.04) 100%);color:#d1904b;border-color:rgba(209,144,75,.22);box-shadow:0 2px 16px rgba(209,144,75,.18),inset 0 0 16px rgba(209,144,75,.05)}
    #appSidebar .nav-link.active{background:linear-gradient(90deg,rgba(209,144,75,.18) 0%,rgba(209,144,75,.06) 100%);color:#d1904b;font-weight:600;border-color:rgba(209,144,75,.3);box-shadow:0 2px 20px rgba(209,144,75,.25),inset 0 0 20px rgba(209,144,75,.07)}
    #appSidebar .nav-link i{width:16px;text-align:center;font-size:13px;flex-shrink:0}
    #appSidebar .badge-pill{margin-left:auto;flex-shrink:0;background:#d1904b;color:#000;font-size:9.5px;font-weight:800;padding:1px 7px;border-radius:50px;min-width:18px;text-align:center}
    #appSidebar .badge-red{background:#ff6b6b!important;color:#fff!important}
    #appSidebar .badge-purple{background:#9b59b6!important;color:#fff!important}
    [data-theme="light"] #appSidebar .nav-link{color:#5a6373}
    [data-theme="light"] #appSidebar .nav-link:hover{color:#d1904b}
    [data-theme="light"] #appSidebar .nav-link.active{color:#d1904b}
    [data-theme="light"] #appSidebar .group-label{color:#5a6373}
    [data-theme="light"] #appSidebar .group-label:hover{color:#111827}
    .sb-toggle{display:none;position:fixed;top:14px;left:14px;z-index:200;background:var(--bg-card,#111);border:1px solid var(--border,#1f1f1f);color:var(--text,#f5f5f5);width:40px;height:40px;border-radius:7px;align-items:center;justify-content:center;font-size:15px}
    .sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(4px);z-index:90}
    .sb-overlay.active{display:block}
    @media(max-width:1023px){
      #appSidebar{transform:translateX(-100%)}
      #appSidebar.open{transform:translateX(0)}
      .sb-toggle{display:flex}
    }
  </style>

  <button class="sb-toggle" onclick="sbToggle()"><i class="fa-solid fa-bars"></i></button>
  <div class="sb-overlay" onclick="sbToggle()"></div>

  <!-- Profile -->
  <div class="flex items-center gap-3 border-b border-white/10 px-4 py-3.5">
    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-xs font-bold text-white" style="background:<?= e($profileRoleColor) ?>"><?= e($profileInitials) ?></div>
    <div class="min-w-0 flex-1">
      <div class="truncate text-sm font-semibold text-white"><?= e($profileName) ?></div>
      <div class="flex items-center gap-1.5 text-[10px] text-slate-400"><span class="inline-block h-1.5 w-1.5 rounded-full" style="background:<?= e($profileRoleColor) ?>"></span><?= e($profileRoleName) ?></div>
    </div>
    <div class="text-[11px] font-bold text-amber-500" id="sbClock">--:--</div>
  </div>

  <!-- Logo -->
  <div class="flex items-center gap-3 px-4 py-3">
    <div class="grid h-9 w-9 place-items-center rounded-xl bg-amber-600 text-white shadow-lg"><i class="fa-solid fa-mug-hot text-sm"></i></div>
    <div class="min-w-0">
      <div class="truncate text-sm font-bold text-white"><?= e(APP_NAME) ?></div>
      <div class="text-[10px] text-slate-400">Café Management</div>
    </div>
    <button onclick="sbToggle()" class="ml-auto grid h-7 w-7 place-items-center rounded-lg text-slate-400 hover:bg-white/10 lg:hidden"><i class="fa-solid fa-xmark text-xs"></i></button>
  </div>

  <!-- Quick order -->
  <?php if (can('find_orders')): ?>
  <a href="<?= e(url('menu')) ?>" class="mx-3 mb-2 flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-[12px] font-bold text-black shadow-lg transition hover:bg-amber-400 hover:-translate-y-0.5"><i class="fa-solid fa-plus"></i> Take New Order</a>
  <?php endif; ?>

  <!-- Nav -->
  <nav class="sb-scroll flex-1 overflow-y-auto px-3 py-1">

    <!-- Overview -->
    <?php if (can('dashboard')): ?>
    <div class="group-label<?= $sb_open('overview') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="overview"><span>Overview</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('overview') ? '' : ' collapsed' ?>" data-gr="overview">
      <a class="nav-link<?= $sb_act('dashboard') ?>" href="<?= e(url('dashboard')) ?>"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span><?php if ($sb_pending + $sb_preparing > 0): ?><span class="badge-pill"><?= $sb_pending + $sb_preparing ?></span><?php endif; ?></a>
    </div>
    <?php endif; ?>

    <!-- Orders -->
    <?php if (can('find_orders') || can('view_orders')): ?>
    <div class="group-label<?= $sb_open('orders') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="orders"><span>Orders</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('orders') ? '' : ' collapsed' ?>" data-gr="orders">
      <?php if (can('find_orders')): ?>
      <a class="nav-link<?= $sb_act('find_order.php') ?>" href="<?= e(root_url('find_order.php')) ?>"><i class="fa-solid fa-magnifying-glass"></i><span>Find Unpaid Orders</span><?php if ($sb_paylater > 0): ?><span class="badge-pill badge-purple"><?= $sb_paylater ?></span><?php elseif ($sb_unpaid > 0): ?><span class="badge-pill badge-purple"><?= $sb_unpaid ?></span><?php endif; ?></a>
      <?php endif; ?>
      <?php if (can('view_orders')): ?>
      <a class="nav-link<?= $sb_act('orders-board') ?>" href="<?= e(url('orders/board')) ?>"><i class="fa-solid fa-receipt"></i><span>Orders</span></a>
      <?php endif; ?>
      <?php if (can('find_orders')): ?>
      <a class="nav-link<?= $sb_act('orders') ?>" href="<?= e(url('orders')) ?>"><i class="fa-solid fa-list"></i><span>All Orders</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Operations -->
    <?php if (can('barista_station') || can('customer_display') || $_can_stands): ?>
    <div class="group-label<?= $sb_open('operations') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="operations"><span>Operations</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('operations') ? '' : ' collapsed' ?>" data-gr="operations">
      <?php if (can('barista_station')): ?>
      <a class="nav-link<?= $sb_act('barista_display.php') ?>" href="<?= e(root_url('barista_display.php')) ?>"><i class="fa-solid fa-mug-hot"></i><span>Barista Station</span></a>
      <?php endif; ?>
      <?php if (can('customer_display')): ?>
      <a class="nav-link<?= $sb_act('customer_display.php') ?>" href="<?= e(root_url('customer_display.php')) ?>"><i class="fa-solid fa-display"></i><span>Customer Display</span></a>
      <?php endif; ?>
      <?php if ($_can_stands): ?>
      <a class="nav-link<?= $sb_act('stands') ?>" href="<?= e(url('stands')) ?>"><i class="fa-solid fa-table-cells-large"></i><span>Stand Numbers</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Loyalty -->
    <?php if (can('loyalty')): ?>
    <div class="group-label<?= $sb_open('loyalty') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="loyalty"><span>Loyalty</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('loyalty') ? '' : ' collapsed' ?>" data-gr="loyalty">
      <a class="nav-link<?= $sb_act('loyalty') ?>" href="<?= e(url('loyalty')) ?>"><i class="fa-solid fa-star"></i><span>Loyalty Card</span></a>
    </div>
    <?php endif; ?>

    <!-- Catalog -->
    <?php if (can('products') || can('categories') || can('ingredients') || can('recipes') || can('manage_levels')): ?>
    <div class="group-label<?= $sb_open('catalog') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="catalog"><span>Catalog</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('catalog') ? '' : ' collapsed' ?>" data-gr="catalog">
      <?php if (can('products')): ?>
      <a class="nav-link<?= $sb_act('products') ?>" href="<?= e(url('products')) ?>"><i class="fa-solid fa-cube"></i><span>Products</span></a>
      <?php endif; ?>
      <?php if (can('categories')): ?>
      <a class="nav-link<?= $sb_act('categories') ?>" href="<?= e(url('categories')) ?>"><i class="fa-solid fa-tags"></i><span>Categories</span></a>
      <?php endif; ?>
      <?php if (can('ingredients')): ?>
      <a class="nav-link<?= $sb_act('inventory') ?>" href="<?= e(url('inventory')) ?>"><i class="fa-solid fa-boxes-stacked"></i><span>Ingredients</span><?php if ($sb_low_stock > 0): ?><span class="badge-pill badge-red"><?= $sb_low_stock ?></span><?php endif; ?></a>
      <?php endif; ?>
      <?php if (can('recipes')): ?>
      <a class="nav-link<?= $sb_act('recipes') ?>" href="<?= e(url('recipes')) ?>"><i class="fa-solid fa-utensils"></i><span>Drink Recipe</span></a>
      <?php endif; ?>
      <?php if (can('manage_levels')): ?>
      <a class="nav-link<?= $sb_act('levels') ?>" href="<?= e(url('levels')) ?>"><i class="fa-solid fa-sliders"></i><span>Customize Levels</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reconciliation -->
    <?php if (can('cash_reconciliation') || can('stock_count')): ?>
    <div class="group-label<?= $sb_open('reconciliation') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="reconciliation"><span>Reconciliation</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('reconciliation') ? '' : ' collapsed' ?>" data-gr="reconciliation">
      <?php if (can('cash_reconciliation')): ?>
      <a class="nav-link<?= $sb_act('reconciliation_report.php') ?>" href="<?= e(root_url('reconciliation_report.php')) ?>"><i class="fa-solid fa-cash-register"></i><span>Cash Count</span><?php if ($sb_recon_alerts > 0): ?><span class="badge-pill badge-red"><?= $sb_recon_alerts ?></span><?php endif; ?></a>
      <?php endif; ?>
      <?php if (can('stock_count')): ?>
      <a class="nav-link<?= $sb_act('stock') ?>" href="<?= e(url('stock')) ?>"><i class="fa-solid fa-clipboard-list"></i><span>Stock Count</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Procurement -->
    <?php if (can('suppliers') || can('purchase_orders')): ?>
    <div class="group-label<?= $sb_open('procurement') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="procurement"><span>Procurement</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('procurement') ? '' : ' collapsed' ?>" data-gr="procurement">
      <?php if (can('suppliers')): ?>
      <a class="nav-link<?= $sb_act('suppliers') ?>" href="<?= e(url('suppliers')) ?>"><i class="fa-solid fa-truck-ramp-box"></i><span>Suppliers</span></a>
      <?php endif; ?>
      <?php if (can('purchase_orders')): ?>
      <a class="nav-link<?= $sb_act('purchase-orders') ?>" href="<?= e(url('purchase-orders')) ?>"><i class="fa-solid fa-file-invoice"></i><span>Purchase Orders</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Analytics -->
    <?php if (can('report') || in_array($_SESSION['role'] ?? '', ['admin','manager'])): ?>
    <div class="group-label<?= $sb_open('analytics') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="analytics"><span>Analytics</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('analytics') ? '' : ' collapsed' ?>" data-gr="analytics">
      <?php if (can('report')): ?>
      <a class="nav-link<?= $sb_act('report.php') ?>" href="<?= e(root_url('report.php')) ?>"><i class="fa-solid fa-chart-simple"></i><span>Daily Report</span></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Staff -->
    <div class="group-label<?= $sb_open('staff') ? ' open' : '' ?>" onclick="sbToggleGroup(this)" data-group="staff"><span>Staff</span><i class="fa-solid fa-chevron-right nav-chev"></i></div>
    <div class="group-items<?= $sb_open('staff') ? '' : ' collapsed' ?>" data-gr="staff">
      <?php if (can('employees')): ?>
      <a class="nav-link<?= $sb_act('employees') ?>" href="<?= e(url('employees')) ?>"><i class="fa-solid fa-user-tie"></i><span>Employees</span></a>
      <?php endif; ?>
      <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
      <a class="nav-link<?= $sb_act('roles') ?>" href="<?= e(url('roles')) ?>"><i class="fa-solid fa-shield-halved"></i><span>Manage Roles</span></a>
      <?php endif; ?>
      <?php if (can('reset_password')): ?>
      <a class="nav-link<?= $sb_act('admins') ?>" href="<?= e(url('admins')) ?>"><i class="fa-solid fa-key"></i><span>Reset Password</span></a>
      <?php endif; ?>
      <?php if (can('announcements')): ?>
      <a class="nav-link<?= $sb_act('announcements') ?>" href="<?= e(url('announcements')) ?>"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span><?php if ($sb_unread_ann > 0): ?><span class="badge-pill badge-red"><?= $sb_unread_ann ?></span><?php endif; ?></a>
      <?php endif; ?>
      <?php if (can('attendance')): ?>
      <a class="nav-link<?= $sb_act('attendance') ?>" href="<?= e(url('attendance')) ?>"><i class="fa-solid fa-fingerprint"></i><span>Attendance</span></a>
      <?php endif; ?>
      <?php if (can('promotions')): ?>
      <a class="nav-link<?= $sb_act('settings') ?>" href="<?= e(url('settings')) ?>"><i class="fa-solid fa-sliders"></i><span>Promotions</span></a>
      <?php endif; ?>
      <?php if (can('my_profile')): ?>
      <a class="nav-link<?= $sb_act('profile.php') ?>" href="<?= e(root_url('profile.php')) ?>"><i class="fa-solid fa-circle-user"></i><span>My Profile</span></a>
      <?php endif; ?>
    </div>

  </nav>

  <!-- Footer -->
  <div class="border-t border-white/10 px-3 py-2.5">
    <div class="mb-2 flex gap-1.5">
      <div class="flex flex-1 items-center gap-1.5 rounded-lg bg-white/5 px-2.5 py-1.5 text-[10px] text-slate-400"><i class="fa-solid fa-dollar-sign text-amber-500 text-[9px]"></i><span class="truncate">$<?= number_format((float)$sb_sales, 2) ?></span></div>
      <div class="flex flex-1 items-center gap-1.5 rounded-lg bg-white/5 px-2.5 py-1.5 text-[10px] text-slate-400"><i class="fa-solid fa-receipt text-amber-500 text-[9px]"></i><span class="truncate"><?= (int)$sb_total_orders ?> orders</span></div>
    </div>
    <a href="<?= e(root_url('logout.php')) ?>" class="nav-link !text-red-400 !border-transparent hover:!bg-red-500/20 hover:!text-red-300"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
  </div>

</aside>

<script>
/* Collapsible groups */
function sbToggleGroup(label) {
  var items = label.nextElementSibling;
  if (!items || !items.classList.contains('group-items')) return;
  var open = label.classList.contains('open');
  label.classList.toggle('open', !open);
  items.classList.toggle('collapsed', open);
  var g = label.dataset.group;
  if (g) localStorage.setItem('nav_' + g, open ? '0' : '1');
}
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('#appSidebar .group-label[data-group]').forEach(function(l) {
    var s = localStorage.getItem('nav_' + l.dataset.group);
    if (s === null) return;
    var items = l.nextElementSibling;
    if (!items) return;
    l.classList.toggle('open', s === '1');
    items.classList.toggle('collapsed', s !== '1');
  });
});

/* Mobile toggle */
function sbToggle() {
  var sb = document.getElementById('appSidebar');
  var ov = document.querySelector('.sb-overlay');
  if (!sb || !ov) return;
  sb.classList.toggle('open');
  ov.classList.toggle('active');
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    var sb = document.getElementById('appSidebar');
    var ov = document.querySelector('.sb-overlay');
    if (sb) sb.classList.remove('open');
    if (ov) ov.classList.remove('active');
  }
});
window.addEventListener('resize', function() {
  if (window.innerWidth > 1023) {
    var sb = document.getElementById('appSidebar');
    var ov = document.querySelector('.sb-overlay');
    if (sb) sb.classList.remove('open');
    if (ov) ov.classList.remove('active');
  }
});

/* Live clock */
(function tick(){
  var el = document.getElementById('sbClock');
  if (!el) return;
  var now = new Date(), h = now.getHours(), m = now.getMinutes();
  var ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  el.textContent = h + ':' + String(m).padStart(2,'0') + ' ' + ampm;
  setTimeout(tick, 1000);
})();
</script>
