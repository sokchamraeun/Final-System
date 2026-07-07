<?php
declare(strict_types=1);
/* Admins â€” edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'admins';
$model     = new User();

$id = (int) input('id', 0);
$admin = $model->find($id);
if (!$admin) {
    flash('Admin not found.', 'error');
    redirect(url('pages/admins/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/admins/edit.php?id=' . $id));
    }
    $admin = array_merge($admin, $_POST);
    $v = new Validator($_POST);
    $v->required('username')->max('username', 50);

    if ($v->passes()) {
        $data = ['username' => input('username')];
        $password = input('password');
        if ($password !== '') {
            $data['password'] = $password;
        }
        $roleId = (int) input('role_id', 0);
        $data['role_id'] = $roleId ?: null;

        $model->update($id, $data);
        flash('Admin updated.', 'success');
        redirect(url('pages/admins/index.php'));
    }
    $errors = $v->errors();
}

$roles    = (new Role())->options();
$pageTitle = 'Edit Â· ' . ($admin['username'] ?? 'Admin');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Admin', 'crumbs' => ['Settings', 'Admins', 'Edit']]);
component('admins/admin-form', [
    'admin'  => $admin,
    'roles'  => $roles,
    'errors' => $errors,
    'action' => url('pages/admins/edit.php?id=' . $id),
]);
component('layout/footer');
