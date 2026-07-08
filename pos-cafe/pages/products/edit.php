<?php
declare(strict_types=1);
/* Products — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'products';
$model     = new Product();

$id = (int) input('id', 0);
$product = $model->find($id);
if (!$product) {
    flash('Product not found.', 'error');
    redirect(url('pages/products/index.php'));
}

$errors  = [];
$partial = !empty($_GET['partial']);

$levelModel = new Level('size_levels');
$sizeLevels  = $levelModel->active();
$iceLevels   = $levelModel->setTable('ice_levels')->active();
$sugarLevels = $levelModel->setTable('sugar_levels')->active();
$milkLevels  = $levelModel->setTable('milk_levels')->active();
$levelModel->setTable('size_levels');
$sizes = $model->getSizes($id);
$selectedIce   = $model->getIceLevelIds($id);
$selectedSugar = $model->getSugarLevelIds($id);
$selectedMilk  = $model->getMilkLevelIds($id);
$selectedSizes  = $model->getSelectedSizeIds($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/products/edit.php?id=' . $id));
    }
    $product = array_merge($product, $_POST);
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100);

    if ($v->passes()) {
        $image = (string) input('existing_image', '');
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            if (in_array($_FILES['image']['type'], $allowed)) {
                $upload_dir = APP_ROOT . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $image = 'uploads/' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . basename($image));
                if (!empty($product['image']) && file_exists(APP_ROOT . '/' . $product['image'])) {
                    @unlink(APP_ROOT . '/' . $product['image']);
                }
            }
        }
        $hasSizes = isset($_POST['has_sizes']);
        if ($hasSizes) {
            $price = medium_size_price($sizeLevels, $_POST['size_level_ids'] ?? [], $_POST['size_prices'] ?? []);
        } else {
            $price = (float) input('price', 0);
        }

        $model->update($id, [
            'name'         => input('name'),
            'price'        => $price,
            'image'        => $image,
            'category'     => input('category'),
            'category_id'  => (int) input('category_id', 0) ?: null,
            'description'  => input('description'),
            'badge_text'   => input('badge_text'),
            'has_sizes'    => $hasSizes ? 1 : 0,
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
        ]);

        if ($hasSizes) {
            $model->saveSizes($id, $_POST['size_level_ids'] ?? [], $_POST['size_prices'] ?? [], $_POST['size_factors'] ?? []);
        } else {
            $model->saveSizes($id, [], [], []);
        }

        $model->saveIceLevels($id, $_POST['ice_level_ids'] ?? []);
        $model->saveSugarLevels($id, $_POST['sugar_level_ids'] ?? []);
        $model->saveMilkLevels($id, $_POST['milk_level_ids'] ?? []);

        if ($partial) {
            echo 'OK';
            return;
        }

        flash('Product updated.', 'success');
        redirect(url('pages/products/index.php'));
    }
    $errors = $v->errors();
}

$categories = (new Category())->active();
$pageTitle  = 'Edit · ' . ($product['name'] ?? 'Product');

if ($partial) {
    component('products/product-form', [
        'product'    => $product,
        'categories' => $categories,
        'errors'     => $errors,
        'action'     => url('pages/products/edit.php?id=' . $id . '&partial=1'),
        'sizes'      => $sizes,
        'sizeLevels' => $sizeLevels,
        'iceLevels'     => $iceLevels,
        'sugarLevels'   => $sugarLevels,
        'milkLevels'    => $milkLevels,
        'selectedIce'   => $selectedIce,
        'selectedSugar' => $selectedSugar,
        'selectedMilk'  => $selectedMilk,
        'selectedSizes' => $selectedSizes,
        'modal'         => true,
    ]);
    return;
}

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Product', 'crumbs' => ['Catalog', 'Products', 'Edit']]);
component('products/product-form', [
    'product'    => $product,
    'categories' => $categories,
    'errors'     => $errors,
    'action'     => url('pages/products/edit.php?id=' . $id),
    'sizes'      => $sizes,
    'sizeLevels' => $sizeLevels,
    'iceLevels'     => $iceLevels,
    'sugarLevels'   => $sugarLevels,
    'milkLevels'    => $milkLevels,
    'selectedIce'   => $selectedIce,
    'selectedSugar' => $selectedSugar,
    'selectedMilk'  => $selectedMilk,
    'selectedSizes' => $selectedSizes,
]);
component('layout/footer');
