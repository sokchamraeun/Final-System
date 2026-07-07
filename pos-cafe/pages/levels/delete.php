<?php
declare(strict_types=1);
/* Delete a level (size / ice / sugar). POST only. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_levels';
require POS_ROOT . '/middleware/permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('pages/levels/index.php'));
}

$type = (string) input('type', 'size');
$id   = max(0, (int) input('id', 0));

$tables = [
    'size'  => 'size_levels',
    'ice'   => 'ice_levels',
    'sugar' => 'sugar_levels',
    'milk'  => 'milk_levels',
];

if (!isset($tables[$type]) || !$id) {
    redirect(url('pages/levels/index.php'));
}

$level = new Level($tables[$type]);
$level->delete($id);

redirect(url('pages/levels/index.php?type=' . $type));
