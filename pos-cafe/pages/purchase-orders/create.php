<?php
declare(strict_types=1);
/* Purchase Orders — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive     = 'purchase-orders';
$errors        = [];
$purchaseOrder = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/purchase-orders/create.php'));
    }
    $purchaseOrder = $_POST;
    $v = new Validator($_POST);
    $v->required('po_number')->max('po_number', 50)
      ->required('supplier_id');

    if ($v->passes()) {
        $id = (new PurchaseOrder())->create([
            'po_number'   => input('po_number'),
            'supplier_id' => (int) input('supplier_id'),
            'status'      => input('status', 'pending'),
            'notes'       => input('notes'),
            'total_cost'  => (float) input('total_cost', 0),
            'ordered_at'  => input('ordered_at'),
            'received_at' => null,
            'created_by'  => Auth::id(),
        ]);
        flash('Purchase order #' . $id . ' created.', 'success');
        redirect(url('pages/purchase-orders/index.php'));
    }
    $errors = $v->errors();
}

$suppliers = (new Supplier())->options();
$pageTitle = 'New Purchase Order';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Purchase Order', 'crumbs' => ['Inventory', 'Purchase Orders', 'New']]);
component('purchase-orders/purchase-order-form', [
    'purchaseOrder' => $purchaseOrder,
    'suppliers'     => $suppliers,
    'errors'        => $errors,
    'action'        => url('pages/purchase-orders/create.php'),
]);
component('layout/footer');
