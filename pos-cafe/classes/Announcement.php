<?php
declare(strict_types=1);

/* ============================================================
   Announcement model — maps to the `announcements` table
   (id, title, message, type, created_by, created_at, expires_at, is_active).
   ============================================================ */

final class Announcement
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
            $where[]  = 'a.title LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[]  = 'a.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }
        if (!empty($filters['type'])) {
            $where[]  = 'a.type = ?';
            $params[] = (string) $filters['type'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM announcements a {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT a.*, u.username AS created_by_name
             FROM announcements a
             LEFT JOIN users u ON u.user_id = a.created_by
             {$whereSql}
             ORDER BY a.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM announcements WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO announcements (title, message, type, created_by, expires_at, is_active)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string) $data['title'],
                (string) $data['message'],
                (string) ($data['type'] ?? 'info'),
                (int)    $data['created_by'],
                !empty($data['expires_at']) ? (string) $data['expires_at'] : null,
                (int)    ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE announcements
             SET title = ?, message = ?, type = ?, expires_at = ?, is_active = ?
             WHERE id = ?",
            [
                (string) $data['title'],
                (string) $data['message'],
                (string) ($data['type'] ?? 'info'),
                !empty($data['expires_at']) ? (string) $data['expires_at'] : null,
                (int)    ($data['is_active'] ?? 1),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM announcements WHERE id = ?", [$id]);
    }
}
