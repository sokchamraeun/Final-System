<?php
declare(strict_types=1);

/* ============================================================
   Middleware: permission

   Guards a page by a single permission slug. Set $required_permission
   before including, or call guard_permission() directly.

       $required_permission = 'products';
       require POS_ROOT . '/middleware/permission.php';
   ============================================================ */

if (!function_exists('guard_permission')) {
    function guard_permission(string $slug): void
    {
        require_can($slug); // defined in includes/permissions.php — admin always passes
    }
}

if (!empty($required_permission)) {
    guard_permission($required_permission);
}
