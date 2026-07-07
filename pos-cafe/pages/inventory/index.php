<?php
declare(strict_types=1);
/* Inventory (Ingredients) — list / search / low-stock filter / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'inventory';
$canManage = can('ingredients');

$search   = (string) input('search', '');
$lowStock = (string) input('low_stock', '');
$page     = max(1, (int) input('page', 1));

$ingredient = new Ingredient();
$filters    = ['search' => $search];
if ($lowStock === '1') {
    $filters['low_stock'] = true;
}
$result = $ingredient->paginate($filters, $page, 100);

$db          = Database::instance();
$totalIng    = (int) $db->scalar("SELECT COUNT(*) FROM ingredients");
$lowStockCnt = (int) $db->scalar("SELECT COUNT(*) FROM ingredients WHERE stock_quantity <= minimum_stock");
$inStock     = $totalIng - $lowStockCnt;
$totalSupp   = (int) $db->scalar("SELECT COUNT(DISTINCT supplier_id) FROM ingredients WHERE supplier_id IS NOT NULL");

$stats = [
    ['label' => 'Total Ingredients', 'value' => $totalIng, 'icon' => 'fa-basket-shopping', 'color' => 'text-brand', 'bg' => 'bg-amber-50 dark:bg-slate-800'],
    ['label' => 'In Stock',          'value' => $inStock,  'icon' => 'fa-circle-check',    'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 dark:bg-slate-800'],
    ['label' => 'Low Stock',         'value' => $lowStockCnt, 'icon' => 'fa-triangle-exclamation', 'color' => 'text-red-500', 'bg' => 'bg-red-50 dark:bg-slate-800'],
    ['label' => 'Suppliers',         'value' => $totalSupp, 'icon' => 'fa-truck',          'color' => 'text-blue-600', 'bg' => 'bg-blue-50 dark:bg-slate-800'],
];

$pageTitle    = 'Inventory';
$pageSubtitle = $result['total'] . ' ingredient' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/inventory/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Ingredient</a>'
    : '';

$baseUrl = url('pages/inventory/index.php?search=' . urlencode($search) . '&low_stock=' . $lowStock);

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Inventory', 'crumbs' => ['Stock', 'Inventory'], 'actions' => $actions]); ?>

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

<?php component('inventory/ingredient-search', ['search' => $search, 'lowStock' => $lowStock]);
component('inventory/ingredient-table', ['rows' => $result['rows'], 'canManage' => $canManage, 'page' => $page]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    /* Delete confirmation modal (posts to delete.php) */
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/inventory/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteIngredientId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete ingredient?',
        'body'   => '<p>You are about to delete <strong id="deleteIngredientName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteIngredient(id, name) {
    document.getElementById('deleteIngredientId').value = id;
    document.getElementById('deleteIngredientName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
