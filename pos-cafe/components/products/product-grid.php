<?php
declare(strict_types=1);
/* Product grid. component('products/product-grid', ['products' => $rows, 'canManage' => can('products')]); */
$products  = $products  ?? [];
$canManage = $canManage ?? false;
$view      = $view      ?? 'grid';

if (!$products) {
    component('common/empty-state', [
        'icon'    => 'fa-mug-hot',
        'title'   => 'No products found',
        'message' => 'Try a different search or add a new product.',
        'action'  => $canManage
            ? '<a href="' . e(url('pages/products/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Product</a>'
            : '',
    ]);
    return;
}
?>
<?php if ($view === 'list'): ?>
<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
  <table class="w-full">
    <thead>
      <tr class="border-b border-slate-100 bg-slate-50/50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400">
        <th class="w-10 px-4 py-3 text-center">No.</th>
        <th class="px-3 py-3">Product</th>
        <th class="px-2 py-3">Category</th>
        <th class="px-4 py-3">Sizes</th>
        <th class="px-4 py-3">Addon</th>
        <th class="px-4 py-3">Milk</th>
        <th class="px-4 py-3">Sugar</th>
        <th class="px-4 py-3">Ice</th>
        <th class="px-4 py-3">Status</th>
        <th class="px-4 py-3 text-center">Top Seller</th>
        <th class="px-4 py-3">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; foreach ($products as $p): ?>
        <?php component('products/product-card-list', ['p' => $p, 'canManage' => $canManage, 'no' => $no]); ?>
      <?php $no++; endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
  <?php foreach ($products as $p): ?>
    <?php component('products/product-card', ['p' => $p, 'canManage' => $canManage]); ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>
