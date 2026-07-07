<?php
declare(strict_types=1);
/* API: loyalty redeem (from loyalty_redeem.php).
   DISPLAY-ONLY deduction: the reward is stored in the session and the actual
   DB point deduction happens when the order is confirmed (confirm_order.php),
   so an abandoned cart never permanently loses the customer's points. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('loyalty')) {
    json_response(['success' => false, 'message' => 'Please login first'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Invalid request'], 400);
}

$loyaltyId  = (string) input('loyalty_id', '');
$rewardName = (string) input('reward_name', '');
if ($loyaltyId === '' || $rewardName === '') {
    json_response(['success' => false, 'message' => 'Missing required fields']);
}

$l    = new Loyalty();
$card = $l->findByLoyaltyId($loyaltyId);
if (!$card || (int) ($card['is_active'] ?? 0) !== 1) {
    json_response(['success' => false, 'message' => 'Card not found']);
}

$reward = $l->findReward($rewardName);
if (!$reward) {
    json_response(['success' => false, 'message' => 'Reward not available']);
}
if ((int) $card['points'] < (int) $reward['points_required']) {
    json_response(['success' => false, 'message' => 'Not enough points']);
}

/* Account for redemptions already pending in this session. */
$pending = 0;
foreach ($_SESSION['redeemed_rewards'] ?? [] as $p) {
    if ((int) ($p['card_id_int'] ?? 0) === (int) $card['card_id']) {
        $pending += (int) ($p['points_required'] ?? 0);
    }
}
$effective = (int) $card['points'] - $pending;
if ($effective < (int) $reward['points_required']) {
    json_response(['success' => false, 'message' => 'Not enough points (accounting for pending redemptions)']);
}

$_SESSION['redeemed_rewards'][] = [
    'reward_name'     => $reward['reward_name'],
    'points_required' => (int) $reward['points_required'],
    'card_id'         => $card['loyalty_id'],   // display
    'card_id_int'     => (int) $card['card_id'], // DB id for later deduction
];

json_response([
    'success'         => true,
    'message'         => $reward['reward_name'] . ' added — will be applied when the order is confirmed.',
    'new_points'      => $effective - (int) $reward['points_required'],
    'reward_name'     => $reward['reward_name'],
    'points_required' => (int) $reward['points_required'],
    'rewards'         => $l->rewards(),
]);
