<?php
declare(strict_types=1);
/* ============================================================
   API: products — JSON endpoints.
     GET  ?action=list&search=&category=&page=
     GET  ?action=search&q=latte
     POST  action=toggle&id=&csrf_token=   (needs 'products')
   ============================================================ */
require __DIR__ . '/../config/app.php';
require POS_ROOT . '/middleware/auth.php';

$model  = new Product();
$action = (string) input('action', 'list');

switch ($action) {
    case 'search':
        json_response([
            'success' => true,
            'results' => $model->search((string) input('q', input('search', ''))),
        ]);
        // no break — json_response exits

    case 'toggle':
        if (!can('products')) {
            json_response(['success' => false, 'message' => 'Not allowed.'], 403);
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
            json_response(['success' => false, 'message' => 'Invalid request.'], 400);
        }
        $id = (int) input('id', 0);
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'Missing id.'], 422);
        }
        $state = $model->toggleAvailability($id);
        json_response(['success' => true, 'is_available' => $state]);

    case 'list':
    default:
        $result = $model->paginate([
            'search'   => (string) input('search', ''),
            'category' => (string) input('category', ''),
        ], max(1, (int) input('page', 1)));
        json_response([
            'success' => true,
            'total'   => $result['total'],
            'rows'    => $result['rows'],
        ]);
}
