<?php
declare(strict_types=1);
/* Legacy redirect shim */
require_once __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$qs = ($q = $_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $q : '';
redirect(url('admins' . $qs));
