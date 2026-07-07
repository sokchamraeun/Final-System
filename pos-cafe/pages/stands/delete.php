<?php
declare(strict_types=1);
/* Stands — free a stand (POST only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    flash('Invalid request.', 'error');
    redirect(url('pages/stands/index.php'));
}

$id = (int) input('id', 0);
if ($id > 0) {
    (new Stand())->free($id);
    flash('Stand #' . $id . ' freed.', 'success');
} else {
    flash('Nothing to free.', 'warning');
}

redirect(url('pages/stands/index.php'));
