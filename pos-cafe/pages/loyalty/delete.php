<?php
declare(strict_types=1);
/* Loyalty — delete (POST only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    flash('Invalid request.', 'error');
    redirect(url('pages/loyalty/index.php'));
}

$id = (int) input('id', 0);
if ($id > 0) {
    (new Loyalty())->delete($id);
    flash('Loyalty card deleted.', 'success');
} else {
    flash('Nothing to delete.', 'warning');
}

redirect(url('pages/loyalty/index.php'));
