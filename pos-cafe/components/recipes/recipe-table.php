<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Product Recipe Cards (Teal Theme)
|--------------------------------------------------------------------------
*/

$productRecipes = $productRecipes ?? [];
$canManage      = $canManage ?? false;

if (!$productRecipes) {
    component('common/empty-state', [
        'icon'    => 'fa-utensils',
        'title'   => 'No Product Recipes',
        'message' => 'Products need ingredients assigned to track inventory and costs.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/recipes/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition">
                    <i class="fa-solid fa-plus"></i>
                    Assign Ingredients
               </a>'
            : '',
    ]);
    return;
}
?>

<div class="space-y-6">

<?php foreach ($productRecipes as $pr): ?>

<?php
$product = $pr['product'];
$ingredients = $pr['ingredients'];
?>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-700 dark:bg-slate-900">

    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-5 dark:border-slate-700 dark:bg-slate-800">

        <div class="flex items-center gap-4">

            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-100 dark:bg-teal-500/20">
                <i class="fa-solid fa-mug-hot text-xl text-teal-600 dark:text-teal-400"></i>
            </div>

            <div>

                <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                    <?= e($product['name']) ?>
                </h2>

                <?php if (!empty($product['category_name'])): ?>

                <p class="mt-1 text-sm text-slate-500">
                    <?= e($product['category_name']) ?>
                </p>

                <?php endif; ?>

            </div>

        </div>

        <span class="rounded-full bg-teal-100 px-4 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">

            <?= count($ingredients) ?>

            Ingredient<?= count($ingredients) == 1 ? '' : 's' ?>

        </span>

    </div>

    <!-- Ingredient List -->

    <?php if ($ingredients): ?>

    <div class="divide-y divide-slate-100 dark:divide-slate-700">

        <?php foreach ($ingredients as $ingredient): ?>

        <div class="flex items-center justify-between px-6 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-800">

            <div>

                <div class="font-semibold text-slate-800 dark:text-slate-200">
                    <?= e($ingredient['ingredient_name']) ?>
                </div>

                <div class="mt-1 text-xs text-slate-500">

                    Recipe Ingredient

                </div>

            </div>

            <div class="flex items-center gap-6">

                <span class="rounded-full bg-teal-50 px-4 py-1 text-sm font-semibold text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">

                    <?= e((string)$ingredient['amount_used']) ?>

                    <?= e($ingredient['unit'] ?? '') ?>

                </span>

                <?php if ($canManage): ?>

                <div class="flex gap-2">

                    <a
                        href="<?= e(url('pages/recipes/edit.php?id='.(int)$ingredient['id'])) ?>"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-teal-600">

                        <i class="fa-solid fa-pen"></i>

                        Edit

                    </a>

                    <button
                        type="button"
                        onclick="confirmDeleteRecipe(
                            <?= (int)$ingredient['id'] ?>,
                            <?= (int)$product['product_id'] ?>,
                            <?= e(json_encode($ingredient['ingredient_name'])) ?>,
                            <?= e(json_encode($product['name'])) ?>
                        )"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-rose-600">

                        <i class="fa-solid fa-trash"></i>

                        Remove

                    </button>

                </div>

                <?php endif; ?>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <?php else: ?>

    <div class="px-6 py-10 text-center">

        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">

            <i class="fa-solid fa-utensils text-2xl text-slate-400"></i>

        </div>

        <h3 class="font-semibold text-slate-700 dark:text-slate-300">

            No Ingredients Assigned

        </h3>

        <p class="mt-2 text-sm text-slate-500">

            This product doesn't have any recipe ingredients yet.

        </p>

        <?php if ($canManage): ?>

        <a
            href="<?= e(url('pages/recipes/create.php')) ?>"
            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">

            <i class="fa-solid fa-plus"></i>

            Add Ingredients

        </a>

        <?php endif; ?>

    </div>

    <?php endif; ?>

</div>

<?php endforeach; ?>

</div>