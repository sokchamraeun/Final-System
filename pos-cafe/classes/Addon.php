<?php
declare(strict_types=1);

/* ============================================================
   Addon model — maps to the `addons` table
   (addon_id, name, price, image, is_active, created_at).
   ============================================================ */

final class Addon
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
            $where[]  = 'name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar("SELECT COUNT(*) FROM addons {$whereSql}", $params);

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT * FROM addons {$whereSql} ORDER BY name LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    /** @return array<int,array> All addons, unpaginated. */
    public function all(): array
    {
        return $this->db->all("SELECT * FROM addons ORDER BY name");
    }

    public function find(int $id): ?array
    {
        return $this->db->first("SELECT * FROM addons WHERE addon_id = ? LIMIT 1", [$id]);
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO addons (name, price, image, is_active) VALUES (?, ?, ?, ?)",
            [
                (string) $data['name'],
                (float)  ($data['price'] ?? 0),
                (string) ($data['image'] ?? ''),
                !empty($data['is_active']) ? 1 : 0,
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE addons SET name = ?, price = ?, image = ?, is_active = ? WHERE addon_id = ?",
            [
                (string) $data['name'],
                (float)  ($data['price'] ?? 0),
                (string) ($data['image'] ?? ''),
                !empty($data['is_active']) ? 1 : 0,
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM addons WHERE addon_id = ?", [$id]);
    }

    /** @return array<int,string> addon_id => name, handy for dropdowns. */
    public function options(): array
    {
        $rows = $this->db->all("SELECT addon_id, name FROM addons ORDER BY name");
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['addon_id']] = (string) $row['name'];
        }
        return $out;
    }
}
