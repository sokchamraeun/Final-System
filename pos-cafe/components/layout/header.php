<?php
declare(strict_types=1);
/* ============================================================
   Layout: header — always paired with layout/footer.php.
   - SPA mode: output metadata div for the JS router, return.
   - Direct mode: start output buffering; the shell renders
     later through app.php (included by footer).
   ============================================================ */
$pageTitle    = $pageTitle    ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';

if (is_spa_request()) {
    /* SPA mode — set response header for the JS router
       then output metadata and skip the HTML shell entirely. */
    header('X-SPA-Content: 1');
    ?><div style="display:none" id="spa-page" data-title="<?= e($pageTitle) ?>" data-subtitle="<?= e($pageSubtitle) ?>"></div><?php
    return;
}

/* Direct access — store page meta and buffer the content.
   layout/footer.php will include app.php as the shell. */
$GLOBALS['_layout_pageTitle']    = $pageTitle;
$GLOBALS['_layout_pageSubtitle'] = $pageSubtitle;
ob_start();
