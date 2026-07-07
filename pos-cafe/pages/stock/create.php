<?php
declare(strict_types=1);
/* Stock Counts â€” create (start a new count). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stock';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/stock/create.php'));
    }

    $v = new Validator($_POST);
    $v->required('business_date');

    if ($v->passes()) {
        $countId = (new StockCount())->startCount(
            (string) input('business_date'),
            Auth::id(),
            (string) input('notes')
        );
        flash('Stock count #' . $countId . ' started.', 'success');
        redirect(url('pages/stock/edit.php?id=' . $countId));
    }
    $errors = $v->errors();
}

$pageTitle  = 'New Stock Count';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Stock Count', 'crumbs' => ['Inventory', 'Stock Counts', 'New']]);
component('stock/stock-form', [
    'action' => url('pages/stock/create.php'),
    'errors' => $errors,
]);
component('layout/footer');
