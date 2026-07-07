<?php
declare(strict_types=1);

/* ============================================================
   Category model — maps to the `categories` table
   (category_id, slug, name, icon, image, display_order, is_active).
   ============================================================ */

final class Category
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    /** @return array<int,array> Active categories, ordered for display. */
    public function active(): array
    {
        return $this->db->all(
            "SELECT category_id, slug, name, icon, image, display_order
             FROM categories
             WHERE is_active = 1
             ORDER BY display_order, name"
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM categories WHERE category_id = ? LIMIT 1",
            [$id]
        );
    }

    /** slug => name map, handy for dropdowns and labels. */
    public function options(): array
    {
        $out = [];
        foreach ($this->active() as $c) {
            $out[$c['slug']] = $c['name'];
        }
        return $out;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = PER_PAGE): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = 'c.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM categories c {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT c.*
             FROM categories c
             {$whereSql}
             ORDER BY c.display_order, c.name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO categories (slug, name, icon, image, display_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string) ($data['slug'] ?? str_slug($data['name'] ?? '')),
                (string) $data['name'],
                (string) ($data['icon'] ?? ''),
                (string) ($data['image'] ?? ''),
                (int)    ($data['display_order'] ?? 0),
                isset($data['is_active']) ? 1 : 0,
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE categories
             SET slug = ?, name = ?, icon = ?, image = ?,
                 display_order = ?, is_active = ?
             WHERE category_id = ?",
            [
                (string) ($data['slug'] ?? str_slug($data['name'] ?? '')),
                (string) $data['name'],
                (string) ($data['icon'] ?? ''),
                (string) ($data['image'] ?? ''),
                (int)    ($data['display_order'] ?? 0),
                isset($data['is_active']) ? 1 : 0,
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM categories WHERE category_id = ?", [$id]);
    }
}
