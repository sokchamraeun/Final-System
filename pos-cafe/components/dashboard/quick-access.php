<?php
declare(strict_types=1);
/* ── Expects: $low_stock, $low_recipe_count, $unpaid_count, $paylater_count, $_unread_ann ── */
$_redesign = in_array($_SESSION['role'] ?? '', ['staff', 'inventory_clerk'], true);
$G = $_redesign ? 'qx' : 'qa';
$cardClass   = fn() => $G . '-tile';
$gridClass   = fn() => $G . '-tiles';
$groupClass  = fn() => $G . '-group';
$groupLabel  = fn() => $G . '-group-label';
?>
<div class="<?= $gridClass() ?> fu" style="animation-delay:.1s">
  <?php if (can('find_orders')): ?>
  <a href="<?= e(url('menu')) ?>" class="<?= $_redesign ? 'qx-hero' : 'qa-hero-btn' ?>">
    <i class="fa-solid fa-plus"></i> <span>Take New Order</span>
  </a>
  <?php endif; ?>

  <?php if (can('view_orders') || can('find_orders')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-receipt"></i> Orders</div>
    <div class="<?= $gridClass() ?>">
      <?php if (can('view_orders')): ?>
      <a href="<?= e(url('orders/board')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-receipt"></i> <span>Orders</span>
      </a>
      <?php endif; ?>
      <?php if (can('find_orders')): ?>
      <a href="<?= e(root_url('find_order.php')) ?>" class="<?= $cardClass() ?>">
        <?php if ($_SESSION['role'] === 'staff' && $paylater_count > 0): ?>
        <span class="qx-tile-badge" style="background:var(--purple);"><?= $paylater_count ?></span>
        <?php elseif ($unpaid_count > 0): ?>
        <span class="qx-tile-badge"><?= $unpaid_count ?></span>
        <?php endif; ?>
        <i class="fa-solid fa-magnifying-glass"></i> <span>Find Order</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('products') || can('ingredients') || can('recipes')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-boxes-stacked"></i> Inventory</div>
    <div class="<?= $gridClass() ?>">
      <?php if (can('products')): ?>
      <a href="<?= e(url('products')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-cube"></i> <span>Products</span>
      </a>
      <?php endif; ?>
      <?php if (can('ingredients')): ?>
      <a href="<?= e(url('inventory')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-flask"></i> <span>Ingredients</span>
      </a>
      <?php endif; ?>
      <?php if (can('recipes')): ?>
      <a href="<?= e(url('recipes')) ?>" class="<?= $cardClass() ?>">
        <?php if ($low_recipe_count > 0): ?>
        <span class="<?= $cardClass() ?>-badge" title="<?= $low_recipe_count ?> recipe<?= $low_recipe_count == 1 ? '' : 's' ?> low on ingredients"><?= $low_recipe_count ?></span>
        <?php endif; ?>
        <i class="fa-solid fa-utensils"></i> <span>Drink Recipe</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('suppliers') || can('purchase_orders')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-truck-ramp-box"></i> Procurement</div>
    <div class="<?= $gridClass() ?>">
      <?php if (can('suppliers')): ?>
      <a href="<?= e(url('suppliers')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-truck-ramp-box"></i> <span>Suppliers</span>
      </a>
      <?php endif; ?>
      <?php if (can('purchase_orders')): ?>
      <a href="<?= e(url('purchase-orders')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-file-invoice"></i> <span>Purchase Orders</span>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('loyalty')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-star"></i> Loyalty</div>
    <div class="<?= $gridClass() ?>">
      <a href="<?= e(url('loyalty')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-star"></i> <span>Loyalty</span>
      </a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('employees') || can('attendance') || can('announcements')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-users"></i> Staff</div>
    <div class="<?= $gridClass() ?>">
      <?php if (can('employees')): ?>
      <a href="<?= e(url('employees')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-user-tie"></i> <span>Employees</span>
      </a>
      <?php endif; ?>
      <?php if (can('attendance')): ?>
      <a href="<?= e(url('attendance')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-fingerprint"></i> <span>Attendance</span>
      </a>
      <?php endif; ?>
      <?php if (can('announcements')): ?>
      <a href="<?= e(url('announcements')) ?>" class="<?= $cardClass() ?>" style="position:relative">
        <i class="fa-solid fa-bullhorn"></i> <span>Announcements</span>
        <?php if ($_unread_ann > 0): ?>
        <span style="position:absolute;top:8px;right:8px;background:var(--red);color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px;line-height:1"><?= $_unread_ann ?></span>
        <?php endif; ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('report')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-chart-simple"></i> Analytics</div>
    <div class="<?= $gridClass() ?>">
      <a href="<?= e(root_url('report.php')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-chart-simple"></i> <span>Daily Report</span>
      </a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (can('my_profile')): ?>
  <div class="<?= $groupClass() ?>">
    <div class="<?= $groupLabel() ?>"><i class="fa-solid fa-circle-user"></i> Account</div>
    <div class="<?= $gridClass() ?>">
      <a href="<?= e(root_url('profile.php')) ?>" class="<?= $cardClass() ?>">
        <i class="fa-solid fa-circle-user"></i> <span>My Profile</span>
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>
