<?php
declare(strict_types=1);
/* Purchase Orders â€” list / search / filter / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'ingredients';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'purchase-orders';
$canManage = can('ingredients');

$search = (string) input('search', '');
$status = (string) input('status', '');
$page   = max(1, (int) input('page', 1));

$model  = new PurchaseOrder();
$result = $model->paginate(
    ['search' => $search, 'status' => $status],
    $page
);

$pageTitle    = 'Purchase Orders';
$pageSubtitle = $result['total'] . ' order' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/purchase-orders/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Purchase Order</a>'
    : '';

$baseUrl = url('pages/purchase-orders/index.php?search=' . urlencode($search) . '&status=' . urlencode($status));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Purchase Orders', 'crumbs' => ['Inventory', 'Purchase Orders'], 'actions' => $actions]);
component('purchase-orders/purchase-order-search', ['search' => $search, 'status' => $status]);
component('purchase-orders/purchase-order-table', ['purchaseOrders' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/purchase-orders/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deletePOId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete purchase order?',
        'body'   => '<p>You are about to delete <strong id="deletePONumber"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeletePO(id, poNumber) {
    document.getElementById('deletePOId').value = id;
    document.getElementById('deletePONumber').textContent = poNumber;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
