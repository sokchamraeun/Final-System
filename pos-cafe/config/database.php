<?php
declare(strict_types=1);

/* ============================================================
   Database configuration.

   The live mysqli connection ($conn) is created by the root
   config.php (loaded in config/app.php) so credentials and the
   migration system live in ONE place. This file exposes that
   same connection through a small helper for code that wants an
   explicit handle without reaching for the global.

   To point pos-cafe at a different database, change the root
   config.php / db_config.local.php — not here.
   ============================================================ */

if (!function_exists('db')) {
    /**
     * Return the shared mysqli connection established by config.php.
     */
    function db(): mysqli
    {
        /** @var mysqli|null $conn */
        $conn = $GLOBALS['conn'] ?? null;
        if (!$conn instanceof mysqli) {
            throw new RuntimeException('Database connection not initialised. Did you include config/app.php first?');
        }
        return $conn;
    }
}
