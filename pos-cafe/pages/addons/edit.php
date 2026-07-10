<?php
declare(strict_types=1);
/* Addons — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addons';
$model     = new Addon();

$id    = (int) input('id', 0);
$addon = $model->find($id);
if (!$addon) {
    flash('Addon not found.', 'error');
    redirect(url('pages/addons/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/addons/edit.php?id=' . $id));
    }
    $addon = array_merge($addon, $_POST);
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->required('price')->numeric('price');

    if ($v->passes()) {
        $image = (string) input('existing_image', '');
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $upload_dir = APP_ROOT . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image = 'uploads/' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . basename($image));
                if (!empty($addon['image']) && file_exists(APP_ROOT . '/' . $addon['image'])) {
                    @unlink(APP_ROOT . '/' . $addon['image']);
                }
            }
        }
        $model->update($id, [
            'name'      => input('name'),
            'price'     => (float) input('price', 0),
            'image'     => $image,
            'is_active' => isset($_POST['is_active']),
        ]);
        flash('Addon updated.', 'success');
        redirect(url('pages/addons/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit · ' . ($addon['name'] ?? 'Addon');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Addon', 'crumbs' => ['Catalog', 'Addons', 'Edit']]);
component('addons/addon-form', [
    'addon'  => $addon,
    'errors' => $errors,
    'action' => url('pages/addons/edit.php?id=' . $id),
]);
component('layout/footer');
