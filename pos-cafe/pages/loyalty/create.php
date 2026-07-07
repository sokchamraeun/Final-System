<?php
declare(strict_types=1);
/* Loyalty — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';
$errors    = [];
$card      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/loyalty/create.php'));
    }
    $card = $_POST;
    $v = new Validator($_POST);
    $v->required('loyalty_id')->max('loyalty_id', 50);

    if ($v->passes()) {
        $id = (new Loyalty())->create([
            'loyalty_id'   => input('loyalty_id'),
            'points'       => (int) input('points', 0),
            'total_orders' => (int) input('total_orders', 0),
            'total_drinks' => (int) input('total_drinks', 0),
            'last_used'    => input('last_used'),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ]);
        flash('Loyalty card #' . $id . ' created.', 'success');
        redirect(url('pages/loyalty/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Loyalty Card';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Loyalty Card', 'crumbs' => ['Sales', 'Loyalty', 'New']]);
component('loyalty/loyalty-form', [
    'loyalty' => $card,
    'errors'  => $errors,
    'action'  => url('pages/loyalty/create.php'),
]);
component('layout/footer');
