<?php
declare(strict_types=1);
/* Roles — delete (POST only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_roles';
require POS_ROOT . '/middleware/permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    flash('Invalid request.', 'error');
    redirect(url('pages/roles/index.php'));
}

$id = (int) input('id', 0);
if ($id > 0) {
    (new Role())->delete($id);
    flash('Role deleted.', 'success');
} else {
    flash('Nothing to delete.', 'warning');
}

redirect(url('pages/roles/index.php'));
