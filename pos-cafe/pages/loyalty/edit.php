<?php
declare(strict_types=1);
/* Loyalty — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'loyalty';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'loyalty';
$model     = new Loyalty();

$id = (int) input('id', 0);
$card = $model->find($id);
if (!$card) {
    flash('Loyalty card not found.', 'error');
    redirect(url('pages/loyalty/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/loyalty/edit.php?id=' . $id));
    }
    $card = array_merge($card, $_POST);
    $v = new Validator($_POST);
    $v->required('loyalty_id')->max('loyalty_id', 50);

    if ($v->passes()) {
        $model->update($id, [
            'loyalty_id'   => input('loyalty_id'),
            'points'       => (int) input('points', 0),
            'total_orders' => (int) input('total_orders', 0),
            'total_drinks' => (int) input('total_drinks', 0),
            'last_used'    => input('last_used'),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ]);
        flash('Loyalty card updated.', 'success');
        redirect(url('pages/loyalty/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit · ' . ($card['loyalty_id'] ?? 'Loyalty Card');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Loyalty Card', 'crumbs' => ['Sales', 'Loyalty', 'Edit']]);
component('loyalty/loyalty-form', [
    'loyalty' => $card,
    'errors'  => $errors,
    'action'  => url('pages/loyalty/edit.php?id=' . $id),
]);
component('layout/footer');
