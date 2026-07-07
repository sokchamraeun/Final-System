<?php
declare(strict_types=1);
/* ============================================================
   Layout: app — SPA shell with persistent sidebar and navbar.
   Render ONCE per page load.  All subsequent navigation swaps
   content via assets/js/spa.js.

   Expects in scope:
     $defaultUrl      string   initial URL to fetch (empty if none)
     $pageTitle       string   initial browser-tab title
     $initialContent  string   pre-rendered HTML for first paint
                               (e.g. access-denied); empty = loading
     $navActive       string   active sidebar group
     $roleMeta        array    Auth::roleMeta()
   ============================================================ */
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>(function(){if((localStorage.getItem('theme')||'light')==='dark')document.documentElement.classList.add('dark');})();</script>
  <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            brand:   { DEFAULT: '#F59E0B', 600: '#D97706' },
            teal:    { DEFAULT: '#0F766E' },
            sidebar: '#111827',
          },
        },
      },
    };
  </script>
</head>
<body class="h-full font-sans bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100">
<div class="flex h-full">

  <?php require POS_ROOT . '/components/sidebar/index.php'; ?>

  <!-- Main column -->
  <div class="flex flex-1 flex-col min-w-0 lg:ml-[242px]">

    <!-- Sticky top navbar -->
    <header class="sticky top-0 z-20 flex h-[72px] items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:px-6">
      <button onclick="sbToggle()" class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden" aria-label="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="min-w-0 flex-1">
        <h1 class="truncate text-lg font-bold text-slate-800 dark:text-white" id="spaTitle"><?= e($pageTitle) ?></h1>
        <p class="truncate text-xs text-slate-500" id="spaSubtitle">&nbsp;</p>
      </div>

      <button id="themeToggle" onclick="toggleTheme()" class="grid h-10 w-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle theme">
        <i class="fa-solid fa-moon dark:hidden"></i>
        <i class="fa-solid fa-sun hidden dark:inline"></i>
      </button>

      <button class="relative grid h-10 w-10 place-items-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" title="Notifications">
        <i class="fa-regular fa-bell"></i>
      </button>

      <div class="flex items-center gap-3 border-l border-slate-200 pl-3 dark:border-slate-800">
        <div class="hidden text-right sm:block">
          <div class="text-sm font-semibold leading-tight"><?= e(Auth::name()) ?></div>
          <div class="text-xs text-slate-500"><?= e($roleMeta['name'] ?? '') ?></div>
        </div>
        <div class="grid h-10 w-10 place-items-center rounded-full text-sm font-bold text-white" style="background: <?= e($roleMeta['color'] ?? '#d1904b') ?>">
          <?= e(Auth::initials()) ?>
        </div>
      </div>
    </header>

    <!-- SPA content area (first paint content or loading spinner) -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6" id="spaContent">
      <?php $__flash = flash(); if ($__flash) component('common/alerts', ['flash' => $__flash]); ?>
      <?php if ($initialContent !== ''): ?>
        <?= $initialContent ?>
      <?php else: ?>
        <div class="flex items-center justify-center py-20 text-slate-400">
          <i class="fa-solid fa-spinner fa-spin text-3xl text-brand"></i>
        </div>
      <?php endif; ?>
    </main>

  </div>
</div>

<?php
/* ── Embed pos-cafe base URL for the SPA router ── */
$spaBase = rtrim(BASE_URL, '/') . '/';
?>
<!-- Toast container -->
<div id="toastRoot" class="fixed right-4 top-20 z-[100] flex flex-col gap-2"></div>
<script>window.SPA_BASE = <?= json_encode($spaBase) ?>; window.SPA_DEBUG = true;</script>

<script>
  // ── Theme toggle (class strategy) ──
  function toggleTheme() {
    var isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  }

  // ── Toast helper ──
  window.toast = function (message, type) {
    type = type || 'success';
    var colors = {
      success: 'border-emerald-500 text-emerald-700',
      error:   'border-red-500 text-red-700',
      info:    'border-blue-500 text-blue-700',
    };
    var icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
    var el = document.createElement('div');
    el.className = 'flex items-center gap-2 rounded-xl border-l-4 bg-white px-4 py-3 text-sm font-medium shadow-lg transition-all duration-300 dark:bg-slate-800 dark:text-slate-100 ' + (colors[type] || colors.info);
    el.style.transform = 'translateX(120%)';
    el.innerHTML = '<i class="fa-solid ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
    document.getElementById('toastRoot').appendChild(el);
    requestAnimationFrame(function () { el.style.transform = 'translateX(0)'; });
    setTimeout(function () {
      el.style.transform = 'translateX(120%)';
      setTimeout(function () { el.remove(); }, 300);
    }, 2800);
  };
</script>
<script src="<?= e(asset('js/spa.js')) ?>"></script>
<script>
  // ── SPA bootstrap: fetch the default page after spa.js has loaded ──
  (function () {
    var url = <?= json_encode($defaultUrl) ?>;
    if (url) SPA.navigate(url, true);
  })();
</script>
</body>
</html>
