<?php
declare(strict_types=1);

/* ============================================================
   Role model — maps to the `roles` table
   (id, slug, name, icon, color, description, is_system, created_at).
   ============================================================ */

final class Role
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
            $where[]  = 'r.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM roles r {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT r.*
             FROM roles r
             {$whereSql}
             ORDER BY r.name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM roles WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function findByName(string $name): ?array
    {
        return $this->db->first(
            "SELECT * FROM roles WHERE name = ? LIMIT 1",
            [$name]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->first(
            "SELECT * FROM roles WHERE slug = ? LIMIT 1",
            [$slug]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO roles (slug, name, icon, color, description, is_system)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string) $data['slug'],
                (string) $data['name'],
                (string) ($data['icon'] ?? ''),
                (string) ($data['color'] ?? ''),
                (string) ($data['description'] ?? ''),
                (int)    ($data['is_system'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE roles
             SET slug = ?, name = ?, icon = ?, color = ?, description = ?, is_system = ?
             WHERE id = ?",
            [
                (string) $data['slug'],
                (string) $data['name'],
                (string) ($data['icon'] ?? ''),
                (string) ($data['color'] ?? ''),
                (string) ($data['description'] ?? ''),
                (int)    ($data['is_system'] ?? 0),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM roles WHERE id = ?", [$id]);
    }

    /** @return array<int,string> */
    public function options(): array
    {
        $rows = $this->db->all(
            "SELECT id, name FROM roles ORDER BY name"
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['id']] = (string) $row['name'];
        }
        return $out;
    }
}
