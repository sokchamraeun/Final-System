<?php
declare(strict_types=1);

/* ============================================================
   Order model — maps to the `orders` table
   (order_id, user_id, customer_name, total, status, order_date, ...).
   Mostly read-only for the pos-cafe pages.
   ============================================================ */

final class Order
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = PER_PAGE): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = '(o.customer_name LIKE ? OR o.order_id = ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = is_numeric($filters['search']) ? (int) $filters['search'] : 0;
        }
        if (!empty($filters['status'])) {
            $where[]  = 'o.status = ?';
            $params[] = (string) $filters['status'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM orders o {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT o.*, u.username AS served_by
             FROM orders o
             LEFT JOIN users u ON u.user_id = o.user_id
             {$whereSql}
             ORDER BY o.order_date DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT o.*, u.username AS served_by
             FROM orders o
             LEFT JOIN users u ON u.user_id = o.user_id
             WHERE o.order_id = ? LIMIT 1",
            [$id]
        );
    }
}
