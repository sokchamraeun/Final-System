<?php
declare(strict_types=1);
/* Categories — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'categories';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'categories';
$errors    = [];
$category  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/categories/create.php'));
    }
    $category = $_POST;
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
        $id = (new Category())->create([
            'name'          => input('name'),
            'slug'          => input('slug'),
            'icon'          => input('icon'),
            'image'         => $image,
            'display_order' => (int) input('display_order', 0),
            'is_active'     => isset($_POST['is_active']),
        ]);
        flash('Category #' . $id . ' created.', 'success');
        redirect(url('pages/categories/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Category';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Category', 'crumbs' => ['Catalog', 'Categories', 'New']]);
component('categories/category-form', [
    'category' => $category,
    'errors'   => $errors,
    'action'   => url('pages/categories/create.php'),
]);
component('layout/footer');
