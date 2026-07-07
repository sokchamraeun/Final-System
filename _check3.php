<?php
require __DIR__ . '/config.php';
foreach (['size_levels', 'product_sizes', 'levels', 'ice_levels', 'sugar_levels', 'product_ice_levels', 'product_sugar_levels'] as $tbl) {
    $r = $conn->query("SHOW COLUMNS FROM $tbl");
    if ($r) {
        echo "$tbl:\n";
        while ($row = $r->fetch_assoc()) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
        echo "\n";
    } else {
        echo "$tbl: NOT FOUND\n\n";
    }
}
