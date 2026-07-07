<?php
declare(strict_types=1);
/* Customize Levels — manage size, ice, and sugar levels. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_levels';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'levels';
$canManage = can('manage_levels');

$type   = (string) input('type', 'size');
$search = (string) input('search', '');
$page   = max(1, (int) input('page', 1));

$tables = [
    'size'  => ['table' => 'size_levels',  'label' => 'Size Levels',  'icon' => 'fa-arrows-up-down'],
    'ice'   => ['table' => 'ice_levels',   'label' => 'Ice Levels',   'icon' => 'fa-snowflake'],
    'sugar' => ['table' => 'sugar_levels', 'label' => 'Sugar Levels', 'icon' => 'fa-cube'],
    'milk'  => ['table' => 'milk_levels',  'label' => 'Milk Levels',  'icon' => 'fa-cow'],
];

if (!isset($tables[$type])) {
    $type = 'size';
}

$tableName = $tables[$type]['table'];
$typeLabel = $tables[$type]['label'];

$level = new Level($tableName);
$result = $level->paginate(['search' => $search], $page);

$pageTitle    = $typeLabel;
$pageSubtitle = $result['total'] . ' item' . ($result['total'] === 1 ? '' : 's');

$createUrl = url('pages/levels/create.php?type=' . $type);
$actions = $canManage
    ? '<a href="' . e($createUrl) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New</a>'
    : '';

$baseUrl = url('pages/levels/index.php?type=' . $type . '&search=' . urlencode($search));

component('layout/header', ['pageTitle' => 'Customize Levels', 'pageSubtitle' => $typeLabel]);
component('layout/main',   ['title' => 'Customize Levels', 'crumbs' => ['Catalog', 'Customize Levels'], 'actions' => $actions]);
?>

<!-- Type tabs -->
<div class="mb-6 flex gap-2">
  <?php foreach ($tables as $key => $info): ?>
    <a href="<?= e(url('pages/levels/index.php?type=' . $key)) ?>"
       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition <?= $key === $type ? 'bg-brand text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300' ?>">
      <i class="fa-solid <?= $info['icon'] ?>"></i> <?= e($info['label']) ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- Search -->
<form method="GET" class="mb-5 flex items-center gap-3">
  <input type="hidden" name="type" value="<?= e($type) ?>">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search…"
           class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
  </div>
  <button type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-search"></i> Search</button>
  <?php if ($search !== ''): ?>
    <a href="<?= e(url('pages/levels/index.php?type=' . $type)) ?>" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">Clear</a>
  <?php endif; ?>
</form>

<?php
component('levels/level-table', ['rows' => $result['rows'], 'type' => $type, 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/levels/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="type" id="deleteLevelType" value="<?= e($type) ?>">
      <input type="hidden" name="id" id="deleteLevelId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete level?',
        'body'   => '<p>You are about to delete <strong id="deleteLevelName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteLevel(id, name) {
    document.getElementById('deleteLevelId').value = id;
    document.getElementById('deleteLevelName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
