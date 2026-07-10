<?php
declare(strict_types=1);
/* Addon Ingredients — assign an ingredient to an addon. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addon-ingredients';
$errors    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/addon-ingredients/create.php'));
    }

    $v = new Validator($_POST);
    $v->required('addon_id')->numeric('addon_id')
      ->required('ingredient_id')->numeric('ingredient_id')
      ->required('amount_used')->numeric('amount_used');

    if ($v->passes()) {
        (new AddonIngredient())->save(
            (int) input('addon_id'),
            (int) input('ingredient_id'),
            (float) input('amount_used')
        );
        flash('Ingredient assigned to addon.', 'success');
        redirect(url('pages/addon-ingredients/index.php'));
    }
    $errors = $v->errors();
}

$addons          = (new Addon())->options();
$ingredients     = (new Ingredient())->options();
$addonIngredient = ['addon_id' => (int) input('addon_id', 0)];

$pageTitle = 'Assign Ingredient';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Assign Ingredient', 'crumbs' => ['Catalog', 'Addon Ingredients', 'Assign']]);
component('addon-ingredients/addon-ingredient-form', [
    'action'          => url('pages/addon-ingredients/create.php'),
    'addonIngredient' => $addonIngredient,
    'addons'          => $addons,
    'ingredients'     => $ingredients,
    'errors'          => $errors,
]);
component('layout/footer');
