<?php
declare(strict_types=1);
/* Stock Counts â€” list / filter / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stock';
$canManage = can('ingredients');

$search = (string) input('search', '');
$status = (string) input('status', '');
$page   = max(1, (int) input('page', 1));

$result = (new StockCount())->paginate(
    ['search' => $search, 'status' => $status],
    $page
);

$pageTitle    = 'Stock Counts';
$pageSubtitle = $result['total'] . ' count' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/stock/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Count</a>'
    : '';

$baseUrl = url('pages/stock/index.php?search=' . urlencode($search) . '&status=' . urlencode($status));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Stock Counts', 'crumbs' => ['Inventory', 'Stock Counts'], 'actions' => $actions]);
component('stock/stock-table', ['rows' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/stock/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteStockId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete stock count?',
        'body'   => '<p>You are about to delete stock count <strong id="deleteStockLabel"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
component('layout/footer');
?>

<script>
function confirmDeleteStock(id, label) {
  document.getElementById('deleteStockId').value = id;
  document.getElementById('deleteStockLabel').textContent = label;
  openModal('deleteModal');
}
</script>
