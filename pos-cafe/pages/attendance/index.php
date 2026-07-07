<?php
declare(strict_types=1);
/* Attendance â€” list / search / date filter / paginate + clock in/out. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'employees';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'attendance';

$search   = (string) input('search', '');
$dateFrom = (string) input('date_from', '');
$dateTo   = (string) input('date_to', '');
$page     = max(1, (int) input('page', 1));

$model  = new Attendance();
$result = $model->paginate(
    ['search' => $search, 'date_from' => $dateFrom, 'date_to' => $dateTo],
    $page
);

/* Handle clock in / clock out actions */
$clockAction = (string) input('action', '');
if ($clockAction === 'clock_in' && csrf_verify(input('csrf_token'))) {
    $userId   = Auth::id();
    $username = Auth::name();
    if ($model->isClockedIn($userId)) {
        flash('You are already clocked in.', 'warning');
    } else {
        $model->clockIn($userId, $username);
        flash('Clocked in successfully.', 'success');
    }
    redirect(url('pages/attendance/index.php'));
}
if ($clockAction === 'clock_out' && csrf_verify(input('csrf_token'))) {
    $userId   = Auth::id();
    $record = $model->isClockedIn($userId);
    if ($record) {
        $model->clockOut((int) $record['id']);
        flash('Clocked out successfully.', 'success');
    } else {
        flash('No active clock-in found.', 'warning');
    }
    redirect(url('pages/attendance/index.php'));
}

$currentRecord = $model->isClockedIn(Auth::id());
$isClockedIn   = $currentRecord !== null;

$pageTitle    = 'Attendance';
$pageSubtitle = $result['total'] . ' record' . ($result['total'] === 1 ? '' : 's');

$clockBtn = $isClockedIn
    ? '<a href="' . e(url('pages/attendance/index.php?action=clock_out&csrf_token=' . csrf_token())) . '" class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"><i class="fa-solid fa-right-from-bracket"></i> Clock Out</a>'
    : '<a href="' . e(url('pages/attendance/index.php?action=clock_in&csrf_token=' . csrf_token())) . '" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"><i class="fa-solid fa-right-to-bracket"></i> Clock In</a>';

$baseUrl = url('pages/attendance/index.php?search=' . urlencode($search) . '&date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo));

component('layout/header', ['pageTitle' => $pageTitle, 'pageSubtitle' => $pageSubtitle]);
component('layout/main',   ['title' => 'Attendance', 'crumbs' => ['Team', 'Attendance'], 'actions' => $clockBtn]);
component('attendance/attendance-search', ['search' => $search, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
component('attendance/attendance-table', ['records' => $result['rows']]);
component('common/pagination', ['page' => $page, 'perPage' => PER_PAGE, 'total' => $result['total'], 'baseUrl' => $baseUrl]);

component('layout/footer');
