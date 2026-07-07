<?php
declare(strict_types=1);

/* ============================================================
   Recipe model — maps to the `product_ingredients` table
   (id, product_id, ingredient_id, amount_used).
   ============================================================ */

final class Recipe
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    /** @return array<int,array> */
    public function findByProduct(int $productId): array
    {
        return $this->db->all(
            "SELECT pi.*, i.ingredient_name, i.unit
             FROM product_ingredients pi
             LEFT JOIN ingredients i ON i.ingredient_id = pi.ingredient_id
             WHERE pi.product_id = ?
             ORDER BY i.ingredient_name",
            [$productId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT pi.*, i.ingredient_name, i.unit
             FROM product_ingredients pi
             LEFT JOIN ingredients i ON i.ingredient_id = pi.ingredient_id
             WHERE pi.id = ? LIMIT 1",
            [$id]
        );
    }

    public function save(int $productId, int $ingredientId, float $amountUsed): int
    {
        $existing = $this->db->first(
            "SELECT id FROM product_ingredients WHERE product_id = ? AND ingredient_id = ? LIMIT 1",
            [$productId, $ingredientId]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE product_ingredients SET amount_used = ? WHERE id = ?",
                [$amountUsed, (int) $existing['id']]
            );
            return (int) $existing['id'];
        }

        return $this->db->insert(
            "INSERT INTO product_ingredients (product_id, ingredient_id, amount_used) VALUES (?, ?, ?)",
            [$productId, $ingredientId, $amountUsed]
        );
    }

    /** Alias for save() with create-like signature for consistency. */
    public function create(array $data): int
    {
        return $this->save(
            (int) $data['product_id'],
            (int) $data['ingredient_id'],
            (float) ($data['amount_used'] ?? 0)
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM product_ingredients WHERE id = ?", [$id]);
    }

    /** Remove all ingredients for a product. */
    public function deleteByProduct(int $productId): int
    {
        return $this->db->execute(
            "DELETE FROM product_ingredients WHERE product_id = ?",
            [$productId]
        );
    }
}
