<?php
declare(strict_types=1);

/* ============================================================
   RBAC glue.

   can(string $slug): bool already exists (defined in the root
   config.php) and checks the current session role against the
   role_permissions table — admin always passes. We only add thin
   guard helpers on top so pages read cleanly.
   ============================================================ */

if (!function_exists('can')) {
    /* Extremely defensive fallback: if the root config.php somehow did not
       define can(), deny everything except admin rather than fatal-error. */
    function can(string $slug): bool
    {
        return (($_SESSION['role'] ?? '') === 'admin');
    }
}

if (!function_exists('require_can')) {
    /** Abort to the app home (denied) unless the user holds $slug. */
    function require_can(string $slug): void
    {
        if (!can($slug)) {
            redirect(url('index.php?denied=1'));
        }
    }
}

if (!function_exists('can_any')) {
    /** True if the user holds ANY of the given permissions. */
    function can_any(string ...$slugs): bool
    {
        foreach ($slugs as $s) {
            if (can($s)) {
                return true;
            }
        }
        return false;
    }
}
