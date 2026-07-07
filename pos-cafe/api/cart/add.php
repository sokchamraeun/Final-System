<?php
declare(strict_types=1);
/* ============================================================
   API: cart — add a product to the session cart.
     POST  id, qty, sweetness, ice, milk, size, csrf_token

   Migrated from the legacy root add_to_cart.php. Thin endpoint:
   all validation, size/price resolution, line merging and totals
   live in the Cart model.
   ============================================================ */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';

if (!can('find_orders')) {
    json_response(['success' => false, 'message' => 'Not allowed', 'cart_count' => 0, 'cart_total' => null], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    json_response(['success' => false, 'message' => 'Invalid request token', 'cart_count' => 0, 'cart_total' => null], 403);
}

try {
    $totals = (new Cart())->add(
        (int) input('id', 0),
        (int) input('qty', 1),
        [
            'sweetness' => (string) input('sweetness', ''),
            'ice'       => (string) input('ice', ''),
            'milk'      => (string) input('milk', ''),
            'sugar'     => (string) input('sugar', ''),
            'size'      => (string) input('size', ''),
        ]
    );
} catch (CartException $e) {
    json_response(['success' => false, 'message' => $e->getMessage(), 'cart_count' => 0, 'cart_total' => null], $e->status());
}

json_response([
    'success'    => true,
    'message'    => 'Added to cart!',
    'cart_count' => $totals['count'],
    'cart_total' => $totals['total'],
]);
