<?php
declare(strict_types=1);
/* ============================================================
   Layout: footer — always paired with layout/header.php.
   - SPA mode: return early (shell owns the close tags).
   - Direct mode: capture buffered content and render
     the universal shell via app.php.
   ============================================================ */
if (is_spa_request()) {
    return;
}

/* Capture everything rendered since layout/header.php */
$initialContent = ob_get_clean();

/* Set up variables the app.php shell expects.
   $navActive is already in scope from the requiring page. */
$pageTitle    = $GLOBALS['_layout_pageTitle']    ?? 'Dashboard';
$pageSubtitle = $GLOBALS['_layout_pageSubtitle'] ?? '';
$navActive    = $navActive ?? '';
$roleMeta     = class_exists('Auth') ? Auth::roleMeta() : [];
$defaultUrl   = '';   // already on the page, don't navigate

require POS_ROOT . '/layout/app.php';
