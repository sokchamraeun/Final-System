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
    $profilePhoto     = Auth::photo();
    $profileInitials  = Auth::initials();
} else {
    $profileName      = $admin_name   ?? ($_SESSION['username']  ?? 'User');
    $profileRoleName  = $_cur_role_name  ?? '';
    $profileRoleColor = $_cur_role_color ?? '#d1904b';
    $profilePhoto     = null;
    if ($conn && !empty($_SESSION['user_id'])) {
        $r = $conn->query("SELECT photo FROM employees WHERE user_id = " . (int)$_SESSION['user_id'] . " AND photo IS NOT NULL AND photo != '' LIMIT 1");
        if ($r && $row = $r->fetch_assoc()) $profilePhoto = $row['photo'];
    }
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
$sb_act   = fn($href) => $sb_cur === $href ? ' active' : '';
$_can_stands = in_array($_SESSION['role'] ?? '', ['admin', 'manager', 'staff'], true);
?>
<aside id="appSidebar"
       class="fixed inset-y-0 left-0 z-40 flex w-[260px] -translate-x-full flex-col bg-black text-slate-300 transition-[transform,width] duration-300 max-h-screen lg:translate-x-0"
       style="font-family:'Poppins',sans-serif">

  <style type="text/tailwindcss">
    :root{--sb-w:260px}
    @media(min-width:1024px){
      .vo-main-col{ margin-left:260px; transition:margin-left .3s; }
      .back-btn{ left:calc(22px + var(--sb-w))!important; transition:left .3s; }
      .sc-bar{ left:calc(20px + var(--sb-w))!important; transition:left .3s; }
    }
    [data-theme="light"] #appSidebar{ @apply !bg-white border-r border-slate-200; }
    .sb-scroll::-webkit-scrollbar{display:none}
    .sb-scroll{scrollbar-width:none}

    @layer components {
      /* Chunkier nav items, plain dark pill on active state (screenshot style) */
      #appSidebar .nav-link{ @apply flex items-center gap-3 rounded px-3.5 py-2.5 mb-1 text-sm font-semibold text-neutral-400 border border-transparent relative no-underline transition-all; }
      #appSidebar .nav-link:hover{ @apply bg-teal-500/10 text-teal-300 translate-x-0.5; }
      #appSidebar .nav-link:hover i{ @apply text-teal-300; }
      #appSidebar .nav-link:focus{ @apply outline-none bg-teal-500 text-teal-100 scale-[.98]; box-shadow: inset 0 2px 5px rgba(0,0,0,.45), inset 0 -1px 0 rgba(255,255,255,.06); }
      #appSidebar .nav-link:focus-visible{ @apply ring-2 ring-teal-500/60; }
      #appSidebar .nav-link:active{ @apply bg-teal-500/20 text-teal-200 scale-[.97]; box-shadow: inset 0 3px 6px rgba(0,0,0,.55), inset 0 -1px 0 rgba(255,255,255,.05); }
      #appSidebar .nav-link.active{ @apply bg-teal-500 text-white font-bold; }
      #appSidebar .nav-link.active i{ @apply text-white; }
      #appSidebar .nav-link.active:hover{ @apply bg-teal-600 text-white; }
      #appSidebar .nav-link.active:hover i{ @apply text-white; }
      #appSidebar .nav-link i{ @apply w-[18px] text-center text-[15px] shrink-0 transition-colors; }

      /* Quick-order CTA — behaves like a nav-link (hover/focus/press background
         changes) while it's not the current page; once active it flattens to
         the same solid teal used by every other current-page nav-link. */
      #appSidebar .cta-link{ @apply mx-3 mb-2 flex items-center gap-3 rounded-sm px-4 py-3 text-[14px] font-bold text-white no-underline transition-all; background: none; box-shadow: none; }
      #appSidebar .cta-link:not(.active):hover{ @apply -translate-y-0.5; background: linear-gradient(135deg,#0f9c8f,#2dd4bf); box-shadow: 0 8px 22px -4px rgba(20,184,166,.6); }
      #appSidebar .cta-link:not(.active):focus{ @apply outline-none scale-[.98]; background: linear-gradient(135deg,#0b8074,#0d9488); box-shadow: inset 0 2px 5px rgba(0,0,0,.35); }
      #appSidebar .cta-link:not(.active):active{ @apply scale-[.97]; background: linear-gradient(135deg,#086b61,#0b8074); box-shadow: inset 0 3px 6px rgba(0,0,0,.45); }
      #appSidebar .cta-link.active{ @apply bg-teal-500 shadow-none; }
      #appSidebar .cta-link.active:hover{ @apply bg-teal-600; }

      #appSidebar .badge-pill{ @apply ml-auto shrink-0 bg-[#d1904b] text-black text-[9.5px] font-extrabold px-[7px] py-px rounded-full min-w-[18px] text-center; }
      #appSidebar .badge-red{ @apply !bg-red-500 !text-white; }
      #appSidebar .badge-purple{ @apply !bg-purple-500 !text-white; }
      /* Active pill uses a stronger red badge, matching the reference screenshot */
      #appSidebar .nav-link.active .badge-pill{ @apply !bg-red-500 !text-white shadow-[0_2px_6px_rgba(255,59,59,.5)]; }
    }

    [data-theme="light"] #appSidebar .nav-link{ @apply text-slate-600; }
    [data-theme="light"] #appSidebar .nav-link:hover{ @apply text-slate-900; }
    [data-theme="light"] #appSidebar .nav-link.active{ @apply text-slate-900; }

    /* ── Collapsed (icon-rail) desktop mode ── */
    @layer components {
      #appSidebar.mini{ @apply w-[76px]; }
      #appSidebar.mini .sb-label,
      #appSidebar.mini .nav-link span,
      #appSidebar.mini .badge-pill,
      #appSidebar.mini .footer-stat span,
      #appSidebar.mini #sbClock{ @apply !hidden; }
      #appSidebar.mini .nav-link{ @apply justify-center px-2.5; }
      #appSidebar.mini .nav-link i{ @apply w-auto; }
      #appSidebar.mini .sb-collapse i{ @apply rotate-180; }
      #appSidebar.mini .profile-row{ @apply justify-center; }
    }
  </style>

  <button onclick="sbToggle()" class="fixed left-3.5 top-3.5 z-[200] flex h-10 w-10 items-center justify-center rounded-md border border-neutral-800 bg-neutral-900 text-[15px] text-neutral-100 lg:hidden"><i class="fa-solid fa-bars"></i></button>
  <div class="sb-overlay fixed inset-0 z-[90] hidden bg-black/70 backdrop-blur-sm" onclick="sbToggle()"></div>

  <!-- Profile -->
  <div class="profile-row flex items-center gap-3 border-b border-white/10 px-4 py-4">
    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full ring-2 ring-white/10" style="background:<?= e($profileRoleColor) ?>">
      <?php if ($profilePhoto): ?>
        <img src="<?= e(root_url($profilePhoto)) ?>" alt="" class="h-full w-full object-cover">
      <?php else: ?>
        <div class="grid h-full w-full place-items-center text-sm font-bold text-white"><?= e($profileInitials) ?></div>
      <?php endif; ?>
    </div>
    <div class="sb-label min-w-0 flex-1">
      <div class="truncate text-sm font-bold text-white"><?= e($profileName) ?></div>
      <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide" style="color:<?= e($profileRoleColor) ?>"><?= e($profileRoleName) ?></div>
    </div>
    <button type="button" class="sb-collapse hidden shrink-0 rounded-lg p-1.5 text-slate-400 transition hover:bg-white/10 hover:text-white lg:grid lg:place-items-center" onclick="sbCollapse()" title="Collapse sidebar">
      <i class="fa-solid fa-angles-left text-xs transition-transform duration-300"></i>
    </button>
  </div>

  <!-- Logo -->
  <div class="flex items-center gap-3 px-4 py-3">
    <div class="grid h-10 w-10 shrink-0 place-items-center rounded bg-amber-600 text-white shadow-lg"><i class="fa-solid fa-mug-hot text-sm"></i></div>
    <div class="sb-label min-w-0">
      <div class="truncate text-sm font-bold text-white"><?= e(APP_NAME) ?></div>
      <div class="text-[10px] text-slate-400">Café Management</div>
    </div>
    <button onclick="sbToggle()" class="ml-auto grid h-7 w-7 shrink-0 place-items-center rounded-lg text-slate-400 hover:bg-white/10 lg:hidden"><i class="fa-solid fa-xmark text-xs"></i></button>
  </div>

  <!-- Quick order -->
  <?php if (can('find_orders')): ?>
  <a href="<?= e(url('menu')) ?>" class="cta-link<?= $sb_act('menu') ?>"><i class="fa-solid fa-cart-shopping"></i> <span class="sb-label">Take New Order</span></a>
  <?php endif; ?>

  <!-- Nav -->
  <nav class="sb-scroll flex-1 overflow-y-auto px-3 py-1">

    <?php if (can('dashboard')): ?>
      <a class="nav-link<?= $sb_act('dashboard') ?>" href="<?= e(url('dashboard')) ?>"><i class="fa-solid fa-chart-pie"></i><span>Dashboard</span><?php if ($sb_pending + $sb_preparing > 0): ?><span class="badge-pill"><?= $sb_pending + $sb_preparing ?></span><?php endif; ?></a>
    <?php endif; ?>

    <?php if (can('find_orders')): ?>
      <a class="nav-link<?= $sb_act('find_order.php') ?>" href="<?= e(root_url('find_order.php')) ?>"><i class="fa-solid fa-magnifying-glass"></i><span>Find Unpaid Orders</span><?php if ($sb_paylater > 0): ?><span class="badge-pill badge-purple"><?= $sb_paylater ?></span><?php elseif ($sb_unpaid > 0): ?><span class="badge-pill badge-purple"><?= $sb_unpaid ?></span><?php endif; ?></a>
    <?php endif; ?>
    <?php if (can('view_orders')): ?>
      <a class="nav-link<?= $sb_act('orders-board') ?>" href="<?= e(url('orders/board')) ?>"><i class="fa-solid fa-receipt"></i><span>Orders</span></a>
    <?php endif; ?>
    <?php if (can('find_orders')): ?>
      <a class="nav-link<?= $sb_act('orders') ?>" href="<?= e(url('orders')) ?>"><i class="fa-solid fa-list"></i><span>All Orders</span></a>
    <?php endif; ?>

    <?php if (can('barista_station')): ?>
      <a class="nav-link<?= $sb_act('barista_display.php') ?>" href="<?= e(root_url('barista_display.php')) ?>"><i class="fa-solid fa-mug-hot"></i><span>Barista Station</span></a>
    <?php endif; ?>
    <?php if (can('customer_display')): ?>
      <a class="nav-link<?= $sb_act('customer_display.php') ?>" href="<?= e(root_url('customer_display.php')) ?>"><i class="fa-solid fa-display"></i><span>Customer Display</span></a>
    <?php endif; ?>
    <?php if ($_can_stands): ?>
      <a class="nav-link<?= $sb_act('stands') ?>" href="<?= e(url('stands')) ?>"><i class="fa-solid fa-table-cells-large"></i><span>Stand Numbers</span></a>
    <?php endif; ?>

    <?php if (can('loyalty')): ?>
      <a class="nav-link<?= $sb_act('loyalty') ?>" href="<?= e(url('loyalty')) ?>"><i class="fa-solid fa-star"></i><span>Loyalty Card</span></a>
    <?php endif; ?>

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
    <?php if (can('addons')): ?>
      <a class="nav-link<?= $sb_act('addons') ?>" href="<?= e(url('addons')) ?>"><i class="fa-solid fa-layer-group"></i><span>Addons</span></a>
      <a class="nav-link<?= $sb_act('addon-ingredients') ?>" href="<?= e(url('addon-ingredients')) ?>"><i class="fa-solid fa-flask"></i><span>Addon Ingredients</span></a>
    <?php endif; ?>

    <?php if (can('cash_reconciliation')): ?>
      <a class="nav-link<?= $sb_act('reconciliation_report.php') ?>" href="<?= e(root_url('reconciliation_report.php')) ?>"><i class="fa-solid fa-cash-register"></i><span>Cash Count</span><?php if ($sb_recon_alerts > 0): ?><span class="badge-pill badge-red"><?= $sb_recon_alerts ?></span><?php endif; ?></a>
    <?php endif; ?>
    <?php if (can('stock_count')): ?>
      <a class="nav-link<?= $sb_act('stock') ?>" href="<?= e(url('stock')) ?>"><i class="fa-solid fa-clipboard-list"></i><span>Stock Count</span></a>
    <?php endif; ?>

    <?php if (can('suppliers')): ?>
      <a class="nav-link<?= $sb_act('suppliers') ?>" href="<?= e(url('suppliers')) ?>"><i class="fa-solid fa-truck-ramp-box"></i><span>Suppliers</span></a>
    <?php endif; ?>
    <?php if (can('purchase_orders')): ?>
      <a class="nav-link<?= $sb_act('purchase-orders') ?>" href="<?= e(url('purchase-orders')) ?>"><i class="fa-solid fa-file-invoice"></i><span>Purchase Orders</span></a>
    <?php endif; ?>

    <?php if (can('report')): ?>
      <a class="nav-link<?= $sb_act('report.php') ?>" href="<?= e(root_url('report.php')) ?>"><i class="fa-solid fa-chart-simple"></i><span>Daily Report</span></a>
    <?php endif; ?>

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
      <a class="nav-link<?= $sb_act('profile.php') ?>" href="<?= e(url('profile')) ?>"><i class="fa-solid fa-circle-user"></i><span>My Profile</span></a>
    <?php endif; ?>

  </nav>

  <!-- Footer -->
  <div class="border-t border-white/10 px-3 py-2.5">
    <div class="mb-2 flex gap-1.5">
      <div class="footer-stat flex flex-1 items-center gap-1.5 rounded-lg bg-white/5 px-2.5 py-1.5 text-[10px] text-slate-400"><i class="fa-solid fa-dollar-sign text-amber-500 text-[9px]"></i><span class="truncate">$<?= number_format((float)$sb_sales, 2) ?></span></div>
      <div class="footer-stat flex flex-1 items-center gap-1.5 rounded-lg bg-white/5 px-2.5 py-1.5 text-[10px] text-slate-400"><i class="fa-solid fa-receipt text-amber-500 text-[9px]"></i><span class="truncate"><?= (int)$sb_total_orders ?> orders</span></div>
    </div>
    <a href="<?= e(root_url('logout.php')) ?>" class="nav-link !text-red-400 !border-transparent hover:!bg-red-500/20 hover:!text-red-300"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
  </div>

</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var sb = document.getElementById('appSidebar');
  if (sb && localStorage.getItem('sb_mini') === '1') {
    sb.classList.add('mini');
    document.documentElement.style.setProperty('--sb-w', '76px');
  }

  /* Keep the nav scrolled to where it was (or to the active link) instead
     of jumping back to the top on every page load / navigation. */
  var navScroll = document.querySelector('#appSidebar .sb-scroll');
  if (navScroll) {
    var savedScroll = sessionStorage.getItem('sb_scroll');
    if (savedScroll !== null) {
      navScroll.scrollTop = parseInt(savedScroll, 10) || 0;
    } else {
      var activeLink = navScroll.querySelector('.nav-link.active');
      if (activeLink) activeLink.scrollIntoView({ block: 'center' });
    }
    navScroll.addEventListener('scroll', function() {
      sessionStorage.setItem('sb_scroll', String(navScroll.scrollTop));
    });
    navScroll.querySelectorAll('.nav-link').forEach(function(link) {
      link.addEventListener('click', function() {
        sessionStorage.setItem('sb_scroll', String(navScroll.scrollTop));
      });
    });
  }
});

