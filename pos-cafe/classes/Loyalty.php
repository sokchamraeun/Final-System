<?php
declare(strict_types=1);

final class Loyalty
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
            $where[]  = 'l.loyalty_id LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->db->scalar(
            "SELECT COUNT(*) FROM loyalty_cards l {$whereSql}",
            $params
        );

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $rows   = $this->db->all(
            "SELECT l.*
             FROM loyalty_cards l
             {$whereSql}
             ORDER BY l.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        return $this->db->first(
            "SELECT * FROM loyalty_cards WHERE card_id = ? LIMIT 1",
            [$id]
        );
    }

    public function findByLoyaltyId(string $loyaltyId): ?array
    {
        return $this->db->first(
            "SELECT * FROM loyalty_cards WHERE loyalty_id = ? LIMIT 1",
            [$loyaltyId]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO loyalty_cards (loyalty_id, points, total_orders, total_drinks, last_used, is_active)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (string) $data['loyalty_id'],
                (int)    ($data['points'] ?? 0),
                (int)    ($data['total_orders'] ?? 0),
                (int)    ($data['total_drinks'] ?? 0),
                !empty($data['last_used']) ? (string) $data['last_used'] : null,
                (int)    ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE loyalty_cards
             SET loyalty_id = ?, points = ?, total_orders = ?, total_drinks = ?,
                 last_used = ?, is_active = ?
             WHERE card_id = ?",
            [
                (string) $data['loyalty_id'],
                (int)    ($data['points'] ?? 0),
                (int)    ($data['total_orders'] ?? 0),
                (int)    ($data['total_drinks'] ?? 0),
                !empty($data['last_used']) ? (string) $data['last_used'] : null,
                (int)    ($data['is_active'] ?? 0),
                $id,
            ]
        );
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM loyalty_cards WHERE card_id = ?", [$id]);
    }

    /* ══════════════ Dashboard / reporting ══════════════ */

    /** Aggregate stats for the dashboard header. */
    public function stats(): array
    {
        $row = $this->db->first(
            "SELECT COUNT(*) AS total_cards, COALESCE(SUM(points),0) AS total_points
             FROM loyalty_cards WHERE is_active = 1"
        );
        return [
            'total_cards'  => (int) ($row['total_cards'] ?? 0),
            'total_points' => (int) ($row['total_points'] ?? 0),
        ];
    }

    public function topCard(): ?array
    {
        return $this->db->first(
            "SELECT loyalty_id, points FROM loyalty_cards WHERE is_active = 1 ORDER BY points DESC LIMIT 1"
        );
    }

    /** All active cards, richest first. */
    public function activeCards(): array
    {
        return $this->db->all("SELECT * FROM loyalty_cards WHERE is_active = 1 ORDER BY points DESC");
    }

    /** Active rewards, cheapest first. */
    public function rewards(): array
    {
        return $this->db->all("SELECT * FROM rewards WHERE is_active = 1 ORDER BY points_required ASC");
    }

    public function findReward(string $name): ?array
    {
        return $this->db->first(
            "SELECT * FROM rewards WHERE reward_name = ? AND is_active = 1 LIMIT 1",
            [$name]
        );
    }

    /** Recent point movements across all cards (dashboard feed). */
    public function recentHistory(int $limit = 10): array
    {
        return $this->db->all(
            "SELECT h.*, c.loyalty_id
             FROM loyalty_history h
             JOIN loyalty_cards c ON c.card_id = h.card_id
             ORDER BY h.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function historyCount(int $cardId): int
    {
        return (int) $this->db->scalar(
            "SELECT COUNT(*) FROM loyalty_history WHERE card_id = ?",
            [$cardId]
        );
    }

    /** Paginated transaction history for one card (joined with the daily order number). */
    public function historyFor(int $cardId, int $page = 1, int $perPage = 10): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        return $this->db->all(
            "SELECT h.*, o.daily_order_no
             FROM loyalty_history h
             LEFT JOIN orders o ON o.order_id = h.order_id
             WHERE h.card_id = ?
             ORDER BY h.created_at DESC
             LIMIT ? OFFSET ?",
            [$cardId, $perPage, $offset]
        );
    }

    /** Generate a loyalty ID guaranteed unique in the table. */
    public function generateUniqueId(): string
    {
        do {
            $id = function_exists('generateLoyaltyId')
                ? generateLoyaltyId()
                : 'CARD-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $exists = (int) $this->db->scalar(
                "SELECT COUNT(*) FROM loyalty_cards WHERE loyalty_id = ?",
                [$id]
            );
        } while ($exists > 0);
        return $id;
    }

    /**
     * Admin points adjustment — updates the balance and logs history atomically.
     * @return array{success:bool,new_points?:int,message?:string}
     */
    public function adjustPoints(int $cardId, int $adjustment, string $reason = 'Manual adjustment by admin'): array
    {
        if ($cardId <= 0 || $adjustment === 0) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        $card = $this->db->first(
            "SELECT card_id, points FROM loyalty_cards WHERE card_id = ? AND is_active = 1",
            [$cardId]
        );
        if (!$card) {
            return ['success' => false, 'message' => 'Card not found'];
        }
        $newPoints = max(0, (int) $card['points'] + $adjustment);
        $conn = $this->db->raw();
        $conn->begin_transaction();
        try {
            $this->db->execute(
                "UPDATE loyalty_cards SET points = ?, last_used = NOW() WHERE card_id = ?",
                [$newPoints, $cardId]
            );
            $type = $adjustment > 0 ? 'adjusted_add' : 'adjusted_deduct';
            $this->db->execute(
                "INSERT INTO loyalty_history (card_id, points_change, type, description) VALUES (?, ?, ?, ?)",
                [$cardId, $adjustment, $type, $reason]
            );
            $conn->commit();
            return ['success' => true, 'new_points' => $newPoints];
        } catch (\Throwable $e) {
            $conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** Loyalty tier metadata for a points balance. */
    public static function tier(int $points): array
    {
        if ($points >= 1000) return ['name' => 'Platinum', 'color' => '#7c93a8', 'next' => null,       'next_pts' => 0];
        if ($points >= 500)  return ['name' => 'Gold',     'color' => '#d4af37', 'next' => 'Platinum', 'next_pts' => 1000];
        if ($points >= 100)  return ['name' => 'Silver',   'color' => '#9ca3af', 'next' => 'Gold',     'next_pts' => 500];
        return                      ['name' => 'Bronze',   'color' => '#cd7f32', 'next' => 'Silver',   'next_pts' => 100];
    }

    /** Tailwind + icon config for a history row type. */
    public static function typeConfig(string $type): array
    {
        return match ($type) {
            'earned'          => ['label' => 'Earned',       'icon' => 'fa-arrow-up',    'cls' => 'bg-emerald-100 text-emerald-700'],
            'redeemed'        => ['label' => 'Redeemed',     'icon' => 'fa-gift',        'cls' => 'bg-amber-100 text-amber-700'],
            'bonus'           => ['label' => 'Bonus',        'icon' => 'fa-star',        'cls' => 'bg-purple-100 text-purple-700'],
            'created'         => ['label' => 'Created',      'icon' => 'fa-plus',        'cls' => 'bg-slate-100 text-slate-600'],
            'adjusted_add'    => ['label' => 'Admin Add',    'icon' => 'fa-circle-plus', 'cls' => 'bg-blue-100 text-blue-700'],
            'adjusted_deduct' => ['label' => 'Admin Deduct', 'icon' => 'fa-circle-minus','cls' => 'bg-red-100 text-red-700'],
            default           => ['label' => ucfirst($type ?: 'Unknown'), 'icon' => 'fa-circle', 'cls' => 'bg-slate-100 text-slate-600'],
        };
    }
}
