<?php
declare(strict_types=1);
/* Announcements — create. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive    = 'announcements';
$errors       = [];
$announcement = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/announcements/create.php'));
    }
    $announcement = $_POST;
    $v = new Validator($_POST);
    $v->required('title')->max('title', 200)
      ->required('message');

    if ($v->passes()) {
        $id = (new Announcement())->create([
            'title'      => input('title'),
            'message'    => input('message'),
            'type'       => input('type', 'info'),
            'created_by' => Auth::id(),
            'expires_at' => input('expires_at'),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ]);
        flash('Announcement #' . $id . ' created.', 'success');
        redirect(url('pages/announcements/index.php'));
    }
    $errors = $v->errors();
}

$pageTitle = 'New Announcement';

component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'New Announcement', 'crumbs' => ['Settings', 'Announcements', 'New']]);
component('announcements/announcement-form', [
    'announcement' => $announcement,
    'errors'       => $errors,
    'action'       => url('pages/announcements/create.php'),
]);
component('layout/footer');
