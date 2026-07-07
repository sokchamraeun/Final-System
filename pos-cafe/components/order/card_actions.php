<?php
if (!isset($order) || !isset($canAdd) || !isset($is_cashier)) return;
$isPL = ($order['payment_method'] === 'paylater' && $order['status'] === 'Preparing');
?>
<?php if ($canAdd): ?>
<a href="add_to_existing_order.php?order_id=<?= $order['order_id'] ?>" class="btn btn-add">
    <i class="fa-solid fa-plus"></i> Add Items
</a>
<?php endif; ?>
<?php if ($isPL): ?>
<a href="edit_order_items.php?order_id=<?= $order['order_id'] ?>" class="btn btn-edit" title="Edit items on this order">
    <i class="fa-solid fa-pen-to-square"></i> Edit
</a>
<?php endif; ?>
<a href="admin_pay_cash.php?order_id=<?= $order['order_id'] ?>"
   class="btn btn-pay-cash"
   <?= $isPL ? 'data-lp-order="'.$order['order_id'].'" data-lp-dest="admin_pay_cash.php?order_id='.$order['order_id'].'" onclick="return interceptPayLater(event,this)"' : '' ?>>
    <i class="fa-solid fa-money-bill-wave"></i> Cash
</a>
<a href="admin_pay_bakong.php?order_id=<?= $order['order_id'] ?>"
   class="btn btn-pay-bakong"
   <?= $isPL ? 'data-lp-order="'.$order['order_id'].'" data-lp-dest="admin_pay_bakong.php?order_id='.$order['order_id'].'" onclick="return interceptPayLater(event,this)"' : '' ?>>
    <i class="fa-solid fa-qrcode"></i> Bakong
</a>
<a href="receipt_paylater.php?order_id=<?= $order['order_id'] ?>" target="_blank" class="btn btn-receipt">
    <i class="fa-solid fa-file-pdf"></i>
</a>
<?php if (!$is_cashier): ?>
<a href="<?= url('orders/board') ?>?highlight=<?= $order['order_id'] ?>" class="btn btn-view">
    <i class="fa-solid fa-eye"></i>
</a>
<?php if ($canAdd): ?>
<button class="btn btn-close" onclick="closeOrder(<?= $order['order_id'] ?>, this)" title="Mark as closed (no more additions)">
    <i class="fa-solid fa-lock"></i>
</button>
<?php endif; ?>
<button class="btn btn-cancel-order" onclick="cancelOrderFromFind(<?= $order['order_id'] ?>, this)" title="Cancel this order">
    <i class="fa-solid fa-ban"></i>
</button>
<?php endif; ?>
