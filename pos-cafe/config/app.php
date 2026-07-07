<?php
declare(strict_types=1);

/* ============================================================
   pos-cafe — Application bootstrap.
   Include this ONCE at the top of every page and api endpoint:

       require __DIR__ . '/../../config/app.php';   // from a pages sub-folder
       require __DIR__ . '/../config/app.php';       // from the api folder

   It: starts the session, loads the canonical root config.php
   (the single source of truth for the DB connection, settings
   constants, RBAC can() and schema migrations), registers a
   PSR-style autoloader for classes/, and pulls in the shared
   helper/permission/validator functions.
   ============================================================ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ── Paths & app identity ── */
define('POS_ROOT', dirname(__DIR__));            // .../FinalSystem/pos-cafe
define('APP_ROOT', dirname(POS_ROOT));           // .../FinalSystem (owns config.php)
define('APP_NAME', "Bird's Nest POS");

/* URL bases — adjust these two if your vhost/docroot differs.
   Defaults assume http://localhost/FinalSystem/ under XAMPP. */
if (!defined('ROOT_URL')) define('ROOT_URL', '/FinalSystem');
if (!defined('BASE_URL')) define('BASE_URL', ROOT_URL . '/pos-cafe');

/* ── Canonical config: DB ($conn), constants, can(), migrations ──
   We deliberately reuse the existing, battle-tested config.php instead of
   duplicating 700 lines of connection + migration logic. Single source of
   truth: schema and settings stay owned by the root app. */
require_once APP_ROOT . '/config.php';           // provides mysqli $conn + can() + TAX_RATE etc.
$GLOBALS['conn'] = $conn ?? null;

/* ── Autoloader for classes/ ── */
spl_autoload_register(static function (string $class): void {
    $file = POS_ROOT . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

/* ── UI constants + shared function libraries ── */
require_once POS_ROOT . '/config/constants.php';
require_once POS_ROOT . '/includes/helpers.php';
require_once POS_ROOT . '/includes/functions.php';
require_once POS_ROOT . '/includes/permissions.php';
require_once POS_ROOT . '/includes/validators.php';
