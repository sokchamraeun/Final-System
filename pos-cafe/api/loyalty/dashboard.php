<?php
declare(strict_types=1);
/* API: loyalty dashboard data (from loyalty_dashboard_data.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('loyalty')) {
    json_response(['error' => 'forbidden'], 403);
}

$l     = new Loyalty();
$stats = $l->stats();

json_response([
    'total_cards'  => $stats['total_cards'],
    'total_points' => $stats['total_points'],
    'top_card'     => $l->topCard(),
    'all_cards'    => $l->activeCards(),
    'history'      => $l->recentHistory(10),
    'rewards'      => $l->rewards(),
]);
