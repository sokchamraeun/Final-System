<?php
declare(strict_types=1);

/* ============================================================
   AddonIngredient model — maps to the `addon_ingredients` table
   (id, addon_id, ingredient_id, amount_used).
   ============================================================ */

final class AddonIngredient
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    /** @return array<int,array> */
    public function findByAddon(int $addonId): array
    {
        return $this->db->all(
            "SELECT ai.*, i.ingredient_name, i.unit
             FROM addon_ingredients ai
             LEFT JOIN ingredients i ON i.ingredient_id = ai.ingredient_id
             WHERE ai.addon_id = ?
             ORDER BY i.ingredient_name",
            [$addonId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT ai.*, i.ingredient_name, i.unit
             FROM addon_ingredients ai
             LEFT JOIN ingredients i ON i.ingredient_id = ai.ingredient_id
             WHERE ai.id = ? LIMIT 1",
            [$id]
        );
    }

    public function save(int $addonId, int $ingredientId, float $amountUsed): int
    {
        $existing = $this->db->first(
            "SELECT id FROM addon_ingredients WHERE addon_id = ? AND ingredient_id = ? LIMIT 1",
            [$addonId, $ingredientId]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE addon_ingredients SET amount_used = ? WHERE id = ?",
                [$amountUsed, (int) $existing['id']]
            );
            return (int) $existing['id'];
        }

        return $this->db->insert(
            "INSERT INTO addon_ingredients (addon_id, ingredient_id, amount_used) VALUES (?, ?, ?)",
            [$addonId, $ingredientId, $amountUsed]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM addon_ingredients WHERE id = ?", [$id]);
    }

    /** Remove all ingredients for an addon. */
    public function deleteByAddon(int $addonId): int
    {
        return $this->db->execute("DELETE FROM addon_ingredients WHERE addon_id = ?", [$addonId]);
    }
}
