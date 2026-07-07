<?php
declare(strict_types=1);
/* Cart — Redirects to the legacy cart management page. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

redirect(root_url('cart.php'));
