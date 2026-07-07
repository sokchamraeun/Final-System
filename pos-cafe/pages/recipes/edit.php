<?php
declare(strict_types=1);
/* Recipes â€” edit ingredient amount for a product recipe. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'recipes';
$model     = new Recipe();

$id = (int) input('id', 0);
$recipe = $model->find($id);
if (!$recipe) {
    flash('Recipe not found.', 'error');
    redirect(url('pages/recipes/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/recipes/edit.php?id=' . $id));
    }

    $recipe = array_merge($recipe, $_POST);
    $v = new Validator($_POST);
    $v->required('amount_used')->numeric('amount_used');

    if ($v->passes()) {
        $model->save(
            (int) $recipe['product_id'],
            (int) $recipe['ingredient_id'],
            (float) input('amount_used')
        );
        flash('Recipe updated.', 'success');
        redirect(url('pages/recipes/index.php'));
    }
    $errors = $v->errors();
}

$products    = Database::instance()->all("SELECT product_id, name FROM products ORDER BY name");
$ingredients = (new Ingredient())->options();

$pageTitle  = 'Edit Recipe';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Recipe', 'crumbs' => ['Catalog', 'Recipes', 'Edit']]);
component('recipes/recipe-form', [
    'action'      => url('pages/recipes/edit.php?id=' . $id),
    'recipe'      => $recipe,
    'products'    => $products,
    'ingredients' => $ingredients,
    'errors'      => $errors,
]);
component('layout/footer');
