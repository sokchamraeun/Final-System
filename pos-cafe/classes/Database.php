<?php
declare(strict_types=1);

/* ============================================================
   Database — a thin OOP wrapper over the shared mysqli $conn.

   It does NOT open its own connection (config.php owns that);
   it wraps the existing handle to give models a clean, prepared-
   statement API and remove bind_param boilerplate.

   Types are inferred (i/d/s) when not supplied.
   ============================================================ */

final class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        $conn = $GLOBALS['conn'] ?? null;
        if (!$conn instanceof mysqli) {
            throw new RuntimeException('mysqli connection missing — include config/app.php first.');
        }
        $this->conn = $conn;
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function raw(): mysqli
    {
        return $this->conn;
    }

    private static function inferTypes(array $params): string
    {
        $types = '';
        foreach ($params as $p) {
            $types .= match (true) {
                is_int($p)   => 'i',
                is_float($p) => 'd',
                default      => 's',
            };
        }
        return $types;
    }

    /** Run a prepared statement, return the mysqli_result (or bool for writes). */
    public function run(string $sql, array $params = [], string $types = ''): mysqli_result|bool
    {
        if ($params === []) {
            return $this->conn->query($sql);
        }
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Prepare failed: ' . $this->conn->error);
        }
        $stmt->bind_param($types ?: self::inferTypes($params), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? true : $result;
    }

    /** All rows as an associative array. */
    public function all(string $sql, array $params = [], string $types = ''): array
    {
        $res = $this->run($sql, $params, $types);
        return $res instanceof mysqli_result ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** First row, or null. */
    public function first(string $sql, array $params = [], string $types = ''): ?array
    {
        $res = $this->run($sql, $params, $types);
        return $res instanceof mysqli_result ? ($res->fetch_assoc() ?: null) : null;
    }

    /** Single scalar value from the first column of the first row. */
    public function scalar(string $sql, array $params = [], string $types = ''): mixed
    {
        $res = $this->run($sql, $params, $types);
        if ($res instanceof mysqli_result) {
            $row = $res->fetch_row();
            return $row[0] ?? null;
        }
        return null;
    }

    /** Execute an INSERT and return the new id. */
    public function insert(string $sql, array $params = [], string $types = ''): int
    {
        $this->run($sql, $params, $types);
        return (int) $this->conn->insert_id;
    }

    /** Execute an UPDATE/DELETE and return affected rows. */
    public function execute(string $sql, array $params = [], string $types = ''): int
    {
        $this->run($sql, $params, $types);
        return (int) $this->conn->affected_rows;
    }

    public function escape(string $value): string
    {
        return $this->conn->real_escape_string($value);
    }
}
