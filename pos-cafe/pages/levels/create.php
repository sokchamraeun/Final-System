<?php
declare(strict_types=1);
/* Create a new level (size / ice / sugar). */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_levels';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'levels';
$type      = (string) input('type', 'size');

$tables = [
    'size'  => 'size_levels',
    'ice'   => 'ice_levels',
    'sugar' => 'sugar_levels',
    'milk'  => 'milk_levels',
];

if (!isset($tables[$type])) redirect(url('pages/levels/index.php'));

$tableName = $tables[$type];
$level     = new Level($tableName);
$errors    = [];
$old       = ['name' => '', 'display_order' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'name'          => (string) input('name', ''),
        'display_order' => (string) input('display_order', '0'),
    ];

    if (trim($old['name']) === '') {
        $errors['name'][] = 'Name is required.';
    }

    if (!$errors) {
        $level->create($old);
        redirect(url('pages/levels/index.php?type=' . $type));
    }
}

$pageTitle = 'New ' . ucfirst($type) . ' Level';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => $pageTitle, 'crumbs' => ['Catalog', 'Customize Levels', 'New']]);
component('levels/level-form', [
    'type'   => $type,
    'errors' => $errors,
    'old'    => $old,
    'action' => url('pages/levels/create.php?type=' . $type),
]);
component('layout/footer');
