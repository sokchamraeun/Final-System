<?php
declare(strict_types=1);
/* Roles — list / search / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'manage_roles';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'roles';
$canManage = can('manage_roles');

$search = (string) input('search', '');
$page   = max(1, (int) input('page', 1));

$model  = new Role();
$result = $model->paginate(['search' => $search], $page);

$pageTitle    = 'Roles';
$pageSubtitle = $result['total'] . ' role' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/roles/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Role</a>'
    : '';

$baseUrl = url('pages/roles/index.php?search=' . urlencode($search));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Roles', 'crumbs' => ['Team', 'Roles'], 'actions' => $actions]);
component('roles/role-search', ['search' => $search]);
component('roles/role-table',  ['rows' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/roles/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteRoleId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete role?',
        'body'   => '<p>You are about to delete the role <strong id="deleteRoleName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteRole(id, name) {
    document.getElementById('deleteRoleId').value = id;
    document.getElementById('deleteRoleName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
