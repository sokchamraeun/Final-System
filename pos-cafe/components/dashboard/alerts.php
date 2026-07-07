<?php
declare(strict_types=1);
/* ── Expects: $low_stock, $_is_mgr, $unpaid_count ── */
$showAlerts = ($low_stock > 0 && can('ingredients')) || ($_is_mgr && $unpaid_count > 0 && can('find_orders'));
if (!$showAlerts) return;
?>
<div class="alert-strip fu" style="animation-delay:.06s">
  <?php if ($low_stock > 0 && can('ingredients')): ?>
  <a href="<?= e(url('inventory')) ?>" class="alert-pill danger">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <?= $low_stock ?> item<?= $low_stock != 1 ? 's' : '' ?> low on stock — restock needed
  </a>
  <?php endif; ?>
  <?php if ($_is_mgr && $unpaid_count > 0 && can('find_orders')): ?>
  <a href="<?= e(root_url('find_order.php')) ?>" class="alert-pill warning">
    <i class="fa-solid fa-clock"></i>
    <?= $unpaid_count ?> unpaid order<?= $unpaid_count != 1 ? 's' : '' ?> pending
  </a>
  <?php endif; ?>
</div>
