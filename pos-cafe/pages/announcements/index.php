<?php
declare(strict_types=1);
/* Announcements â€” list / search / filter / paginate. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'settings';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'announcements';
$canManage = can('settings');

$search   = (string) input('search', '');
$type     = (string) input('type', '');
$isActive = (string) input('is_active', '');
$page     = max(1, (int) input('page', 1));

$model  = new Announcement();
$result = $model->paginate(
    ['search' => $search, 'type' => $type, 'is_active' => $isActive === '' ? null : (int) $isActive],
    $page
);

$pageTitle    = 'Announcements';
$pageSubtitle = $result['total'] . ' item' . ($result['total'] === 1 ? '' : 's');

$actions = $canManage
    ? '<a href="' . e(url('pages/announcements/create.php')) . '" class="inline-flex items-center gap-2 rounded-xl bg-brand px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-600"><i class="fa-solid fa-plus"></i> New Announcement</a>'
    : '';

$baseUrl = url('pages/announcements/index.php?search=' . urlencode($search) . '&type=' . urlencode($type) . '&is_active=' . urlencode($isActive));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Announcements', 'crumbs' => ['Settings', 'Announcements'], 'actions' => $actions]);
component('announcements/announcement-search', ['search' => $search, 'type' => $type, 'isActive' => $isActive, 'showAll' => true]);
component('announcements/announcement-table', ['announcements' => $result['rows'], 'canManage' => $canManage]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

if ($canManage):
    ob_start(); ?>
    <form method="POST" action="<?= e(url('pages/announcements/delete.php')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="deleteAnnouncementId" value="">
      <a href="#" onclick="closeModal('deleteModal');return false;" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200">Cancel</a>
      <button type="submit" class="rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600"><i class="fa-solid fa-trash-can"></i> Delete</button>
    </form>
    <?php $footerHtml = ob_get_clean();
    component('common/modal', [
        'id'     => 'deleteModal',
        'title'  => 'Delete announcement?',
        'body'   => '<p>You are about to delete <strong id="deleteAnnouncementTitle"></strong>. This cannot be undone.</p>',
        'footer' => $footerHtml,
    ]);
endif;
?>

<script>
  function confirmDeleteAnnouncement(id, title) {
    document.getElementById('deleteAnnouncementId').value = id;
    document.getElementById('deleteAnnouncementTitle').textContent = title;
    openModal('deleteModal');
  }
</script>

<?php component('layout/footer'); ?>
