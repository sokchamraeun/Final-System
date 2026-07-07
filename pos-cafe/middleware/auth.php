<?php
declare(strict_types=1);

/* ============================================================
   Middleware: auth
   Guarantees an authenticated session. Include AFTER config/app.php:

       require POS_ROOT . '/middleware/auth.php';

   Thin alias over includes/auth.php so route files can compose
   middleware uniformly (auth → role → permission).
   ============================================================ */

require_once POS_ROOT . '/includes/auth.php';
