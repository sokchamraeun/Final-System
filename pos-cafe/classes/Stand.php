<?php

declare(strict_types=1);

/**
 * -------------------------------------------------------------
 * Stand Model
 * -------------------------------------------------------------
 * Handles café stand/table management without a dedicated
 * database table.
 *
 * The available stands are generated dynamically using the
 * STAND_COUNT constant.
 * -------------------------------------------------------------
 */

final class Stand
{
    /**
     * Database instance.
     */
    private Database $db;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->db = Database::instance();
    }

    /**
     * Get all available stands.
     *
     * @return array<int, array{
     *     id:int,
     *     label:string,
     *     occupied:bool
     * }>
     */
    public function all(): array
    {
        $occupiedTables = $this->db->all(
            "
            SELECT DISTINCT table_number
            FROM orders
            WHERE table_number IS NOT NULL
              AND table_number <> ''
              AND status NOT IN ('completed', 'cancelled')
            "
        );

        $occupiedIds = array_map(
            'intval',
            array_column($occupiedTables, 'table_number')
        );

        $stands = [];

        for ($id = 1; $id <= STAND_COUNT; $id++) {
            $stands[] = [
                'id'        => $id,
                'label'     => "Stand #{$id}",
                'occupied'  => in_array($id, $occupiedIds, true),
            ];
        }

        return $stands;
    }

    /**
     * Find a stand by ID.
     */
    public function find(int $id): ?array
    {
        foreach ($this->all() as $stand) {
            if ($stand['id'] === $id) {
                return $stand;
            }
        }

        return null;
    }

    /**
     * Assign a stand to an order.
     *
     * @return int Number of affected rows.
     */
    public function occupy(int $standId, int $orderId): int
    {
        return $this->db->execute(
            "
            UPDATE orders
            SET table_number = ?
            WHERE order_id = ?
            ",
            [(string) $standId, $orderId]
        );
    }

    /**
     * Free a stand.
     *
     * Removes the stand assignment from all active orders.
     *
     * @return int Number of affected rows.
     */
    public function free(int $standId): int
    {
        return $this->db->execute(
            "
            UPDATE orders
            SET table_number = NULL
            WHERE table_number = ?
              AND status NOT IN ('completed', 'cancelled')
            ",
            [(string) $standId]
        );
    }

    /**
     * Check whether a stand is occupied.
     */
    public function isOccupied(int $standId): bool
    {
        $stand = $this->find($standId);

        return $stand !== null && $stand['occupied'];
    }

    /**
     * Get all available (empty) stands.
     *
     * @return array<int, array>
     */
    public function available(): array
    {
        return array_values(
            array_filter(
                $this->all(),
                static fn(array $stand): bool => !$stand['occupied']
            )
        );
    }

    /**
     * Get all occupied stands.
     *
     * @return array<int, array>
     */
    public function occupied(): array
    {
        return array_values(
            array_filter(
                $this->all(),
                static fn(array $stand): bool => $stand['occupied']
            )
        );
    }
}