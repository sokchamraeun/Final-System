<?php
declare(strict_types=1);
/* Stock Counts â€” edit (record actual qty and complete). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stock';
$model     = new StockCount();

$id = (int) input('id', 0);
$count = $model->find($id);
if (!$count) {
    flash('Stock count not found.', 'error');
    redirect(url('pages/stock/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/stock/edit.php?id=' . $id));
    }

    $action = input('form_action', 'save');

    if ($action === 'complete') {
        $model->completeCount($id, Auth::id());
        flash('Stock count #' . $id . ' completed.', 'success');
        redirect(url('pages/stock/index.php'));
    }

    $v = new Validator($_POST);
    $v->required('business_date');

    if ($v->passes()) {
        $model->update($id, [
            'business_date' => (string) input('business_date'),
            'notes'         => (string) input('notes'),
        ]);

        /* Update actual_qty for each item */
        if (!empty($_POST['actual_qty']) && is_array($_POST['actual_qty'])) {
            $db = Database::instance();
            foreach ($_POST['actual_qty'] as $itemId => $qty) {
                $db->execute(
                    "UPDATE stock_count_items SET actual_qty = ? WHERE item_id = ? AND count_id = ?",
                    [(float) $qty, (int) $itemId, $id]
                );
            }
        }

        flash('Stock count updated.', 'success');
        redirect(url('pages/stock/edit.php?id=' . $id));
    }
    $errors = $v->errors();
}

$pageTitle  = 'Edit Stock Count #' . $id;
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Stock Count', 'crumbs' => ['Inventory', 'Stock Counts', 'Edit #' . $id]]);
component('stock/stock-form', [
    'action' => url('pages/stock/edit.php?id=' . $id),
    'count'  => $count,
    'errors' => $errors,
]);
component('layout/footer');
