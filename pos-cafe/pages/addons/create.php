<?php
declare(strict_types=1);
/* Addons — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addons';
$errors    = [];
$addon     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/addons/create.php'));
    }
    $addon = $_POST;
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->required('price')->numeric('price');

    if ($v->passes()) {
        $image = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $upload_dir = APP_ROOT . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image = 'uploads/' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . basename($image));
            }
        }
        $id = (new Addon())->create([
            'name'      => input('name'),
            'price'     => (float) input('price', 0),
            'image'     => $image,
            'is_active' => isset($_POST['is_active']),
        ]);
        flash('Addon #' . $id . ' created.', 'success');
        redirect(url('pages/addons/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Addon';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Addon', 'crumbs' => ['Catalog', 'Addons', 'New']]);
component('addons/addon-form', [
    'addon'  => $addon,
    'errors' => $errors,
    'action' => url('pages/addons/create.php'),
]);
component('layout/footer');
