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

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

<?php foreach ($productRecipes as $pr): ?>

<?php
$product = $pr['product'];
$ingredients = $pr['ingredients'];
?>

<div class="overflow-hidden rounded-3xl border-2 border-teal-100 bg-white shadow-sm transition-all duration-300 hover:border-teal-200 hover:shadow-lg dark:border-teal-900/40 dark:bg-slate-900">

    <!-- Header -->
    <div class="flex items-center gap-4 border-b border-teal-100 bg-white px-6 py-5 dark:border-teal-900/40">

        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-teal-50 dark:bg-teal-500/20">
            <i class="fa-solid fa-mug-hot text-xl text-teal-600 dark:text-teal-400"></i>
        </div>

        <div class="flex flex-1 items-center gap-3 rounded-xl border border-teal-100 bg-teal-50/40 px-4 py-2.5 dark:border-teal-900/40 dark:bg-teal-500/10">

            <div class="min-w-0 flex-1">

                <h2 class="truncate text-base font-bold text-slate-800 dark:text-white">
                    <?= e($product['name']) ?>
                </h2>

                <?php if (!empty($product['category_name'])): ?>

                <p class="mt-0.5 text-xs text-slate-500">
                    <?= e($product['category_name']) ?>
                </p>

                <?php endif; ?>

            </div>

        </div>

        <?php if ($canManage): ?>

        <a
            href="<?= e(url('pages/recipes/create.php?product_id='.(int)$product['product_id'])) ?>"
            class="flex shrink-0 items-center gap-1.5 whitespace-nowrap text-sm font-semibold text-teal-600 transition hover:text-teal-800 dark:text-teal-400">

            <i class="fa-solid fa-plus text-xs"></i>

            Add Ingredient

        </a>

        <?php endif; ?>

    </div>

    <!-- Ingredient List -->

    <?php if ($ingredients): ?>

    <div class="px-4 pb-4 pt-4">

        <div class="overflow-hidden rounded-2xl border border-teal-100 dark:border-teal-900/40">

            <div class="flex items-center justify-between bg-teal-50/60 px-5 py-2.5 dark:bg-teal-500/10">

                <span class="inline-flex rounded-lg border border-teal-200 bg-white px-3 py-1 text-xs font-bold text-teal-700 dark:border-teal-800 dark:bg-slate-900 dark:text-teal-300">
                    <?= count($ingredients) ?> Ingredient<?= count($ingredients) == 1 ? '' : 's' ?>
                </span>

                <?php if ($canManage): ?>
                <a href="<?= e(url('pages/recipes/edit-all.php?product_id='.(int)$product['product_id'])) ?>"
                   class="text-xs font-semibold text-teal-600 hover:text-teal-800 dark:text-teal-400">
                    Edit All
                </a>
                <?php endif; ?>

            </div>

            <table class="w-full border-collapse">

                <thead>
                    <tr class="bg-teal-800 dark:bg-teal-900">
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">
                            Ingredient
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-white">
                            Quantity
                        </th>
                        <?php if ($canManage): ?>
                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-white">
                            Actions
                        </th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-teal-50 dark:divide-teal-900/40">

                <?php foreach ($ingredients as $ingredient): ?>

                <tr class="bg-white transition hover:bg-teal-50/40 dark:bg-slate-900 dark:hover:bg-teal-500/5">

                    <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-200">
                        <?= e($ingredient['ingredient_name']) ?>
                    </td>

                    <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                        <?= e((string)$ingredient['amount_used']) ?>
                        <?= e($ingredient['unit'] ?? '') ?>
                    </td>

                    <?php if ($canManage): ?>

                    <td class="px-5 py-4">

                        <div class="flex justify-end gap-4 text-xs font-semibold">

                            <a
                                href="<?= e(url('pages/recipes/edit.php?id='.(int)$ingredient['id'])) ?>"
                                class="text-teal-600 transition hover:text-teal-800 dark:text-teal-400">

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
                                class="text-rose-500 transition hover:text-rose-700">

                                Remove

                            </button>

                        </div>

                    </td>

                    <?php endif; ?>

                </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

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