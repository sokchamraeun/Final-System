<?php
declare(strict_types=1);
/* Roles — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_roles';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'roles';
$model     = new Role();

$id = (int) input('id', 0);
$role = $model->find($id);
if (!$role) {
    flash('Role not found.', 'error');
    redirect(url('pages/roles/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/roles/edit.php?id=' . $id));
    }
    $role = array_merge($role, $_POST);
    $v = new Validator($_POST);
    $v->required('name')->max('name', 100)
      ->required('slug')->max('slug', 50)
      ->max('icon', 50)
      ->max('description', 255);

    if ($v->passes()) {
        $model->update($id, [
            'slug'        => input('slug'),
            'name'        => input('name'),
            'icon'        => input('icon'),
            'color'       => input('color'),
            'description' => input('description'),
            'is_system'   => isset($_POST['is_system']) ? 1 : 0,
        ]);
        flash('Role updated.', 'success');
        redirect(url('pages/roles/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit · ' . ($role['name'] ?? 'Role');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Role', 'crumbs' => ['Team', 'Roles', 'Edit']]);
component('roles/role-form', [
    'role'   => $role,
    'errors' => $errors,
    'action' => url('pages/roles/edit.php?id=' . $id),
]);
component('layout/footer');
