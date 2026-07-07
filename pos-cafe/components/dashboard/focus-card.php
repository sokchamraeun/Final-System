<?php
declare(strict_types=1);
/* ── Role-aware focus card: shows the single most relevant task ── */
$_role   = $_SESSION['role'] ?? '';
$_focus  = null;

$_focus_barista = [
    'icon'  => 'fa-fire-burner',
    'count' => (int)$preparing_count,
    'label' => $preparing_count == 1 ? 'drink to prepare' : 'drinks to prepare',
    'sub'   => $preparing_count > 0 ? 'Orders are waiting in the queue' : 'All caught up — nothing in the queue',
    'href'  => 'barista_display.php',
    'cta'   => 'Open Barista Station',
    'color' => $preparing_count > 0 ? '#ff8a3d' : '#55c97e',
];
$_pending_total = (int)$unpaid_count + (int)$paylater_count;
$_focus_cashier = [
    'icon'  => 'fa-cash-register',
    'count' => $_pending_total,
    'label' => $_pending_total == 1 ? 'order awaiting payment' : 'orders awaiting payment',
    'sub'   => (int)$unpaid_count . ' unpaid · ' . (int)$paylater_count . ' pay-later',
    'href'  => 'find_order.php',
    'cta'   => 'Find Orders',
    'color' => $_pending_total > 0 ? '#9b59b6' : '#55c97e',
];
$_focus_inventory = [
    'icon'  => 'fa-triangle-exclamation',
    'count' => (int)$low_stock,
    'label' => $low_stock == 1 ? 'item low on stock' : 'items low on stock',
    'sub'   => $low_stock > 0 ? 'Restock needed soon' : 'Stock levels look healthy',
    'href'  => 'inventory',
    'cta'   => 'Review Stock',
    'color' => $low_stock > 0 ? '#ff6b6b' : '#55c97e',
];

if      ($_role === 'barista')        $_focus = $_focus_barista;
elseif ($_role === 'inventory_clerk') $_focus = $_focus_inventory;
elseif (can('find_orders'))           $_focus = $_focus_cashier;
elseif (can('ingredients') || can('products')) $_focus = $_focus_inventory;
elseif (can('barista_station'))       $_focus = $_focus_barista;

if (!$_focus) return;
?>
<a href="<?= e(str_contains($_focus['href'], '.php') ? root_url($_focus['href']) : url($_focus['href'])) ?>" class="focus-card fu" style="animation-delay:.06s;border-left-color:<?= $_focus['color'] ?>">
  <div class="focus-icon" style="width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:25px;color:<?= $_focus['color'] ?>;background:<?= $_focus['color'] ?>22;">
    <i class="fa-solid <?= $_focus['icon'] ?>"></i>
  </div>
  <div class="focus-body">
    <div class="focus-count" style="font-size:26px;font-weight:700;color:var(--text);">
      <?= (int)$_focus['count'] ?> <span style="font-size:14px;font-weight:500;color:var(--text-muted);"><?= e($_focus['label']) ?></span>
    </div>
    <div class="focus-sub"><?= e($_focus['sub']) ?></div>
  </div>
  <span class="focus-cta" style="color:<?= $_focus['color'] ?>">
    <?= e($_focus['cta']) ?> <i class="fa-solid fa-arrow-right"></i>
  </span>
</a>
