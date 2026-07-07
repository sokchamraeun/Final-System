<?php
declare(strict_types=1);
/* API: generate a unique loyalty ID (from loyalty_generate_id.php). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('loyalty')) {
    json_response(['error' => 'forbidden'], 403);
}

json_response(['loyalty_id' => (new Loyalty())->generateUniqueId()]);
