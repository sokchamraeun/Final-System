<?php
declare(strict_types=1);
/* Addon Ingredients — edit ingredient amount for an addon. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'addons';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'addon-ingredients';
$model     = new AddonIngredient();

$id              = (int) input('id', 0);
$addonIngredient = $model->find($id);
if (!$addonIngredient) {
    flash('Addon ingredient not found.', 'error');
    redirect(url('pages/addon-ingredients/index.php'));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash('Security token mismatch. Please try again.', 'error');
        redirect(url('pages/addon-ingredients/edit.php?id=' . $id));
    }

    $addonIngredient = array_merge($addonIngredient, $_POST);
    $v = new Validator($_POST);
    $v->required('amount_used')->numeric('amount_used');

    if ($v->passes()) {
        $model->save(
            (int) $addonIngredient['addon_id'],
            (int) $addonIngredient['ingredient_id'],
            (float) input('amount_used')
        );
        flash('Addon ingredient updated.', 'success');
        redirect(url('pages/addon-ingredients/index.php'));
    }
    $errors = $v->errors();
}

$addons      = (new Addon())->options();
$ingredients = (new Ingredient())->options();

$pageTitle = 'Edit Addon Ingredient';
component('layout/header', ['pageTitle' => $pageTitle]);
component('layout/main',   ['title' => 'Edit Addon Ingredient', 'crumbs' => ['Catalog', 'Addon Ingredients', 'Edit']]);
component('addon-ingredients/addon-ingredient-form', [
    'action'          => url('pages/addon-ingredients/edit.php?id=' . $id),
    'addonIngredient' => $addonIngredient,
    'addons'          => $addons,
    'ingredients'     => $ingredients,
    'errors'          => $errors,
]);
component('layout/footer');
