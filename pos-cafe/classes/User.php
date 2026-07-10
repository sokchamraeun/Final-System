<?php
declare(strict_types=1);

/* ============================================================
   User model — maps to the `users` table
   (user_id, username, password, security_question, security_answer,
    must_change_password, reset_token, reset_token_expires, role_id).
   ============================================================ */

final class User
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
            $where[]  = 'u.username LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM users u {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT u.user_id, u.username, u.must_change_password, u.reset_token,
                    u.reset_token_expires, u.role_id, r.name AS role_name,
                    e.name AS employee_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             LEFT JOIN employees e ON e.user_id = u.user_id
             {$whereSql}
             ORDER BY u.username
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM users WHERE user_id = ? LIMIT 1",
            [$id]
        );
    }

    public function findByUsername(string $username): ?array
    {
        return $this->db->first(
            "SELECT * FROM users WHERE username = ? LIMIT 1",
            [$username]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO users (username, password, security_question, security_answer,
                                must_change_password, reset_token, reset_token_expires, role_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (string) $data['username'],
                password_hash((string) $data['password'], PASSWORD_DEFAULT),
                (string) ($data['security_question'] ?? ''),
                (string) ($data['security_answer'] ?? ''),
                (int)    ($data['must_change_password'] ?? 0),
                isset($data['reset_token']) ? (string) $data['reset_token'] : null,
                isset($data['reset_token_expires']) ? (string) $data['reset_token_expires'] : null,
                isset($data['role_id']) ? (int) $data['role_id'] : null,
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];

        if (array_key_exists('username', $data)) {
            $fields[] = 'username = ?';
            $params[] = (string) $data['username'];
        }
        if (array_key_exists('password', $data) && $data['password'] !== '') {
            $fields[] = 'password = ?';
            $params[] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }
        if (array_key_exists('security_question', $data)) {
            $fields[] = 'security_question = ?';
            $params[] = (string) $data['security_question'];
        }
        if (array_key_exists('security_answer', $data)) {
            $fields[] = 'security_answer = ?';
            $params[] = (string) $data['security_answer'];
        }
        if (array_key_exists('must_change_password', $data)) {
            $fields[] = 'must_change_password = ?';
            $params[] = (int) $data['must_change_password'];
        }
        if (array_key_exists('reset_token', $data)) {
            $fields[] = 'reset_token = ?';
            $params[] = $data['reset_token'] !== null ? (string) $data['reset_token'] : null;
        }
        if (array_key_exists('reset_token_expires', $data)) {
            $fields[] = 'reset_token_expires = ?';
            $params[] = $data['reset_token_expires'] !== null ? (string) $data['reset_token_expires'] : null;
        }
        if (array_key_exists('role_id', $data)) {
            $fields[] = 'role_id = ?';
            $params[] = isset($data['role_id']) ? (int) $data['role_id'] : null;
        }

        if ($fields === []) {
            return 0;
        }

        $params[] = $id;

        return $this->db->execute(
            "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?",
            $params
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM users WHERE user_id = ?", [$id]);
    }
}
