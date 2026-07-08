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
                (SELECT COUNT(*) FROM order_payments op2 WHERE op2.order_id = o.order_id AND op2.payment_status = 'paid') AS has_paid_payment,
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
            ORDER BY o.order_id DESC
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
                    'payment_status' => ((int)($r['has_paid_payment'] ?? 0) > 0) ? 'paid' : 'unpaid',
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

        $method = (string) input('method', 'cash');
        $allowed = ['cash', 'bakong', 'riel', 'paylater'];
        if (!in_array($method, $allowed)) $method = 'cash';

        $conn->begin_transaction();
        try {
            $s1 = $conn->prepare("UPDATE orders SET status = 'Preparing', payment_method = ? WHERE order_id = ?");
            $s1->bind_param('si', $method, $order_id);
            $s1->execute();

            // Upsert order_payments record
            $s3 = $conn->prepare("SELECT payment_id FROM order_payments WHERE order_id = ? LIMIT 1");
            $s3->bind_param('i', $order_id);
            $s3->execute();
            $_pay_existing = $s3->get_result()->fetch_assoc();

            if ($_pay_existing) {
                $s4 = $conn->prepare("UPDATE order_payments SET payment_method = ?, payment_status = 'paid' WHERE order_id = ?");
                $s4->bind_param('si', $method, $order_id);
            } else {
                $s4 = $conn->prepare("INSERT INTO order_payments (order_id, payment_method, amount, payment_status, paid_at) VALUES (?, ?, 0, 'paid', NOW())");
                $s4->bind_param('si', $order_id, $method);
            }
            $s4->execute();

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

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE orders SET status = 'Preparing', payment_method = COALESCE(NULLIF(payment_method, ''), 'paylater') WHERE order_id = ?");
            $stmt->bind_param('i', $order_id);
            $stmt->execute();

            // Create a pending payment record so order stays unpaid
            $sp = $conn->prepare("SELECT payment_id FROM order_payments WHERE order_id = ? AND payment_method = 'paylater' LIMIT 1");
            $sp->bind_param('i', $order_id);
            $sp->execute();
            if (!$sp->get_result()->fetch_assoc()) {
                $si = $conn->prepare("INSERT INTO order_payments (order_id, payment_method, amount, payment_status) VALUES (?, 'paylater', 0, 'pending')");
                $si->bind_param('i', $order_id);
                $si->execute();
            }

            $conn->commit();
            json_response(['ok' => 1]);
        } catch (Exception $e) {
            $conn->rollback();
            json_response(['ok' => 0, 'error' => $e->getMessage()]);
        }
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

    case 'gen_bakong_qr':
        $order_id = (int) input('id', 0);
        if ($order_id <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid order id']);
        }

        $oq = $conn->prepare("SELECT order_id, total FROM orders WHERE order_id = ?");
        $oq->bind_param('i', $order_id);
        $oq->execute();
        $order = $oq->get_result()->fetch_assoc();
        if (!$order) {
            json_response(['ok' => 0, 'error' => 'Order not found']);
        }

        $amount = (float) $order['total'];
        if ($amount <= 0) {
            json_response(['ok' => 0, 'error' => 'Invalid amount']);
        }

        try {
            require __DIR__ . '/../../bakong-khqr-php-main/vendor/autoload.php';
            $bakCfg = require __DIR__ . '/../../bakong_config.php';
            $bi = new \KHQR\Models\IndividualInfo(
                bakongAccountID: $bakCfg['bakong_id'],
                merchantName: $bakCfg['merchant_name'],
                merchantCity: $bakCfg['merchant_city'],
                currency: $bakCfg['currency'],
                amount: $amount,
                billNumber: 'ORDER_' . $order_id,
                storeLabel: 'ObsidianCafe',
                terminalLabel: 'POS1',
                mobileNumber: $bakCfg['mobile_number'],
                expirationTimestamp: strval((time() + 15 * 60) * 1000)
            );
            $bakResp = \KHQR\BakongKHQR::generateIndividual($bi);
            if (($bakResp->status['code'] ?? 1) === 0 && !empty($bakResp->data['qr']) && !empty($bakResp->data['md5'])) {
                $qrString = $bakResp->data['qr'];
                $qrMd5    = $bakResp->data['md5'];
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=' . urlencode($qrString);

                $conn->begin_transaction();
                try {
                    $stmtM = $conn->prepare("UPDATE orders SET bakong_md5 = ? WHERE order_id = ?");
                    $stmtM->bind_param("si", $qrMd5, $order_id);
                    $stmtM->execute();

                    // Ensure order_payments record exists for Bakong
                    $sp = $conn->prepare("SELECT payment_id FROM order_payments WHERE order_id = ? AND payment_method = 'bakong' LIMIT 1");
                    $sp->bind_param('i', $order_id);
                    $sp->execute();
                    if (!$sp->get_result()->fetch_assoc()) {
                        $si = $conn->prepare("INSERT INTO order_payments (order_id, payment_method, amount, payment_status) VALUES (?, 'bakong', ?, 'pending')");
                        $si->bind_param('id', $order_id, $amount);
                        $si->execute();
                    }

                    $conn->commit();
                    json_response(['ok' => 1, 'qr_url' => $qrUrl, 'md5' => $qrMd5]);
                } catch (Exception $e) {
                    $conn->rollback();
                    json_response(['ok' => 0, 'error' => 'DB error: ' . $e->getMessage()]);
                }
            } else {
                json_response(['ok' => 0, 'error' => 'QR generation failed']);
            }
        } catch (\Exception $e) {
            json_response(['ok' => 0, 'error' => 'QR generation error: ' . $e->getMessage()]);
        }
        // no break — json_response exits

    default:
        json_response(['ok' => 0, 'error' => 'Unknown action'], 400);
}
