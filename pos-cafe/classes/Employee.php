<?php
declare(strict_types=1);

/* ============================================================
   Employee model — maps to the `employees` table
   (employee_id, name, phone, address, date_of_birth, hire_date,
    job_title, salary, photo, created_at, user_id, shift).
   ============================================================ */

final class Employee
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
            $where[]  = '(e.name LIKE ? OR e.job_title LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM employees e {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT e.*
             FROM employees e
             {$whereSql}
             ORDER BY e.name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM employees WHERE employee_id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO employees
                (name, phone, address, date_of_birth, hire_date,
                 job_title, salary, photo, user_id, shift)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (string) $data['name'],
                (string) ($data['phone'] ?? ''),
                (string) ($data['address'] ?? ''),
                !empty($data['date_of_birth']) ? (string) $data['date_of_birth'] : null,
                !empty($data['hire_date']) ? (string) $data['hire_date'] : null,
                (string) ($data['job_title'] ?? ''),
                (float)  ($data['salary'] ?? 0),
                (string) ($data['photo'] ?? ''),
                isset($data['user_id']) ? (int) $data['user_id'] : null,
                (string) ($data['shift'] ?? ''),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE employees
             SET name = ?, phone = ?, address = ?, date_of_birth = ?, hire_date = ?,
                 job_title = ?, salary = ?, photo = ?, user_id = ?, shift = ?
             WHERE employee_id = ?",
            [
                (string) $data['name'],
                (string) ($data['phone'] ?? ''),
                (string) ($data['address'] ?? ''),
                !empty($data['date_of_birth']) ? (string) $data['date_of_birth'] : null,
                !empty($data['hire_date']) ? (string) $data['hire_date'] : null,
                (string) ($data['job_title'] ?? ''),
                (float)  ($data['salary'] ?? 0),
                (string) ($data['photo'] ?? ''),
                isset($data['user_id']) ? (int) $data['user_id'] : null,
                (string) ($data['shift'] ?? ''),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM employees WHERE employee_id = ?", [$id]);
    }
}
