<?php
declare(strict_types=1);
/* ============================================================
   API: orders board — live order queue (KDS) JSON endpoints.
     GET  ?action=fetch                         (order list + announcements)
     GET  ?action=paid&id=            (not barista)
     GET  ?action=prepare&id=
     GET  ?action=complete&id=
     GET  ?action=delete&id=          (admin only)
   ============================================================ */
require __DIR__ . '/../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'view_orders';
require POS_ROOT . '/middleware/permission.php';

date_default_timezone_set('Asia/Phnom_Penh');

$now = new DateTime();
$business_date = (int) $now->format('H') < 6
    ? $now->modify('-1 day')->format('Y-m-d')
    : $now->format('Y-m-d');

$action = (string) input('action', '');

$requestedDate = (string) input('date', '');
if ($requestedDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)) {
    $business_date = $requestedDate;
}

switch ($action) {

    case 'fetch':
        $stmt = $conn->prepare("
            SELECT
                o.order_id,
                o.daily_order_no,
                o.customer_name,
                o.total,
                o.status,
                o.payment_method,
                o.order_date,
                o.token_number,
                o.employee_id,
                o.employee_name,
                ro.slug AS employee_role,
                o.prepared_by,
                o.prepared_by_role,
                o.table_number,
                cu.phone AS customer_phone,
                COUNT(rm.id) AS remake_count,
                oc.cancel_reason,
                oc.cancelled_by,
                orr.refund_reason,
                orr.refunded_by,
                (SELECT GROUP_CONCAT(reason ORDER BY remade_at ASC SEPARATOR '|||') FROM order_remakes rml WHERE rml.order_id = o.order_id) AS remake_reasons,
                oi.item_id,
                oi.product_name,
                oi.sweetness,
                oi.ice,
                oi.milk,
                oi.size_label,
                oi.quantity
            FROM orders o
            LEFT JOIN employees emp ON emp.employee_id = o.employee_id
            LEFT JOIN users u ON u.user_id = emp.user_id
            LEFT JOIN roles ro ON ro.id = u.role_id
            LEFT JOIN customers cu ON cu.customer_id = o.customer_id
            LEFT JOIN order_remakes rm ON rm.order_id = o.order_id
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN order_cancellations oc ON oc.order_id = o.order_id
            LEFT JOIN order_refunds orr ON orr.order_id = o.order_id
            WHERE o.business_date = ?
            GROUP BY o.order_id, oi.item_id, oi.product_name, oi.sweetness, oi.ice, oi.milk, oi.size_label, oi.quantity
            ORDER BY
                CASE o.status
                    WHEN 'PendingPayment' THEN 1
                    WHEN 'Paid' THEN 2
                    WHEN 'Preparing' THEN 3
                    WHEN 'Completed' THEN 4
                    WHEN 'Cancelled' THEN 5
                    WHEN 'Refunded' THEN 6
                END,
                o.order_id ASC
        ");

        $stmt->bind_param('s', $business_date);
        $stmt->execute();
        $result = $stmt->get_result();

        $map = [];
        while ($r = $result->fetch_assoc()) {
            $id = $r['order_id'];

            if (!isset($map[$id])) {
                $map[$id] = [
                    'order_id' => $id,
                    'daily_order_no' => $r['daily_order_no'],
                    'customer_name' => $r['customer_name'],
                    'phone' => $r['customer_phone'] ?? '',
                    'total' => $r['total'],
                    'status' => $r['status'],
                    'payment_method' => $r['payment_method'] ?? '',
                    'payment_status' => $r['status'] === 'PendingPayment' ? 'unpaid' : 'paid',
                    'order_date' => $r['order_date'],
                    'token_number' => $r['token_number'],
                    'employee_id' => $r['employee_id'],
                    'employee_name' => $r['employee_name'],
                    'employee_role' => $r['employee_role'] ?? '',
                    'prepared_by' => $r['prepared_by'] ?? '',
                    'prepared_by_role' => $r['prepared_by_role'] ?? '',
                    'table_number' => $r['table_number'],
                    'remake_count'         => (int) $r['remake_count'],
                    'is_remade'            => (int) $r['remake_count'] > 0 ? 1 : 0,
                    'cancel_reason'        => $r['cancel_reason'] ?? '',
                    'cancelled_by'         => $r['cancelled_by'] ?? '',
                    'refund_reason'        => $r['refund_reason'] ?? '',
                    'refunded_by'          => $r['refunded_by'] ?? '',
                    'remake_reasons' => $r['remake_reasons'] ? explode('|||', $r['remake_reasons']) : [],
                    'items' => [],
                ];
            }

            if (!empty($r['product_name'])) {
                $map[$id]['items'][] = [
                    'item_id'      => (int) $r['item_id'],
                    'product_name' => $r['product_name'],
                    'size'         => $r['size_label'],
                    'sweetness'    => $r['sweetness'],
                    'ice'          => $r['ice'],
                    'milk'         => $r['milk'],
                    'quantity'     => $r['quantity'],
                ];
            }
        }

        $_ann_data = [];
        $_ann_res2 = $conn->query("SELECT id, title, message, type FROM announcements WHERE is_active = 1 AND (expires_at IS NULL OR expires_at >= CURDATE()) ORDER BY created_at DESC");
        if ($_ann_res2) {
            foreach ($_ann_res2->fetch_all(MYSQLI_ASSOC) as $_a) {
                $_ann_data[] = ['id' => (int) $_a['id'], 'title' => $_a['title'], 'message' => $_a['message'], 'type' => $_a['type']];
            }
        }

        json_response(['orders' => array_values($map), 'announcements' => $_ann_data]);
        // no break — json_response exits

    case 'paid':
        if (($_SESSION['role'] ?? '') === 'barista') {
            json_response(['ok' => 0, 'error' => 'Unauthorized']);
        }

        $order_id = (int) input('id', 0);
        if ($order_id <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid order id']);
        }

        $conn->begin_transaction();
        try {
            $s1 = $conn->prepare("UPDATE orders SET status = 'Preparing' WHERE order_id = ?");
            $s1->bind_param('i', $order_id);
            $s1->execute();

            // Sync any pending payment records so order_payments stays consistent
            $s2 = $conn->prepare("UPDATE order_payments SET payment_status = 'paid' WHERE order_id = ? AND payment_status != 'paid'");
            $s2->bind_param('i', $order_id);
            $s2->execute();

            $conn->commit();
            json_response(['ok' => 1]);
        } catch (Exception $e) {
            $conn->rollback();
            json_response(['ok' => 0, 'error' => $e->getMessage()]);
        }
        // no break — json_response exits

    case 'prepare':
        $order_id = (int) input('id', 0);
        if ($order_id <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid order id']);
        }

        $stmt = $conn->prepare("UPDATE orders SET status = 'Preparing' WHERE order_id = ?");
        $stmt->bind_param('i', $order_id);

        if ($stmt->execute()) {
            json_response(['ok' => 1]);
        }
        json_response(['ok' => 0, 'error' => 'Failed to update status']);
        // no break — json_response exits

    case 'complete':
        $order_id = (int) input('id', 0);
        if ($order_id <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid order id']);
        }

        $conn->begin_transaction();
        try {
            // Lock order
            $stmt_check = $conn->prepare("SELECT status FROM orders WHERE order_id = ? FOR UPDATE");
            $stmt_check->bind_param('i', $order_id);
            $stmt_check->execute();
            $check_res = $stmt_check->get_result();

            if ($check_res->num_rows === 0) {
                throw new Exception('Order not found');
            }

            $order = $check_res->fetch_assoc();

            if ($order['status'] === 'Completed') {
                $conn->commit();
                json_response(['ok' => 1]);
            }

            // Stock was already deducted at order creation (confirm_order.php).
            // Deducting again here would double-consume ingredients and cause
            // "Not enough stock" errors on busy days. Only mark the order complete.
            $prepared_by      = $_SESSION['username'] ?? '';
            $prepared_by_role = $_SESSION['role']     ?? '';
            $stmt_update = $conn->prepare("UPDATE orders SET status = 'Completed', prepared_by = ?, prepared_by_role = ?, completed_at = NOW() WHERE order_id = ?");
            $stmt_update->bind_param('ssi', $prepared_by, $prepared_by_role, $order_id);
            $stmt_update->execute();

            $conn->commit();
            json_response(['ok' => 1]);
        } catch (Exception $e) {
            $conn->rollback();
            json_response(['ok' => 0, 'error' => $e->getMessage()]);
        }
        // no break — json_response exits

    case 'delete':
        $order_id = (int) input('id', 0);
        if ($order_id <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid order id']);
        }

        if (($_SESSION['role'] ?? '') !== 'admin') {
            json_response(['ok' => 0, 'error' => 'Unauthorized']);
        }

        $conn->begin_transaction();
        try {
            $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt_items->bind_param('i', $order_id);
            $stmt_items->execute();

            $stmt_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
            $stmt_order->bind_param('i', $order_id);
            $stmt_order->execute();

            $conn->commit();
            json_response(['ok' => 1]);
        } catch (Exception $e) {
            $conn->rollback();
            json_response(['ok' => 0, 'error' => $e->getMessage()]);
        }
        // no break — json_response exits

    default:
        json_response(['ok' => 0, 'error' => 'Unknown action'], 400);
}
