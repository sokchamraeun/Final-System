<?php
declare(strict_types=1);
/* Addons — list / search / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addons';
$canManage = can('addons');

$search = (string) input('search', '');
$page   = max(1, (int) input('page', 1));

$addon  = new Addon();
$result = $addon->paginate(['search' => $search], $page);

$db             = Database::instance();
$totalAddons    = (int) $db->scalar("SELECT COUNT(*) FROM addons");
$withImg        = (int) $db->scalar("SELECT COUNT(*) FROM addons WHERE image IS NOT NULL AND image != ''");
$activeAddons   = (int) $db->scalar("SELECT COUNT(*) FROM addons WHERE is_active = 1");
$inactiveAddons = $totalAddons - $activeAddons;

$stats = [
    ['label' => 'Total Addons', 'value' => $totalAddons,    'icon' => 'fa-layer-group', 'color' => 'text-brand',      'bg' => 'bg-amber-50 dark:bg-slate-800'],
    ['label' => 'With Image',   'value' => $withImg,        'icon' => 'fa-image',       'color' => 'text-blue-600',   'bg' => 'bg-blue-50 dark:bg-slate-800'],
    ['label' => 'Active',       'value' => $activeAddons,   'icon' => 'fa-check',       'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 dark:bg-slate-800'],
    ['label' => 'Inactive',     'value' => $inactiveAddons, 'icon' => 'fa-eye-slash',   'color' => 'text-slate-500',  'bg' => 'bg-slate-50 dark:bg-slate-800'],
];

$pageTitle    = 'Addons';
$pageSubtitle = $result['total'] . ' item' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/addons/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> Add Addon</a>'
    : '';

$baseUrl = url('pages/addons/index.php?search=' . urlencode($search));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Addons', 'crumbs' => ['Catalog', 'Addons'], 'actions' => $actions]); ?>

<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
  <?php foreach ($stats as $s): ?>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between gap-2">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?= e($s['label']) ?></p>
        <p class="mt-0.5 text-2xl font-extrabold text-slate-800 dark:text-white"><?= $s['value'] ?></p>
      </div>
      <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl <?= $s['bg'] ?> <?= $s['color'] ?> text-lg">
        <i class="fa-solid <?= $s['icon'] ?>"></i>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php /* Search bar */ ?>
<form method="GET" class="mb-5 flex items-center gap-3">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search addons…"
           class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
  </div>
  <button type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-search"></i> Search</button>
  <?php if ($search !== ''): ?>
    <a href="<?= e(url('pages/addons/index.php')) ?>" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">Clear</a>
  <?php endif; ?>
</form>
<?php

component('addons/addon-table', ['rows' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    /* Delete confirmation modal (posts to delete.php) */
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/addons/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteAddonId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete addon?',
        'body'   => '<p>You are about to delete <strong id="deleteAddonName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteAddon(id, name) {
    document.getElementById('deleteAddonId').value = id;
    document.getElementById('deleteAddonName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
