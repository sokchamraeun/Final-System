<?php
declare(strict_types=1);
/* Employees — list / search / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'employees';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'employees';
$canManage = can('employees');

$search = (string) input('search', '');
$page   = max(1, (int) input('page', 1));

$employee = new Employee();
$result   = $employee->paginate(['search' => $search], $page);

$pageTitle    = 'Employees';
$pageSubtitle = $result['total'] . ' employee' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/employees/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Employee</a>'
    : '';

$baseUrl = url('pages/employees/index.php?search=' . urlencode($search));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Employees', 'crumbs' => ['HR', 'Employees'], 'actions' => $actions]);
component('employees/employee-search', ['search' => $search]);
component('employees/employee-table', ['rows' => $result['rows'], 'canManage' => $canManage, 'page' => $page, 'perPage' => PER_PAGE]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    /* Delete confirmation modal (posts to delete.php) */
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/employees/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteEmployeeId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete employee?',
        'body'   => '<p>You are about to delete <strong id="deleteEmployeeName"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteEmployee(id, name) {
    document.getElementById('deleteEmployeeId').value = id;
    document.getElementById('deleteEmployeeName').textContent = name;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
