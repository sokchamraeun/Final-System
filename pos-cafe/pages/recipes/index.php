<?php
declare(strict_types=1);
/* Recipes â€” view ingredients for all products. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'recipes';
$canManage = can('products');

$products = Database::instance()->all(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.category_id = p.category_id
     ORDER BY p.name"
);

$recipeModel = new Recipe();
$productRecipes = [];
foreach ($products as $p) {
    $pid = (int) $p['product_id'];
    $productRecipes[$pid] = [
        'product'    => $p,
        'ingredients' => $recipeModel->findByProduct($pid),
    ];
}

$pageTitle    = 'Product Recipes';
$pageSubtitle = count($products) . ' product' . (count($products) === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/recipes/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> Assign Ingredients</a>'
    : '';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Product Recipes', 'crumbs' => ['Catalog', 'Recipes'], 'actions' => $actions]);
component('recipes/recipe-table', ['productRecipes' => $productRecipes, 'canManage' => $canManage]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/recipes/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteRecipeId" value="">
      <input type="hidden" name="product_id" id="deleteRecipeProductId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Remove</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Remove ingredient?',
        'body'   => '<p>You are about to remove <strong id="deleteRecipeName"></strong> from <strong id="deleteRecipeProductName"></strong>.</p>',
        'footer' => $footerHtml,
    ]);
endif;
component('layout/footer');
?>

<script>
function confirmDeleteRecipe(id, productId, ingredientName, productName) {
  document.getElementById('deleteRecipeId').value = id;
  document.getElementById('deleteRecipeProductId').value = productId;
  document.getElementById('deleteRecipeName').textContent = ingredientName;
  document.getElementById('deleteRecipeProductName').textContent = productName;
  openModal('deleteModal');
}
</script>
