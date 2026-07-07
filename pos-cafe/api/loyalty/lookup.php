<?php
declare(strict_types=1);
/* API: loyalty lookup (from loyalty_lookup.php).
   Sets $_SESSION['loyalty_card_id'] so confirm_order.php can award points. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('loyalty')) {
    json_response(['found' => false, 'message' => 'Unauthorized'], 403);
}

$loyaltyId = (string) input('loyalty_id', '');
if ($loyaltyId === '') {
    json_response(['found' => false, 'message' => 'No loyalty ID provided']);
}

$l    = new Loyalty();
$card = $l->findByLoyaltyId($loyaltyId);

if (!$card || (int) ($card['is_active'] ?? 0) !== 1) {
    unset($_SESSION['loyalty_card_id']);
    json_response(['found' => false, 'message' => 'Card not found']);
}

$_SESSION['loyalty_card_id'] = (int) $card['card_id'];

json_response([
    'found'        => true,
    'loyalty_id'   => $card['loyalty_id'],
    'points'       => (int) $card['points'],
    'total_orders' => (int) $card['total_orders'],
    'total_drinks' => (int) $card['total_drinks'],
    'rewards'      => $l->rewards(),
    'history'      => $l->historyFor((int) $card['card_id'], 1, 5),
]);
