<?php
declare(strict_types=1);

/* ============================================================
   PurchaseOrder model — maps to the `purchase_orders` table
   (po_id, po_number, supplier_id, status, notes, total_cost,
    ordered_at, received_at, created_by, created_at).
   ============================================================ */

final class PurchaseOrder
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
            $where[]  = '(po.po_number LIKE ? OR s.name LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'po.status = ?';
            $params[] = (string) $filters['status'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM purchase_orders po
             LEFT JOIN suppliers s ON s.supplier_id = po.supplier_id
             {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT po.*, s.name AS supplier_name
             FROM purchase_orders po
             LEFT JOIN suppliers s ON s.supplier_id = po.supplier_id
             {$whereSql}
             ORDER BY po.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT po.*, s.name AS supplier_name
             FROM purchase_orders po
             LEFT JOIN suppliers s ON s.supplier_id = po.supplier_id
             WHERE po.po_id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO purchase_orders
                (po_number, supplier_id, status, notes, total_cost, ordered_at, received_at, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (string) $data['po_number'],
                (int)    $data['supplier_id'],
                (string) ($data['status'] ?? 'pending'),
                (string) ($data['notes'] ?? ''),
                (float)  ($data['total_cost'] ?? 0),
                !empty($data['ordered_at']) ? (string) $data['ordered_at'] : null,
                !empty($data['received_at']) ? (string) $data['received_at'] : null,
                (int)    $data['created_by'],
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE purchase_orders
             SET po_number = ?, supplier_id = ?, status = ?, notes = ?,
                 total_cost = ?, ordered_at = ?, received_at = ?
             WHERE po_id = ?",
            [
                (string) $data['po_number'],
                (int)    $data['supplier_id'],
                (string) ($data['status'] ?? 'pending'),
                (string) ($data['notes'] ?? ''),
                (float)  ($data['total_cost'] ?? 0),
                !empty($data['ordered_at']) ? (string) $data['ordered_at'] : null,
                !empty($data['received_at']) ? (string) $data['received_at'] : null,
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM purchase_orders WHERE po_id = ?", [$id]);
    }
}
