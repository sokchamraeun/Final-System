<?php
declare(strict_types=1);

/* ============================================================
   StockCount model — maps to the `stock_counts` and `stock_count_items` tables.
   stock_counts:    (count_id, business_date, status, submitted_by, submitted_at, notes, created_by, created_at)
   stock_count_items: (item_id, count_id, ingredient_id, expected_qty, actual_qty, difference)
   ============================================================ */

final class StockCount
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
            $where[]  = 'sc.business_date LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[]  = 'sc.status = ?';
            $params[] = (string) $filters['status'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM stock_counts sc {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT sc.*, u.username AS submitted_by_name
             FROM stock_counts sc
             LEFT JOIN users u ON u.user_id = sc.submitted_by
             {$whereSql}
             ORDER BY sc.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        $count = $this->db->first(
            "SELECT sc.*, u.username AS submitted_by_name
             FROM stock_counts sc
             LEFT JOIN users u ON u.user_id = sc.submitted_by
             WHERE sc.count_id = ? LIMIT 1",
            [$id]
        );

        if ($count === null) {
            return null;
        }

        $count['items'] = $this->db->all(
            "SELECT sci.*, i.ingredient_name, i.unit
             FROM stock_count_items sci
             LEFT JOIN ingredients i ON i.ingredient_id = sci.ingredient_id
             WHERE sci.count_id = ?
             ORDER BY i.ingredient_name",
            [$id]
        );

        return $count;
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO stock_counts (business_date, status, notes, created_by)
             VALUES (?, 'in_progress', ?, ?)",
            [
                (string) $data['business_date'],
                (string) ($data['notes'] ?? ''),
                (int)    $data['created_by'],
            ]
        );
    }

    /** Start a new count: create the stock_count and populate items from current stock. */
    public function startCount(string $businessDate, int $createdBy, ?string $notes = null): int
    {
        $countId = $this->create([
            'business_date' => $businessDate,
            'notes'         => $notes ?? '',
            'created_by'    => $createdBy,
        ]);

        $ingredients = $this->db->all(
            "SELECT ingredient_id, stock_quantity FROM ingredients ORDER BY ingredient_name"
        );

        foreach ($ingredients as $ing) {
            $this->db->insert(
                "INSERT INTO stock_count_items (count_id, ingredient_id, expected_qty, actual_qty, difference)
                 VALUES (?, ?, ?, 0, 0)",
                [
                    $countId,
                    (int) $ing['ingredient_id'],
                    (float) $ing['stock_quantity'],
                ]
            );
        }

        return $countId;
    }

    /** Complete a count: update status, record who submitted and when. */
    public function completeCount(int $countId, int $submittedBy): int
    {
        $items = $this->db->all(
            "SELECT * FROM stock_count_items WHERE count_id = ?",
            [$countId]
        );

        foreach ($items as $item) {
            $diff = (float) $item['actual_qty'] - (float) $item['expected_qty'];
            $this->db->execute(
                "UPDATE stock_count_items SET difference = ? WHERE item_id = ?",
                [$diff, (int) $item['item_id']]
            );
        }

        return $this->db->execute(
            "UPDATE stock_counts
             SET status = 'completed', submitted_by = ?, submitted_at = NOW()
             WHERE count_id = ? AND status = 'in_progress'",
            [$submittedBy, $countId]
        );
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];

        if (array_key_exists('business_date', $data)) {
            $fields[] = 'business_date = ?';
            $params[] = (string) $data['business_date'];
        }
        if (array_key_exists('status', $data)) {
            $fields[] = 'status = ?';
            $params[] = (string) $data['status'];
        }
        if (array_key_exists('notes', $data)) {
            $fields[] = 'notes = ?';
            $params[] = (string) $data['notes'];
        }

        if ($fields === []) {
            return 0;
        }

        $params[] = $id;

        return $this->db->execute(
            "UPDATE stock_counts SET " . implode(', ', $fields) . " WHERE count_id = ?",
            $params
        );
    }

    public function delete(int $id): int
    {
        $this->db->execute("DELETE FROM stock_count_items WHERE count_id = ?", [$id]);
        return $this->db->execute("DELETE FROM stock_counts WHERE count_id = ?", [$id]);
    }
}
