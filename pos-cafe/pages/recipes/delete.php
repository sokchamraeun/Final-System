<?php
declare(strict_types=1);
/* Recipes — delete (POST only). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    flash('Invalid request.', 'error');
    redirect(url('pages/recipes/index.php'));
}

$id = (int) input('id', 0);
if ($id > 0) {
    (new Recipe())->delete($id);
    flash('Ingredient removed from recipe.', 'success');
} else {
    flash('Nothing to delete.', 'warning');
}

redirect(url('pages/recipes/index.php'));
