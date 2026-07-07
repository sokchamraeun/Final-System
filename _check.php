<?php
require __DIR__ . '/config.php';
$r = $conn->query("SHOW COLUMNS FROM orders LIKE '%stand%'");
echo "Columns matching 'stand':\n";
if ($r->num_rows === 0) echo "(none found)\n";
while ($row = $r->fetch_assoc()) {
    echo "  {$row['Field']}  ({$row['Type']})\n";
}
echo "\nAll orders columns:\n";
$r2 = $conn->query("SHOW COLUMNS FROM orders");
while ($row = $r2->fetch_assoc()) {
    echo "  {$row['Field']}  ({$row['Type']})\n";
}
