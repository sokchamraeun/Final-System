<?php
declare(strict_types=1);
/* Stands â€” list / manage. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'find_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'stands';
$canManage = can('find_orders');

$model = new Stand();
$stands = $model->all();

$pageTitle    = 'Stands';
$occupied     = count(array_filter($stands, fn($s) => $s['occupied']));
$pageSubtitle = count($stands) . ' stand' . (count($stands) === 1 ? '' : 's') . ' Â· ' . $occupied . ' occupied';

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Stands', 'crumbs' => ['POS', 'Stands']]);
component('stands/stand-table', ['stands' => $stands, 'canManage' => $canManage]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/stands/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="freeStandId" value="">
      <a href="#" onclick="closeModal('freeModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-door-open"></i> Free stand</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'freeModal',
        'title'  => 'Free stand?',
        'body'   => '<p>You are about to free <strong id="freeStandName"></strong>. Any active orders on this stand will lose their stand assignment.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmFreeStand(id, name) {
    document.getElementById('freeStandId').value = id;
    document.getElementById('freeStandName').textContent = name;
    openModal('freeModal');
  }
</script>

<?php component('layout/footer'); ?>
