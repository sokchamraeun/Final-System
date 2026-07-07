<?php
declare(strict_types=1);
/* Roles — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_roles';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'roles';
$errors    = [];
$role      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/roles/create.php'));
    }
    $role = $_POST;
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->required('slug')->max('slug', 50)
      ->max('icon', 50)
      ->max('description', 255);

    if ($v->passes()) {
        $id = (new Role())->create([
            'slug'        => input('slug'),
            'name'        => input('name'),
            'icon'        => input('icon'),
            'color'       => input('color'),
            'description' => input('description'),
            'is_system'   => isset($_POST['is_system']) ? 1 : 0,
        ]);
        flash('Role #' . $id . ' created.', 'success');
        redirect(url('pages/roles/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Role';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Role', 'crumbs' => ['Team', 'Roles', 'New']]);
component('roles/role-form', [
    'role'   => $role,
    'errors' => $errors,
    'action' => url('pages/roles/create.php'),
]);
component('layout/footer');
