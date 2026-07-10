<?php
declare(strict_types=1);
/* Categories — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'categories';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'categories';
$model     = new Category();

$id = (int) input('id', 0);
$category = $model->find($id);
if (!$category) {
    flash('Category not found.', 'error');
    redirect(url('pages/categories/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/categories/edit.php?id=' . $id));
    }
    $category = array_merge($category, $_POST);
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
                if (!empty($category['image']) && file_exists(APP_ROOT . '/' . $category['image'])) {
                    @unlink(APP_ROOT . '/' . $category['image']);
                }
            }
        }
        $model->update($id, [
            'name'          => input('name'),
            'slug'          => input('slug'),
            'icon'          => input('icon'),
            'image'         => $image,
            'display_order' => (int) input('display_order', 0),
            'is_active'     => isset($_POST['is_active']),
            'enable_ice'    => !empty($_POST['enable_ice']),
            'enable_sugar'  => !empty($_POST['enable_sugar']),
            'enable_milk'   => !empty($_POST['enable_milk']),
            'enable_addons' => !empty($_POST['enable_addons']),
        ]);
        flash('Category updated.', 'success');
        redirect(url('pages/categories/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit · ' . ($category['name'] ?? 'Category');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Category', 'crumbs' => ['Catalog', 'Categories', 'Edit']]);
component('categories/category-form', [
    'category' => $category,
    'errors'   => $errors,
    'action'   => url('pages/categories/edit.php?id=' . $id),
]);
component('layout/footer');
