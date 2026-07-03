<?php
require 'admin_only.php';
require 'config.php';

$r = $conn->query("SHOW COLUMNS FROM employees LIKE 'user_id'");
if ($r && $r->num_rows === 0) {
    $conn->query("ALTER TABLE employees ADD COLUMN user_id INT NULL");
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $r = $conn->query("SELECT COALESCE(user_id, employee_id) AS uid FROM employees WHERE employee_id = $id");
    $uid = ($r && $row = $r->fetch_assoc()) ? (int)$row['uid'] : $id;
    mysqli_query($conn, "DELETE FROM employees WHERE employee_id = $id");
    if ($uid > 0) mysqli_query($conn, "DELETE FROM users WHERE user_id = $uid");
}

header("Location: employees.php");
exit;