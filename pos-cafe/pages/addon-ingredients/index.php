<?php
declare(strict_types=1);
/* Addon Ingredients — view/manage ingredient composition for all addons. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addon-ingredients';
$canManage = can('addons');

$search = (string) input('search', '');

$db     = Database::instance();
$where  = $search !== '' ? 'WHERE name LIKE ?' : '';
$params = $search !== '' ? ['%' . $search . '%'] : [];
$addons = $db->all("SELECT * FROM addons {$where} ORDER BY name", $params);

$aiModel = new AddonIngredient();
$addonIngredients = [];
foreach ($addons as $a) {
    $aid = (int) $a['addon_id'];
    $addonIngredients[$aid] = [
        'addon'       => $a,
        'ingredients' => $aiModel->findByAddon($aid),
    ];
}

$totalRecords    = (int) $db->scalar("SELECT COUNT(*) FROM addon_ingredients");
$withIngredients = (int) $db->scalar("SELECT COUNT(DISTINCT addon_id) FROM addon_ingredients");
$allAddons       = (int) $db->scalar("SELECT COUNT(*) FROM addons");

$stats = [
    ['label' => 'Total Records',    'value' => $totalRecords,    'icon' => 'fa-clipboard-list', 'color' => 'text-blue-600',    'bg' => 'bg-blue-50 dark:bg-slate-800'],
    ['label' => 'With Ingredients', 'value' => $withIngredients, 'icon' => 'fa-circle-check',   'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 dark:bg-slate-800'],
    ['label' => 'All Addons',       'value' => $allAddons,       'icon' => 'fa-cubes',          'color' => 'text-slate-500',   'bg' => 'bg-slate-50 dark:bg-slate-800'],
];

$pageTitle    = 'Addon Ingredients';
$pageSubtitle = 'Manage addon composition';

$actions = $canManage
    ? '<a href="' . e(url('pages/addon-ingredients/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> Add Ingredient</a>'
    : '';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Addon Ingredients', 'crumbs' => ['Catalog', 'Addon Ingredients'], 'actions' => $actions]); ?>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
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

<form method="GET" class="mb-5 flex items-center gap-3">
  <div class="relative flex-1">
    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search addons…"
           class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand/30 dark:border-slate-700 dark:bg-slate-800">
  </div>
  <button type="submit" class="rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-search"></i> Search</button>
  <?php if ($search !== ''): ?>
    <a href="<?= e(url('pages/addon-ingredients/index.php')) ?>" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">Clear</a>
  <?php endif; ?>
</form>
<?php

component('addon-ingredients/addon-ingredient-table', ['addonIngredients' => $addonIngredients, 'canManage' => $canManage]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/addon-ingredients/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteAIId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Remove</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Remove ingredient?',
        'body'   => '<p>You are about to remove <strong id="deleteAIName"></strong> from <strong id="deleteAIAddonName"></strong>.</p>',
        'footer' => $footerHtml,
    ]);
endif;
component('layout/footer');
?>

<script>
function confirmDeleteAI(id, ingredientName, addonName) {
  document.getElementById('deleteAIId').value = id;
  document.getElementById('deleteAIName').textContent = ingredientName;
  document.getElementById('deleteAIAddonName').textContent = addonName;
  openModal('deleteModal');
}
</script>
