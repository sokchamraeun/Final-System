<?php
$isPaidOpen = ($order['status'] === 'Paid' && $order['is_open'] == 1);
$isPayLater = ($order['payment_method'] === 'paylater');
$canAdd     = ($order['is_open'] == 1 && (in_array($order['status'], ['Preparing', 'Paid']) || ($isPayLater && $order['status'] === 'Completed')));
$cardClass  = $isPaidOpen ? 'is-paid-open' : ($canAdd ? 'can-add' : '');
$statusClass = strtolower($order['status']);
$tz   = new DateTimeZone('Asia/Phnom_Penh');
$now  = new DateTime('now', $tz);
$then = new DateTime($order['order_date'], $tz);
$diff = $now->getTimestamp() - $then->getTimestamp();
if ($diff < 0) {
    $absDiff = abs($diff);
    if ($absDiff < 3600)       $timeAgo = 'in ' . floor($absDiff/60) . 'm';
    elseif ($absDiff < 86400)  $timeAgo = 'in ' . floor($absDiff/3600) . 'h';
    else                       $timeAgo = 'in ' . floor($absDiff/86400) . 'd';
} elseif ($diff < 60)          $timeAgo = $diff . 's ago';
elseif ($diff < 3600)          $timeAgo = floor($diff/60) . 'm ago';
elseif ($diff < 86400)         $timeAgo = floor($diff/3600) . 'h ' . floor(($diff%3600)/60) . 'm ago';
else                           $timeAgo = floor($diff/86400) . 'd ago';

$isOverdue = ($order['payment_method'] === 'paylater' && $diff > 1800);

$overdueMins = floor($diff / 60);
if ($overdueMins < 60) {
    $overdueLabel = $overdueMins . '+ min';
} elseif ($overdueMins < 1440) {
    $h = floor($overdueMins / 60);
    $overdueLabel = $h . '+ ' . ($h === 1 ? 'hour' : 'hours');
} else {
    $d = floor($overdueMins / 1440);
    $overdueLabel = $d . '+ ' . ($d === 1 ? 'day' : 'days');
}
?>
<div class="order-card <?= $cardClass ?> <?= $isOverdue ? 'overdue' : '' ?>"
     data-name="<?= strtolower(htmlspecialchars($order['customer_name'])) ?>"
     data-token="<?= $order['token_number'] ?>"
     data-amount="<?= $order['total'] ?>"
     data-order="<?= $order['daily_order_no'] ?>">

    <div class="card-top">
        <div class="card-main-info">
            <?php require __DIR__ . '/../../components/order/card_info.php'; ?>
        </div>
        <div class="actions">
            <?php require __DIR__ . '/../../components/order/card_actions.php'; ?>
        </div>
    </div>

    <?php require __DIR__ . '/../../components/order/card_meta.php'; ?>
</div>
