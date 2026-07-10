<?php
declare(strict_types=1);

/* ============================================================
   Cart model — owns the session shopping cart ($_SESSION['cart']).

   Encapsulates option validation, per-size price resolution,
   identical-line merging and total calculation. Mirrors the
   Product / Loyalty model shape; every DB query is a prepared
   statement run through Database.
   ============================================================ */

final class Cart
{
    /** Allowed customisation values ('' means "not chosen"). */
    public const SWEETNESS = ['0%', '25%', '50%', '75%', '100%'];

    private Database $db;

    public function __construct()
    {
        $this->db = Database::instance();
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Add a product (with options) to the session cart.
     *
     * @param array{sweetness?:string,ice?:string,milk?:string,sugar?:string,size?:string,addons?:string} $options
     * @return array{count:int,total:float}  Cart totals after the add.
     * @throws CartException on invalid input or unknown product.
     */
    public function add(int $productId, int $qty, array $options = []): array
    {
        if ($productId <= 0) {
            throw new CartException('Missing product ID', 400);
        }
        $qty = max(1, min(99, $qty));

        $sweetness = $this->cleanOption((string) ($options['sweetness'] ?? ''), self::SWEETNESS, 'sweetness');
        $ice       = trim((string) ($options['ice']       ?? ''));
        $milk      = trim((string) ($options['milk'] ?? ''));
        $sugar     = trim((string) ($options['sugar']     ?? ''));
        $sizeCode  = trim((string) ($options['size'] ?? ''));
        $addons    = trim((string) ($options['addons'] ?? ''));

        $product = $this->db->first(
            "SELECT product_id, name, price, image, has_sizes FROM products WHERE product_id = ?",
            [$productId]
        );
        if (!$product) {
            throw new CartException('Product not found or unavailable', 404);
        }

        [$linePrice, $sizeLabel, $sizeFactor, $resolvedCode, $discountPct] = $this->resolveSize($product, $sizeCode);

        $this->mergeItem([
            'product_id'   => (int) $product['product_id'],
            'product_name' => (string) $product['name'],
            'price'        => $linePrice,
            'image'        => (string) $product['image'],
            'size_code'    => $resolvedCode,
            'size_label'   => $sizeLabel,
            'size_factor'  => $sizeFactor,
            'discount_pct' => $discountPct,
            'sweetness'    => $sweetness,
            'ice'          => $ice,
            'milk'         => $milk,
            'sugar'        => $sugar,
            'addons'       => $addons,
            'qty'          => $qty,
        ]);

        return $this->totals();
    }

    /** @return array{count:int,total:float} */
    public function totals(): array
    {
        $count = 0;
        $total = 0.0;
        foreach ($_SESSION['cart'] as $item) {
            $q      = (int) ($item['qty'] ?? 1);
            $count += $q;
            $total += (float) ($item['price'] ?? 0) * $q;
        }
        return ['count' => $count, 'total' => round($total, 2)];
    }

    /** Validate a customisation value against its whitelist ('' = not chosen). */
    private function cleanOption(string $value, array $allowed, string $label): string
    {
        $value = trim($value);
        if ($value !== '' && !in_array($value, $allowed, true)) {
            throw new CartException("Invalid {$label} option", 400);
        }
        return $value;
    }

    /**
     * Resolve the per-size absolute price for a product. Sized products
     * carry an explicit price per size_code; everything else falls back
     * to the base products.price (defensive: has_sizes=1 with no rows is
     * treated as unsized rather than an error).
     *
     * @return array{0:float,1:string,2:float,3:string,4:int} [price, label, factor, code, discount_pct]
     */
    private function resolveSize(array $product, string $sizeCode): array
    {
        $linePrice = (float) $product['price'];   // products.price == Medium / base
        if ((int) $product['has_sizes'] !== 1) {
            return [$linePrice, '', 1.0, '', 0];
        }

        $rows = [];
        foreach ($this->db->all(
            "SELECT size_code, label, price, size_factor, promo_pct FROM product_sizes WHERE product_id = ?",
            [(int) $product['product_id']]
        ) as $r) {
            $rows[$r['size_code']] = $r;
        }
        if (!$rows) {
            return [$linePrice, '', 1.0, '', 0];
        }

        // size_code is required once a product actually has sizes
        if ($sizeCode === '' || !isset($rows[$sizeCode])) {
            throw new CartException('Please choose a size', 400);
        }
        $chosen = $rows[$sizeCode];

        return [
            (float) $chosen['price'],
            (string) $chosen['label'],
            (float) $chosen['size_factor'],
            $sizeCode,
            min(90, (int) ($chosen['promo_pct'] ?? 0)),
        ];
    }

    /** Merge into an identical line (same product + options) or append a new one. */
    private function mergeItem(array $line): void
    {
        $wasEmpty = empty($_SESSION['cart']);

        foreach ($_SESSION['cart'] as &$item) {
            if (
                (int) $item['product_id'] === $line['product_id'] &&
                (string) ($item['size_code'] ?? '') === $line['size_code'] &&
                $item['sweetness'] === $line['sweetness'] &&
                $item['ice']       === $line['ice'] &&
                $item['milk']      === $line['milk'] &&
                $item['sugar']     === $line['sugar'] &&
                ($item['addons'] ?? '') === $line['addons']
            ) {
                $item['qty'] += $line['qty'];
                return;
            }
        }
        unset($item);

        $_SESSION['cart'][] = $line;

        // Stamp when the first item lands in an empty cart.
        if ($wasEmpty && !isset($_SESSION['cart_started_at'])) {
            $_SESSION['cart_started_at'] = date('Y-m-d H:i:s');
        }
    }
}