/* Mobile toggle */
function sbToggle() {
  var sb = document.getElementById('appSidebar');
  var ov = document.querySelector('.sb-overlay');
  if (!sb || !ov) return;
  sb.classList.toggle('-translate-x-full');
  sb.classList.toggle('translate-x-0');
  ov.classList.toggle('hidden');
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    var sb = document.getElementById('appSidebar');
    var ov = document.querySelector('.sb-overlay');
    if (sb) { sb.classList.add('-translate-x-full'); sb.classList.remove('translate-x-0'); }
    if (ov) ov.classList.add('hidden');
  }
});
window.addEventListener('resize', function() {
  if (window.innerWidth > 1023) {
    var sb = document.getElementById('appSidebar');
    var ov = document.querySelector('.sb-overlay');
    if (sb) { sb.classList.add('-translate-x-full'); sb.classList.remove('translate-x-0'); }
    if (ov) ov.classList.add('hidden');
  }
});

/* Desktop icon-rail collapse toggle */
function sbCollapse() {
  var sb = document.getElementById('appSidebar');
  if (!sb) return;
  var mini = sb.classList.toggle('mini');
  document.documentElement.style.setProperty('--sb-w', mini ? '76px' : '260px');
  localStorage.setItem('sb_mini', mini ? '1' : '0');
}

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