<?php
session_start();
require __DIR__ . '/../../../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => 0, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_SESSION['last_cart_backup'])) {
    echo json_encode(['ok' => 0, 'error' => 'No backup cart found']);
    exit;
}

$_SESSION['cart'] = $_SESSION['last_cart_backup'];
unset($_SESSION['last_cart_backup']);
echo json_encode(['ok' => 1, 'count' => count($_SESSION['cart'])]);
