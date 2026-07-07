<?php
declare(strict_types=1);

/* ============================================================
   Supplier model — maps to the `suppliers` table
   (supplier_id, name, contact_person, phone, email, address, notes, created_at).
   ============================================================ */

final class Supplier
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
            $where[]  = '(s.name LIKE ? OR s.contact_person LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM suppliers s {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT s.*
             FROM suppliers s
             {$whereSql}
             ORDER BY s.name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM suppliers WHERE supplier_id = ? LIMIT 1",
            [$id]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO suppliers (name, contact_person, phone, email, address, notes)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string) $data['name'],
                (string) ($data['contact_person'] ?? ''),
                (string) ($data['phone'] ?? ''),
                (string) ($data['email'] ?? ''),
                (string) ($data['address'] ?? ''),
                (string) ($data['notes'] ?? ''),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE suppliers
             SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, notes = ?
             WHERE supplier_id = ?",
            [
                (string) $data['name'],
                (string) ($data['contact_person'] ?? ''),
                (string) ($data['phone'] ?? ''),
                (string) ($data['email'] ?? ''),
                (string) ($data['address'] ?? ''),
                (string) ($data['notes'] ?? ''),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM suppliers WHERE supplier_id = ?", [$id]);
    }

    /** @return array<int,string> */
    public function options(): array
    {
        $rows = $this->db->all(
            "SELECT supplier_id, name FROM suppliers ORDER BY name"
        );
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['supplier_id']] = (string) $row['name'];
        }
        return $out;
    }
}
