<?php
declare(strict_types=1);
/* Suppliers â€” edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'suppliers';
$model     = new Supplier();

$id = (int) input('id', 0);
$supplier = $model->find($id);
if (!$supplier) {
    flash('Supplier not found.', 'error');
    redirect(url('pages/suppliers/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/suppliers/edit.php?id=' . $id));
    }
    $supplier = array_merge($supplier, $_POST);
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->max('contact_person', 100)
      ->max('phone', 30)
      ->max('email', 100)
      ->max('address', 255)
      ->max('notes', 500);

    if ($v->passes()) {
        $model->update($id, [
            'name'           => input('name'),
            'contact_person' => input('contact_person'),
            'phone'          => input('phone'),
            'email'          => input('email'),
            'address'        => input('address'),
            'notes'          => input('notes'),
        ]);
        flash('Supplier updated.', 'success');
        redirect(url('pages/suppliers/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit Â· ' . ($supplier['name'] ?? 'Supplier');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Supplier', 'crumbs' => ['Inventory', 'Suppliers', 'Edit']]);
component('suppliers/supplier-form', [
    'supplier' => $supplier,
    'errors'   => $errors,
    'action'   => url('pages/suppliers/edit.php?id=' . $id),
]);
component('layout/footer');
