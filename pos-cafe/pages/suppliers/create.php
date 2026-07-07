<?php
declare(strict_types=1);
/* Suppliers â€” create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'suppliers';
$errors    = [];
$supplier  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/suppliers/create.php'));
    }
    $supplier = $_POST;
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->max('contact_person', 100)
      ->max('phone', 30)
      ->max('email', 100)
      ->max('address', 255)
      ->max('notes', 500);

    if ($v->passes()) {
        $id = (new Supplier())->create([
            'name'           => input('name'),
            'contact_person' => input('contact_person'),
            'phone'          => input('phone'),
            'email'          => input('email'),
            'address'        => input('address'),
            'notes'          => input('notes'),
        ]);
        flash('Supplier #' . $id . ' created.', 'success');
        redirect(url('pages/suppliers/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Supplier';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Supplier', 'crumbs' => ['Inventory', 'Suppliers', 'New']]);
component('suppliers/supplier-form', [
    'supplier' => $supplier,
    'errors'   => $errors,
    'action'   => url('pages/suppliers/create.php'),
]);
component('layout/footer');
