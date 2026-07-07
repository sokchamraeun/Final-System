<?php
declare(strict_types=1);

/* ============================================================
   Ingredient model — maps to the `ingredients` table
   (ingredient_id, ingredient_name, unit, stock_quantity, minimum_stock,
    cost_price, purchase_qty, cost_per_unit, supplier_id).
   ============================================================ */

final class Ingredient
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
            $where[]  = 'i.ingredient_name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['low_stock'])) {
            $where[] = 'i.stock_quantity <= i.minimum_stock';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM ingredients i {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT i.*, s.name AS supplier_name
             FROM ingredients i
             LEFT JOIN suppliers s ON s.supplier_id = i.supplier_id
             {$whereSql}
             ORDER BY i.ingredient_name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT i.*, s.name AS supplier_name
             FROM ingredients i
             LEFT JOIN suppliers s ON s.supplier_id = i.supplier_id
             WHERE i.ingredient_id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO ingredients
                (ingredient_name, unit, stock_quantity, minimum_stock,
                 cost_price, purchase_qty, cost_per_unit, supplier_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (string) $data['ingredient_name'],
                (string) ($data['unit'] ?? ''),
                (float)  ($data['stock_quantity'] ?? 0),
                (float)  ($data['minimum_stock'] ?? 0),
                (float)  ($data['cost_price'] ?? 0),
                (float)  ($data['purchase_qty'] ?? 0),
                (float)  ($data['cost_per_unit'] ?? 0),
                isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE ingredients
             SET ingredient_name = ?, unit = ?, stock_quantity = ?, minimum_stock = ?,
                 cost_price = ?, purchase_qty = ?, cost_per_unit = ?, supplier_id = ?
             WHERE ingredient_id = ?",
            [
                (string) $data['ingredient_name'],
                (string) ($data['unit'] ?? ''),
                (float)  ($data['stock_quantity'] ?? 0),
                (float)  ($data['minimum_stock'] ?? 0),
                (float)  ($data['cost_price'] ?? 0),
                (float)  ($data['purchase_qty'] ?? 0),
                (float)  ($data['cost_per_unit'] ?? 0),
                isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM ingredients WHERE ingredient_id = ?", [$id]);
    }

    public function lowStock(): array
    {
        return $this->db->all(
            "SELECT i.*, s.name AS supplier_name
             FROM ingredients i
             LEFT JOIN suppliers s ON s.supplier_id = i.supplier_id
             WHERE i.stock_quantity <= i.minimum_stock
             ORDER BY (i.minimum_stock - i.stock_quantity) DESC"
        );
    }

    public function refill(int $id, float $quantity): int
    {
        return $this->db->execute(
            "UPDATE ingredients
             SET stock_quantity = stock_quantity + ?,
                 purchase_qty = ?
             WHERE ingredient_id = ?",
            [$quantity, $quantity, $id]
        );
    }

    /** @return array<int,string> */
    public function options(): array
    {
        $rows = $this->db->all(
            "SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_name"
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['ingredient_id']] = (string) $row['ingredient_name'];
        }
        return $out;
    }
}
