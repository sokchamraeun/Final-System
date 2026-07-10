<?php
declare(strict_types=1);
/* Products — list / search / filter / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'products';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'products';
$canManage = can('products');

$search   = (string) input('search', '');
$category = (string) input('category', '');
$page     = max(1, (int) input('page', 1));

$products   = new Product();
$categories = (new Category())->active();
$result     = $products->paginate(
    ['search' => $search, 'category' => $category],
    1,
    999999
);

$db        = Database::instance();
$totalProds = (int) $db->scalar("SELECT COUNT(*) FROM products");
$totalCats  = (int) $db->scalar("SELECT COUNT(*) FROM categories");
$activeItems = (int) $db->scalar("SELECT COUNT(*) FROM products WHERE is_available = 1");
$inactiveItems = (int) $db->scalar("SELECT COUNT(*) FROM products WHERE is_available = 0");
$onPromotion  = (int) $db->scalar("SELECT COUNT(*) FROM products WHERE badge_text IS NOT NULL AND badge_text != ''");

$stats = [
    ['label' => 'Total Products', 'value' => $totalProds, 'icon' => 'fa-mug-hot', 'color' => 'text-brand', 'bg' => 'bg-amber-50 dark:bg-slate-800'],
    ['label' => 'Categories',     'value' => $totalCats,  'icon' => 'fa-tag',     'color' => 'text-blue-600', 'bg' => 'bg-blue-50 dark:bg-slate-800'],
    ['label' => 'Active Items',   'value' => $activeItems, 'icon' => 'fa-eye',    'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50 dark:bg-slate-800'],
    ['label' => 'Inactive Items', 'value' => $inactiveItems, 'icon' => 'fa-eye-slash', 'color' => 'text-red-500', 'bg' => 'bg-red-50 dark:bg-slate-800'],
    ['label' => 'On Promotion',   'value' => $onPromotion, 'icon' => 'fa-bullhorn', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50 dark:bg-slate-800'],
];

$pageTitle    = 'Products';
$pageSubtitle = $result['total'] . ' item' . ($result['total'] === 1 ? '' : 's');

$view = $_COOKIE['products_view'] ?? 'grid';

$viewToggle = '<div class="flex items-center rounded-xl border border-slate-200 bg-white p-0.5 dark:border-slate-700 dark:bg-slate-800">
  <button type="button" onclick="setProductsView(\'grid\')"
          class="grid h-8 w-8 place-items-center rounded-lg text-sm transition ' . ($view === 'grid' ? 'bg-slate-100 text-slate-800 shadow-sm dark:bg-slate-600 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300') . '"
          title="Grid view"><i class="fa-solid fa-grip"></i></button>
  <button type="button" onclick="setProductsView(\'list\')"
          class="grid h-8 w-8 place-items-center rounded-lg text-sm transition ' . ($view === 'list' ? 'bg-slate-100 text-slate-800 shadow-sm dark:bg-slate-600 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300') . '"
          title="List view"><i class="fa-solid fa-list"></i></button>
</div>';

$actions = $canManage
    ? $viewToggle . '<a href="' . e(url('pages/products/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Product</a>'
    : $viewToggle;

$baseUrl = url('pages/products/index.php?search=' . urlencode($search) . '&category=' . urlencode($category));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Products', 'crumbs' => ['Catalog', 'Products'], 'actions' => $actions]); ?>

<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
  <?php foreach ($stats as $s): ?>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
    <div class="flex items-center justify-between gap-2">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500"><?= e($s['label']) ?></p>
        <p class="mt-0.5 text-2xl font-extrabold text-slate-800 dark:text-white"><?= $s['value'] ?></p>
      </div>
      <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl <?= $s['bg'] ?> <?= $s['color'] ?> text-lg">
        <i class="fa-solid <?= $s['icon'] ?>"></i>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php component('products/product-search', ['search' => $search, 'category' => $category, 'categories' => $categories]);
component('products/product-grid',   ['products' => $result['rows'], 'canManage' => $canManage, 'view' => $view]);

if ($canManage):
    /* Delete confirmation modal (posts to delete.php) */
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/products/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteProductId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete product?',
        'body'   => '<p>You are about to delete <strong id="deleteProductName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  var CSRF = <?= json_encode(csrf_token()) ?>;

  function toggleCheckboxes(master) {
    var box = master.closest('.rounded-xl');
    if (!box) return;
    var items = box.querySelectorAll('input[type="checkbox"]');
    items.forEach(function(cb) { if (cb !== master) cb.checked = master.checked; });
  }

  function syncSelectAll(cb) {
    var box = cb.closest('.rounded-xl');
    if (!box) return;
    var all = box.querySelectorAll('input[type="checkbox"]');
    var master = all[0];
    var checked = 0;
    all.forEach(function(c) { if (c !== master && c.checked) checked++; });
    master.checked = checked === all.length - 1;
  }

  function confirmDeleteProduct(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    openModal('deleteModal');
  }

  function toggleProduct(id, btn) {
    fetch(<?= json_encode(url('api/products.php')) ?>, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=toggle&id=' + id + '&csrf_token=' + encodeURIComponent(CSRF)
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (!data.success) { window.toast(data.message || 'Error', 'error'); return; }
      var icon = btn.querySelector('i');
      if (data.is_available) { icon.className = 'fa-solid fa-eye'; window.toast('Product shown', 'success'); }
      else { icon.className = 'fa-solid fa-eye-slash'; window.toast('Product hidden', 'info'); }
    })
    .catch(function () { window.toast('Request failed', 'error'); });
  }

  function setProductsView(view) {
    document.cookie = 'products_view=' + view + '; path=/; max-age=31536000; SameSite=Lax';
    location.reload();
  }

  /* ── Edit modal ── */
  function openEditProductModal(id) {
    var body = document.getElementById('editProductBody');
    body.innerHTML = '<div class="flex items-center justify-center py-10 text-slate-400"><i class="fa-solid fa-spinner fa-spin text-xl"></i></div>';
    openModal('editProductModal');
    fetch(<?= json_encode(url('pages/products/edit.php')) ?> + '?id=' + id + '&partial=1')
      .then(function (r) { return r.text(); })
      .then(function (html) {
        body.innerHTML = html;
        var form = body.querySelector('form');
        if (form) form.addEventListener('submit', submitEditForm);
      });
  }

  function submitEditForm(e) {
    e.preventDefault();
    var form = e.target;
    var btn = form.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        if (html.indexOf('rounded-xl border border-red-200') !== -1 || html.indexOf('alert') !== -1) {
          var parser = new DOMParser();
          var doc = parser.parseFromString(html, 'text/html');
          var newForm = doc.querySelector('form');
          if (newForm) {
            document.getElementById('editProductBody').innerHTML = html;
            var f = document.querySelector('#editProductBody form');
            if (f) f.addEventListener('submit', submitEditForm);
          }
          btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check"></i> Save changes';
        } else {
          closeModal('editProductModal');
          window.toast('Product updated', 'success');
          setTimeout(function () { location.reload(); }, 600);
        }
      })
      .catch(function () {
        var f = document.querySelector('#editProductBody form');
        if (f) { var b = f.querySelector('button[type="submit"]'); if (b) { b.disabled = false; b.innerHTML = '<i class=\"fa-solid fa-check\"></i> Save changes'; } }
        window.toast('Save failed. Check console.', 'error');
      });
  }
</script>

<div id="editProductModal" class="fixed inset-0 z-[90] hidden items-start justify-center overflow-y-auto bg-black/50 p-4 pt-10 backdrop-blur-sm" onclick="if(event.target===this)closeModal('editProductModal')">
  <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl dark:bg-slate-900">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
      <h3 class="text-lg font-bold text-slate-800 dark:text-white">Edit Product</h3>
      <button type="button" onclick="closeModal('editProductModal')" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="px-6 py-5 text-sm text-slate-600 dark:text-slate-300" id="editProductBody"></div>
  </div>
</div>

<?php component('layout/footer'); ?>
