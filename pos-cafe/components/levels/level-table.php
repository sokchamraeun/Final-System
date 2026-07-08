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

<div class="overflow-hidden rounded-3xl border border-amber-100 bg-white shadow-sm dark:border-amber-900/30 dark:bg-slate-900">

    <table class="w-full border-collapse">
        <thead>
            <tr class="border-b border-amber-100 bg-amber-50/60 dark:border-amber-900/30 dark:bg-amber-500/10">
                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    #ID
                </th>

                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    Level Name
                </th>

                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    Display Order
                </th>

                <?php if ($canManage): ?>
                <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    Actions
                </th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($rows as $r): ?>

            <tr class="border-b border-slate-100 transition hover:bg-amber-50/40 dark:border-slate-800 dark:hover:bg-amber-500/5">

                <td class="px-6 py-5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-xs font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                        <?= str_pad((string)(int)$r['id'], 2, '0', STR_PAD_LEFT) ?>
                    </span>
                </td>

                <td class="px-6 py-5">
                    <div class="font-semibold text-slate-800 dark:text-white">
                        <?= e($r['name']) ?>
                    </div>
                </td>

                <td class="px-6 py-5">
                    <span class="inline-flex rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-500/20 dark:text-orange-300">
                        <?= (int)$r['display_order'] ?>
                    </span>
                </td>

                <?php if ($canManage): ?>

                <td class="px-6 py-5">

                    <div class="inline-flex items-center gap-0.5 rounded-full border border-amber-100 bg-amber-50/60 p-1 shadow-sm dark:border-amber-900/30 dark:bg-amber-500/10 float-right">

                        <a
                            href="<?= e(url('pages/levels/edit.php?type='.$type.'&id='.(int)$r['id'])) ?>"
                            title="Edit"
                            class="group flex h-8 w-8 items-center justify-center rounded-full text-amber-600 transition duration-200 hover:bg-amber-500 hover:text-white hover:shadow-md hover:shadow-amber-500/40 active:scale-90 dark:text-amber-400">

                            <i class="fa-solid fa-pen text-xs transition-transform duration-200 group-hover:rotate-6"></i>

                        </a>

                        <span class="h-4 w-px bg-amber-200 dark:bg-amber-800"></span>

                        <button
                            type="button"
                            onclick="confirmDeleteLevel(<?= (int)$r['id'] ?>, <?= e(json_encode($r['name'])) ?>)"
                            title="Delete"
                            class="group flex h-8 w-8 items-center justify-center rounded-full text-red-500 transition duration-200 hover:bg-red-500 hover:text-white hover:shadow-md hover:shadow-red-500/40 active:scale-90">

                            <i class="fa-solid fa-trash text-xs transition-transform duration-200 group-hover:scale-110"></i>

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