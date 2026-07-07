<?php
declare(strict_types=1);
/* ============================================================
   Bootstrap for legacy files moved from root to pos-cafe/pages/legacy/.
   Loads the pos-cafe app bootstrap (DB, session, constants, helpers)
   and the auth guard.  Single include replaces the old
   `require 'config.php'` + `require 'auth.php'` pair.
   ============================================================ */
require_once __DIR__ . '/../../config/app.php';
require_once POS_ROOT . '/middleware/auth.php';
