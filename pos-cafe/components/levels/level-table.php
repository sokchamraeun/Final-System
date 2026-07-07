<?php
declare(strict_types=1);

$rows      = $rows ?? [];
$type      = $type ?? 'size';
$canManage = $canManage ?? false;
?>

<?php if (!$rows): ?>

<div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white py-16 dark:border-slate-700 dark:bg-slate-900">
    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
        <i class="fa-solid fa-layer-group text-3xl text-slate-400"></i>
    </div>

    <h3 class="mt-5 text-xl font-bold text-slate-700 dark:text-white">
        No Levels Found
    </h3>

    <p class="mt-2 text-sm text-slate-500">
        Create your first level to start organizing your products.
    </p>
</div>

<?php else: ?>

<div class="overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-800">

    <table class="w-full border-separate border-spacing-y-3 px-4">
        <thead>
            <tr>
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    ID
                </th>

                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Level Name
                </th>

                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Display Order
                </th>

                <?php if ($canManage): ?>
                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-slate-500">
                    Actions
                </th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($rows as $r): ?>

            <tr class="rounded-2xl bg-slate-50 transition duration-200 hover:-translate-y-0.5 hover:bg-white hover:shadow-lg dark:bg-slate-800 dark:hover:bg-slate-700">

                <td class="px-6 py-5">
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-slate-700 dark:text-white">
                        #<?= (int)$r['id'] ?>
                    </span>
                </td>

                <td class="px-6 py-5">
                    <div class="flex items-center gap-3">

                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                            <i class="fa-solid fa-layer-group text-amber-600"></i>
                        </div>

                        <div>

                            <div class="font-semibold text-slate-800 dark:text-white">
                                <?= e($r['name']) ?>
                            </div>

                            <div class="text-xs text-slate-400">
                                Product Level
                            </div>

                        </div>

                    </div>
                </td>

                <td class="px-6 py-5">

                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                        <?= (int)$r['display_order'] ?>
                    </span>

                </td>

                <?php if ($canManage): ?>

                <td class="px-6 py-5 text-right">

                    <div class="flex justify-end gap-2">

                        <a
                            href="<?= e(url('pages/levels/edit.php?type='.$type.'&id='.(int)$r['id'])) ?>"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600">

                            <i class="fa-solid fa-pen"></i>

                            Edit

                        </a>

                        <button
                            type="button"
                            onclick="confirmDeleteLevel(<?= (int)$r['id'] ?>, <?= e(json_encode($r['name'])) ?>)"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-600">

                            <i class="fa-solid fa-trash"></i>

                            Delete

                        </button>

                    </div>

                </td>

                <?php endif; ?>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php endif; ?>