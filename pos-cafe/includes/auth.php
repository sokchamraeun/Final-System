<?php
declare(strict_types=1);

/* ============================================================
   Authentication guard.

   `require __DIR__ . '/../../includes/auth.php';` at the top of a
   page forces a valid session, redirecting guests to the existing
   login screen. Session keys (user_id, username, role) are the
   same ones the root app already sets on login.
   ============================================================ */

if (empty($_SESSION['user_id'])) {
    // Preserve the intended destination so login can bounce back.
    $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '';
    redirect(root_url('login.php'));
}
