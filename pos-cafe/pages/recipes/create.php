<?php
declare(strict_types=1);
/* Recipes â€” assign ingredients to a product. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'recipes';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/recipes/create.php'));
    }

    $v = new Validator($_POST);
    $v->required('product_id')->numeric('product_id')
      ->required('ingredient_id')->numeric('ingredient_id')
      ->required('amount_used')->numeric('amount_used');

    if ($v->passes()) {
        (new Recipe())->save(
            (int) input('product_id'),
            (int) input('ingredient_id'),
            (float) input('amount_used')
        );
        flash('Ingredient assigned to product.', 'success');
        redirect(url('pages/recipes/index.php'));
    }
    $errors = $v->errors();
}

$products    = Database::instance()->all("SELECT product_id, name FROM products ORDER BY name");
$ingredients = (new Ingredient())->options();

$pageTitle  = 'Assign Ingredient';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Assign Ingredient', 'crumbs' => ['Catalog', 'Recipes', 'Assign']]);
component('recipes/recipe-form', [
    'action'      => url('pages/recipes/create.php'),
    'products'    => $products,
    'ingredients' => $ingredients,
    'errors'      => $errors,
]);
component('layout/footer');
