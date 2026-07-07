<?php
declare(strict_types=1);
/* Purchase Orders â€” edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'purchase-orders';
$model     = new PurchaseOrder();

$id = (int) input('id', 0);
$purchaseOrder = $model->find($id);
if (!$purchaseOrder) {
    flash('Purchase order not found.', 'error');
    redirect(url('pages/purchase-orders/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/purchase-orders/edit.php?id=' . $id));
    }
    $purchaseOrder = array_merge($purchaseOrder, $_POST);
    $v = new Validator($_POST);
    $v->required('po_number')->max('po_number', 50)
      ->required('supplier_id');

    if ($v->passes()) {
        $model->update($id, [
            'po_number'   => input('po_number'),
            'supplier_id' => (int) input('supplier_id'),
            'status'      => input('status', 'pending'),
            'notes'       => input('notes'),
            'total_cost'  => (float) input('total_cost', 0),
            'ordered_at'  => input('ordered_at'),
            'received_at' => input('received_at'),
        ]);
        flash('Purchase order updated.', 'success');
        redirect(url('pages/purchase-orders/index.php'));
    }
    $errors = $v->errors();
}

$suppliers = (new Supplier())->options();
$pageTitle = 'Edit Â· ' . ($purchaseOrder['po_number'] ?? 'Purchase Order');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Purchase Order', 'crumbs' => ['Inventory', 'Purchase Orders', 'Edit']]);
component('purchase-orders/purchase-order-form', [
    'purchaseOrder' => $purchaseOrder,
    'suppliers'     => $suppliers,
    'errors'        => $errors,
    'action'        => url('pages/purchase-orders/edit.php?id=' . $id),
]);
component('layout/footer');
