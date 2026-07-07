<?php
declare(strict_types=1);
/* Products — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'products';
$model     = new Product();
$errors    = [];

$levelModel = new Level('size_levels');
$sizeLevels  = $levelModel->active();
$iceLevels   = $levelModel->setTable('ice_levels')->active();
$sugarLevels = $levelModel->setTable('sugar_levels')->active();
$milkLevels  = $levelModel->setTable('milk_levels')->active();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/products/create.php'));
    }
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100);

    if ($v->passes()) {
        $image = '';
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $upload_dir = APP_ROOT . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image = 'uploads/' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . basename($image));
            }
        }
        $id = $model->create([
            'name'          => input('name'),
            'price'         => 0.0,
            'image'         => $image,
            'category'      => input('category'),
            'category_id'   => (int) input('category_id', 0) ?: null,
            'description'   => input('description'),
            'badge_text'    => input('badge_text'),
            'has_sizes'     => isset($_POST['has_sizes']) ? 1 : 0,
            'is_available'  => isset($_POST['is_available']) ? 1 : 0,
        ]);

        if (isset($_POST['has_sizes'])) {
            $model->saveSizes($id, $_POST['size_level_ids'] ?? [], $_POST['size_prices'] ?? [], $_POST['size_factors'] ?? []);
        }

        $model->saveIceLevels($id, $_POST['ice_level_ids'] ?? []);
        $model->saveSugarLevels($id, $_POST['sugar_level_ids'] ?? []);
        $model->saveMilkLevels($id, $_POST['milk_level_ids'] ?? []);

        flash('Product created.', 'success');
        redirect(url('pages/products/index.php'));
    }
    $errors = $v->errors();
}

$categories = (new Category())->active();
$pageTitle  = 'Create Product';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Create Product', 'crumbs' => ['Catalog', 'Products', 'Create']]);
component('products/product-form', [
    'categories' => $categories,
    'errors'     => $errors,
    'action'     => url('pages/products/create.php'),
    'sizeLevels' => $sizeLevels,
    'iceLevels'   => $iceLevels,
    'sugarLevels' => $sugarLevels,
    'milkLevels'  => $milkLevels,
]);
component('layout/footer');
