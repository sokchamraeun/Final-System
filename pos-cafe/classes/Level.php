<?php
declare(strict_types=1);

final class Level
{
    private Database $db;
    private string $table;

    public function __construct(string $table = 'size_levels')
    {
        $this->db = Database::instance();
        $this->table = $table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function active(): array
    {
        return $this->db->all(
            "SELECT * FROM {$this->table} ORDER BY display_order, id"
        );
    }

    public function all(): array
    {
        return $this->db->all(
            "SELECT * FROM {$this->table} ORDER BY display_order, id"
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1",
            [$id]
        );
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

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM {$this->table} {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT * FROM {$this->table} {$whereSql} ORDER BY display_order, id LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO {$this->table} (name, display_order) VALUES (?, ?)",
            [
                (string) $data['name'],
                (int) ($data['display_order'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE {$this->table} SET name = ?, display_order = ? WHERE id = ?",
            [
                (string) $data['name'],
                (int) ($data['display_order'] ?? 0),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
