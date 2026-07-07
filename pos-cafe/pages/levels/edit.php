<?php
declare(strict_types=1);
/* Edit a level (size / ice / sugar). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_levels';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'levels';
$type      = (string) input('type', 'size');
$id        = max(0, (int) input('id', 0));

$tables = [
    'size'  => 'size_levels',
    'ice'   => 'ice_levels',
    'sugar' => 'sugar_levels',
    'milk'  => 'milk_levels',
];

if (!isset($tables[$type]) || !$id) redirect(url('pages/levels/index.php'));

$tableName = $tables[$type];
$level     = new Level($tableName);
$row       = $level->find($id);
if (!$row) redirect(url('pages/levels/index.php?type=' . $type));

$errors = [];
$old    = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'          => (string) input('name', ''),
        'display_order' => (string) input('display_order', '0'),
    ];

    if (trim($old['name']) === '') {
        $errors['name'][] = 'Name is required.';
    }

    if (!$errors) {
        $level->update($id, $old);
        redirect(url('pages/levels/index.php?type=' . $type));
    }
}

$pageTitle = 'Edit ' . ucfirst($type) . ' Level';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => $pageTitle, 'crumbs' => ['Catalog', 'Customize Levels', 'Edit']]);
component('levels/level-form', [
    'type'   => $type,
    'errors' => $errors,
    'old'    => $old,
    'action' => url('pages/levels/edit.php?type=' . $type . '&id=' . $id),
]);
component('layout/footer');
