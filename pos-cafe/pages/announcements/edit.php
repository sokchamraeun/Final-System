<?php
declare(strict_types=1);
/* Announcements â€” edit. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'announcements';
$model     = new Announcement();

$id = (int) input('id', 0);
$announcement = $model->find($id);
if (!$announcement) {
    flash('Announcement not found.', 'error');
    redirect(url('pages/announcements/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/announcements/edit.php?id=' . $id));
    }
    $announcement = array_merge($announcement, $_POST);
    $v = new Validator($_POST);
    $v->required('title')->max('title', 200)
      ->required('message');

    if ($v->passes()) {
        $model->update($id, [
            'title'      => input('title'),
            'message'    => input('message'),
            'type'       => input('type', 'info'),
            'expires_at' => input('expires_at'),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ]);
        flash('Announcement updated.', 'success');
        redirect(url('pages/announcements/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'Edit Â· ' . ($announcement['title'] ?? 'Announcement');

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Announcement', 'crumbs' => ['Settings', 'Announcements', 'Edit']]);
component('announcements/announcement-form', [
    'announcement' => $announcement,
    'errors'       => $errors,
    'action'       => url('pages/announcements/edit.php?id=' . $id),
]);
component('layout/footer');
