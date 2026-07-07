<?php
declare(strict_types=1);

/* ============================================================
   Middleware: role

   Restrict a page to an explicit set of roles. Set $allowed_roles
   before including:

       $allowed_roles = ['admin', 'manager'];
       require POS_ROOT . '/middleware/role.php';

   Admin always passes. Prefer permission.php (capability-based)
   over this where possible — roles change, capabilities are stable.
   ============================================================ */

if (!function_exists('guard_roles')) {
    function guard_roles(array $roles): void
    {
        $current = Auth::role();
        if ($current !== 'admin' && !in_array($current, $roles, true)) {
            redirect(url('index.php?denied=1'));
        }
    }
}

if (!empty($allowed_roles) && is_array($allowed_roles)) {
    guard_roles($allowed_roles);
}
