<?php
declare(strict_types=1);
/* Employees — edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'employees';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'employees';
$model     = new Employee();

$id = (int) input('id', 0);
$employee = $model->find($id);
if (!$employee) {
    flash('Employee not found.', 'error');
    redirect(url('pages/employees/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/employees/edit.php?id=' . $id));
    }
    $employee = array_merge($employee, $_POST);
    $v = new Validator($_POST);
    $v->required('name')->max('name', 200);

    if ($v->passes()) {
        $photo = (string) input('existing_photo', '');
        if (!empty($_FILES['photo_file']['name']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (in_array($_FILES['photo_file']['type'], $allowed, true)) {
                $upload_dir = APP_ROOT . '/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext   = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
                $photo = 'uploads/' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . basename($photo));
                if (!empty($employee['photo']) && file_exists(APP_ROOT . '/' . $employee['photo'])) {
                    @unlink(APP_ROOT . '/' . $employee['photo']);
                }
            }
        }
        (new Employee())->update($id, [
            'name'          => input('name'),
            'phone'         => input('phone'),
            'address'       => input('address'),
            'date_of_birth' => input('date_of_birth'),
            'hire_date'     => input('hire_date'),
            'job_title'     => input('job_title'),
            'salary'        => (float) input('salary', 0),
            'photo'         => $photo,
            'shift'         => input('shift'),
        ]);
        flash('Employee updated.', 'success');
        redirect(url('pages/employees/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit · ' . ($employee['name'] ?? 'Employee');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Employee', 'crumbs' => ['HR', 'Employees', 'Edit']]);
component('employees/employee-form', [
    'employee' => $employee,
    'errors'   => $errors,
    'action'   => url('pages/employees/edit.php?id=' . $id),
]);
component('layout/footer');
