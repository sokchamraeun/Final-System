<?php
declare(strict_types=1);
/* Admins â€” create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'admins';
$errors    = [];
$admin     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/admins/create.php'));
    }
    $admin = $_POST;
    $v = new Validator($_POST);
    $v->required('username')->max('username', 50)
      ->required('password')->min('password', 6);

    if ($v->passes()) {
        $id = (new User())->create([
            'username'            => input('username'),
            'password'            => input('password'),
            'role_id'             => (int) input('role_id', 0) ?: null,
            'must_change_password' => 0,
        ]);
        flash('Admin #' . $id . ' created.', 'success');
        redirect(url('pages/admins/index.php'));
    }
    $errors = $v->errors();
}

$roles    = (new Role())->options();
$pageTitle = 'New Admin';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Admin', 'crumbs' => ['Settings', 'Admins', 'New']]);
component('admins/admin-form', [
    'admin'  => $admin,
    'roles'  => $roles,
    'errors' => $errors,
    'action' => url('pages/admins/create.php'),
]);
component('layout/footer');
