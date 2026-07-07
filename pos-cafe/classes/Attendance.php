<?php
declare(strict_types=1);

/* ============================================================
   Attendance model — maps to the `attendance` table
   (id, user_id, username, clock_in, clock_out, date, hours_worked).
   ============================================================ */

final class Attendance
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
            $where[]  = 'a.username LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'a.date >= ?';
            $params[] = (string) $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'a.date <= ?';
            $params[] = (string) $filters['date_to'];
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM attendance a {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT a.*
             FROM attendance a
             {$whereSql}
             ORDER BY a.date DESC, a.clock_in DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM attendance WHERE id = ? LIMIT 1",
            [$id]
        );
    }

    /** Check if a user is currently clocked in (no clock_out). */
    public function isClockedIn(int $userId): ?array
    {
        return $this->db->first(
            "SELECT * FROM attendance
             WHERE user_id = ? AND clock_out IS NULL AND date = CURDATE()
             LIMIT 1",
            [$userId]
        );
    }

    public function clockIn(int $userId, string $username): int
    {
        return $this->db->insert(
            "INSERT INTO attendance (user_id, username, clock_in, date)
             VALUES (?, ?, NOW(), CURDATE())",
            [$userId, $username]
        );
    }

    public function clockOut(int $id): int
    {
        return $this->db->execute(
            "UPDATE attendance
             SET clock_out = NOW(),
                 hours_worked = TIMESTAMPDIFF(HOUR, clock_in, NOW())
             WHERE id = ? AND clock_out IS NULL",
            [$id]
        );
    }
}
