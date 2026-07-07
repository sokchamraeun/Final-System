<?php
declare(strict_types=1);
/* ============================================================
   pos-cafe entry point — SPA shell.
   Direct request renders the app shell with sidebar + navbar;
   subsequent navigation is handled client-side by spa.js.
   ============================================================ */
require __DIR__ . '/config/app.php';
require POS_ROOT . '/middleware/auth.php';

$denied = isset($_GET['denied']);

/* ── Default landing page based on permissions ── */
if (can('products')) {
    $defaultUrl = url('products');
    $pageTitle  = 'Products';
} elseif (can('dashboard')) {
    $defaultUrl = url('dashboard');
    $pageTitle  = 'Dashboard';
} else {
    $defaultUrl = url('menu');
    $pageTitle  = 'Menu';
}

$navActive = '';
$roleMeta  = Auth::roleMeta();

/* ── Render initial content (e.g. access-denied) inline ── */
if ($denied) {
    ob_start();
    $pageTitle = 'Access denied';
    ?>
    <div class="mx-auto max-w-md">
      <?php component('common/empty-state', [
          'icon'    => 'fa-lock',
          'title'   => 'Access denied',
          'message' => 'Your role does not have permission to view that page. Contact an administrator if you think this is a mistake.',
          'action'  => '<a href="' . e($defaultUrl) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600"><i class="fa-solid fa-arrow-left"></i> Back to app</a>',
      ]); ?>
    </div>
    <?php
    $initialContent = ob_get_clean();
    $defaultUrl = ''; // don't navigate after showing denied
} else {
    $initialContent = '';
}

/* ── Render the SPA shell (renders sidebar, navbar, content area) ── */
require POS_ROOT . '/layout/app.php';
