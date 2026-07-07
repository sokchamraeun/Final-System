<?php
declare(strict_types=1);
/* API: loyalty points adjustment (from loyalty_adjust_points.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('loyalty')) {
    json_response(['success' => false, 'message' => 'Not allowed'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Invalid request'], 400);
}

$cardId     = (int) input('card_id', 0);
$adjustment = (int) input('adjustment', 0);
$reason     = trim((string) input('reason', ''));
if ($reason === '') {
    $reason = 'Manual adjustment by admin';
}

$result = (new Loyalty())->adjustPoints($cardId, $adjustment, $reason);
json_response($result, $result['success'] ? 200 : 400);
