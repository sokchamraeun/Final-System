<?php
declare(strict_types=1);
/* Admins â€” list / search / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'admins';
$canManage = can('settings');

$search = (string) input('search', '');
$page   = max(1, (int) input('page', 1));

$users  = new User();
$result = $users->paginate(['search' => $search], $page);

$pageTitle    = 'Admins';
$pageSubtitle = $result['total'] . ' user' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/admins/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Admin</a>'
    : '';

$baseUrl = url('pages/admins/index.php?search=' . urlencode($search));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Admins', 'crumbs' => ['Settings', 'Admins'], 'actions' => $actions]);
component('admins/admin-search', ['search' => $search]);
component('admins/admin-table',  ['admins' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/admins/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteAdminId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete admin?',
        'body'   => '<p>You are about to delete <strong id="deleteAdminName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteAdmin(id, name) {
    document.getElementById('deleteAdminId').value = id;
    document.getElementById('deleteAdminName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
