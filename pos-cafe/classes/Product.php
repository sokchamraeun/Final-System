<?php
declare(strict_types=1);

/* ============================================================
   Product model — maps to the `products` table.

   Real columns (from the live schema): product_id, name, price,
   image, category (slug), category_id, description, badge_text,
   has_sizes, is_available.

   This is the reference model: copy its shape for Order, Employee,
   Inventory, etc. Every query is a prepared statement.
   ============================================================ */

final class Product
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    /**
     * Paginated / filtered list.
     *
     * @param array{search?:string,category?:string,available?:int|null} $filters
     * @return array{rows:array,total:int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = PER_PAGE): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = 'p.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['category'])) {
            $where[]  = 'p.category = ?';
            $params[] = $filters['category'];
        }
        if (isset($filters['available']) && $filters['available'] !== null) {
            $where[]  = 'p.is_available = ?';
            $params[] = (int) $filters['available'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM products p {$whereSql}",
            $params
        );

        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;
        $rows    = $this->db->all(
            "SELECT p.product_id, p.name, p.price, p.image, p.category, p.category_id,
                    p.description, p.badge_text, p.has_sizes, p.is_available,
                    c.name AS category_name,
                    GROUP_CONCAT(DISTINCT szl.name ORDER BY ps.sort_order SEPARATOR ', ') AS sizes,
                    GROUP_CONCAT(DISTINCT ice.name ORDER BY ice.id SEPARATOR ', ') AS ice_levels,
                    GROUP_CONCAT(DISTINCT sug.name ORDER BY sug.id SEPARATOR ', ') AS sugar_levels
             FROM products p
             LEFT JOIN categories c ON c.category_id = p.category_id
             LEFT JOIN product_sizes ps ON ps.product_id = p.product_id
             LEFT JOIN size_levels szl ON szl.id = ps.size_level_id
             LEFT JOIN product_ice_levels pic ON pic.product_id = p.product_id
             LEFT JOIN ice_levels ice ON ice.id = pic.ice_level_id
             LEFT JOIN product_sugar_levels psl ON psl.product_id = p.product_id
             LEFT JOIN sugar_levels sug ON sug.id = psl.sugar_level_id
             {$whereSql}
             GROUP BY p.product_id
             ORDER BY p.category, p.name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM products WHERE product_id = ? LIMIT 1",
            [$id]
        );
    }

    /** @return array<int,array> */
    public function search(string $term): array
    {
        return $this->db->all(
            "SELECT product_id, name, price, image, category
             FROM products
             WHERE name LIKE ? AND is_available = 1
             ORDER BY name
             LIMIT 20",
            ['%' . $term . '%']
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO products (name, price, image, category, category_id, description, badge_text, has_sizes, is_available)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (string) $data['name'],
                (float)  $data['price'],
                (string) ($data['image'] ?? ''),
                (string) ($data['category'] ?? ''),
                isset($data['category_id']) ? (int) $data['category_id'] : null,
                (string) ($data['description'] ?? ''),
                (string) ($data['badge_text'] ?? ''),
                (int)    ($data['has_sizes'] ?? 0),
                (int)    ($data['is_available'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE products
             SET name = ?, price = ?, image = ?, category = ?, category_id = ?,
                 description = ?, badge_text = ?, has_sizes = ?, is_available = ?
             WHERE product_id = ?",
            [
                (string) $data['name'],
                (float)  $data['price'],
                (string) ($data['image'] ?? ''),
                (string) ($data['category'] ?? ''),
                isset($data['category_id']) ? (int) $data['category_id'] : null,
                (string) ($data['description'] ?? ''),
                (string) ($data['badge_text'] ?? ''),
                (int)    ($data['has_sizes'] ?? 0),
                (int)    ($data['is_available'] ?? 1),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM products WHERE product_id = ?", [$id]);
    }

    /** Flip availability; returns the new state. */
    public function toggleAvailability(int $id): int
    {
        $this->db->execute(
            "UPDATE products SET is_available = 1 - is_available WHERE product_id = ?",
            [$id]
        );
        return (int) $this->db->scalar(
            "SELECT is_available FROM products WHERE product_id = ?",
            [$id]
        );
    }

    public function count(): int
    {
        return (int) $this->db->scalar("SELECT COUNT(*) FROM products");
    }

    /** @return array<int,array> */
    public function getSizes(int $productId): array
    {
        return $this->db->all(
            "SELECT * FROM product_sizes WHERE product_id = ? ORDER BY sort_order",
            [$productId]
        );
    }

    public function saveSizes(int $productId, array $sizeLevelIds, array $prices, array $factors): void
    {
        $this->db->execute("DELETE FROM product_sizes WHERE product_id = ?", [$productId]);
        $levels = $this->db->all("SELECT id, name FROM size_levels ORDER BY display_order");
        $nameMap = [];
        foreach ($levels as $l) {
            $nameMap[(int)$l['id']] = $l['name'];
        }
        foreach ($sizeLevelIds as $i => $slid) {
            $slid  = (int) $slid;
            if ($slid <= 0) continue;
            $code   = strtoupper(substr($nameMap[$slid] ?? '', 0, 1));
            $label  = $nameMap[$slid] ?? '';
            $price  = (float) ($prices[$i] ?? 0);
            $factor = (float) ($factors[$i] ?? 1);
            $this->db->insert(
                "INSERT INTO product_sizes (product_id, size_level_id, size_code, label, price, size_factor, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$productId, $slid, $code, $label, $price, $factor, $i]
            );
        }
    }

    /** @return array<int,int> */
    public function getSelectedSizeIds(int $productId): array
    {
        return array_map(
            fn($r) => (int) $r['size_level_id'],
            $this->db->all("SELECT size_level_id FROM product_sizes WHERE product_id = ? AND size_level_id IS NOT NULL ORDER BY sort_order", [$productId])
        );
    }

    /** @return array<int,int> */
    public function getIceLevelIds(int $productId): array
    {
        return array_map(
            fn($r) => (int) $r['ice_level_id'],
            $this->db->all("SELECT ice_level_id FROM product_ice_levels WHERE product_id = ?", [$productId])
        );
    }

    /** @return array<int,int> */
    public function getSugarLevelIds(int $productId): array
    {
        return array_map(
            fn($r) => (int) $r['sugar_level_id'],
            $this->db->all("SELECT sugar_level_id FROM product_sugar_levels WHERE product_id = ?", [$productId])
        );
    }

    /** @param int[] $ids */
    public function saveIceLevels(int $productId, array $ids): void
    {
        $this->db->execute("DELETE FROM product_ice_levels WHERE product_id = ?", [$productId]);
        foreach ($ids as $lid) {
            $lid = (int) $lid;
            if ($lid <= 0) continue;
            $this->db->insert(
                "INSERT INTO product_ice_levels (product_id, ice_level_id) VALUES (?, ?)",
                [$productId, $lid]
            );
        }
    }

    /** @param int[] $ids */
    public function saveSugarLevels(int $productId, array $ids): void
    {
        $this->db->execute("DELETE FROM product_sugar_levels WHERE product_id = ?", [$productId]);
        foreach ($ids as $lid) {
            $lid = (int) $lid;
            if ($lid <= 0) continue;
            $this->db->insert(
                "INSERT INTO product_sugar_levels (product_id, sugar_level_id) VALUES (?, ?)",
                [$productId, $lid]
            );
        }
    }

    /** @return array<int,int> */
    public function getMilkLevelIds(int $productId): array
    {
        return array_map(
            fn($r) => (int) $r['milk_level_id'],
            $this->db->all("SELECT milk_level_id FROM product_milk_levels WHERE product_id = ?", [$productId])
        );
    }

    /** @param int[] $ids */
    public function saveMilkLevels(int $productId, array $ids): void
    {
        $this->db->execute("DELETE FROM product_milk_levels WHERE product_id = ?", [$productId]);
        foreach ($ids as $lid) {
            $lid = (int) $lid;
            if ($lid <= 0) continue;
            $this->db->insert(
                "INSERT INTO product_milk_levels (product_id, milk_level_id) VALUES (?, ?)",
                [$productId, $lid]
            );
        }
    }
}
