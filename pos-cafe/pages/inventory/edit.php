<?php
declare(strict_types=1);
/* Inventory — edit ingredient. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive  = 'inventory';
$model      = new Ingredient();

$id = (int) input('id', 0);
$ingredient = $model->find($id);
if (!$ingredient) {
    flash('Ingredient not found.', 'error');
    redirect(url('pages/inventory/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/inventory/edit.php?id=' . $id));
    }
    $ingredient = array_merge($ingredient, $_POST);
    $v = new Validator($_POST);
    $v->required('ingredient_name')->max('ingredient_name', 100);

    if ($v->passes()) {
        $model->update($id, [
            'ingredient_name' => input('ingredient_name'),
            'unit'            => input('unit'),
            'stock_quantity'  => (float) input('stock_quantity', 0),
            'minimum_stock'   => (float) input('minimum_stock', 0),
            'cost_price'      => (float) input('cost_price', 0),
            'purchase_qty'    => (float) input('purchase_qty', 0),
            'cost_per_unit'   => (float) input('cost_per_unit', 0),
            'supplier_id'     => (int) input('supplier_id', 0) ?: null,
        ]);
        flash('Ingredient updated.', 'success');
        redirect(url('pages/inventory/index.php'));
    }
    $errors = $v->errors();
}

$suppliers = (new Supplier())->options();
$pageTitle = 'Edit · ' . ($ingredient['ingredient_name'] ?? 'Ingredient');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Ingredient', 'crumbs' => ['Stock', 'Inventory', 'Edit']]);
component('inventory/ingredient-form', [
    'ingredient' => $ingredient,
    'suppliers'  => $suppliers,
    'errors'     => $errors,
    'action'     => url('pages/inventory/edit.php?id=' . $id),
]);
component('layout/footer');
