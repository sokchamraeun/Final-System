<?php
session_start();
require __DIR__ . '/../../../config.php';

// ── CLEAR CART BEFORE ADDING ──
// This ensures only the current item is in the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cart'] = [];
}

// ── FIX SQL INJECTION ──
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid product ID.");
}

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sweetness = $_POST['sweetness'] ?? '';
    $ice = $_POST['ice'] ?? '';
    $milk = $_POST['milk'] ?? '';

    $item = [
        'product_id' => $product['product_id'],
        'product_name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'qty' => 1,
        'sweetness' => $sweetness,
        'ice' => $ice,
        'milk' => $milk
    ];

    // ── REPLACE CART WITH SINGLE ITEM ──
    $_SESSION['cart'] = [$item];

    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Your Drink | Obsidian Cafe</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                },
            },
        };
    </script>
</head>
<body class="font-sans bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 sm:p-8">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-2xl">

        <!-- Product Image -->
        <div class="relative">
            <img
                src="<?php echo htmlspecialchars($product['image']); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                class="w-full h-56 object-cover">
            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/50 to-transparent"></div>
        </div>

        <!-- Card Body -->
        <div class="p-6 sm:p-8">

            <!-- Title & Price -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h2>
                <div class="mt-2 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 text-amber-600 font-semibold text-lg">
                    <i class="fa-solid fa-tag text-sm"></i>
                    $<?php echo number_format($product['price'], 2); ?>
                </div>
            </div>

            <form method="POST" class="space-y-5">

                <!-- Sweetness Level -->
                <div>
                    <label class="flex items-center gap-2 mb-2 text-sm font-medium text-slate-600">
                        <i class="fa-solid fa-cube text-amber-500"></i>
                        Sweetness Level
                    </label>
                    <select name="sweetness" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition
                               cursor-pointer">
                        <option value="0%">0%</option>
                        <option value="25%">25%</option>
                        <option value="50%" selected>50%</option>
                        <option value="75%">75%</option>
                        <option value="100%">100%</option>
                    </select>
                </div>

                <!-- Ice Level -->
                <div>
                    <label class="flex items-center gap-2 mb-2 text-sm font-medium text-slate-600">
                        <i class="fa-solid fa-snowflake text-amber-500"></i>
                        Ice Level
                    </label>
                    <select name="ice" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition
                               cursor-pointer">
                        <option value="No Ice">No Ice</option>
                        <option value="Less Ice">Less Ice</option>
                        <option value="Normal Ice" selected>Normal Ice</option>
                        <option value="More Ice">More Ice</option>
                    </select>
                </div>

                <!-- Milk -->
                <div>
                    <label class="flex items-center gap-2 mb-2 text-sm font-medium text-slate-600">
                        <i class="fa-solid fa-mug-hot text-amber-500"></i>
                        Milk
                    </label>
                    <select name="milk"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition
                               cursor-pointer">
                        <option value="Fresh Milk" selected>Fresh Milk</option>
                        <option value="Almond Milk">Almond Milk</option>
                        <option value="Soy Milk">Soy Milk</option>
                        <option value="Oat Milk">Oat Milk</option>
                    </select>
                </div>

                <!-- Add to Cart -->
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-amber-500 hover:bg-amber-600
                           text-white font-semibold text-lg shadow-md hover:shadow-lg
                           transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-cart-plus"></i>
                    Add to Cart
                </button>
            </form>

            <!-- Back to Menu -->
            <a href="menu.php"
               class="mt-5 flex items-center justify-center gap-2 text-sm font-medium text-slate-400 hover:text-amber-500 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Menu
            </a>
        </div>
    </div>

</body>
</html>
