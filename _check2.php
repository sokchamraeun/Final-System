<?php
require __DIR__ . '/config.php';
$r = $conn->query("SHOW TABLES LIKE '%size%'");
echo "Tables matching 'size':\n";
while ($row = $r->fetch_assoc()) {
    echo "  " . implode('', $row) . "\n";
}
$r = $conn->query("SHOW TABLES LIKE '%level%'");
echo "\nTables matching 'level':\n";
while ($row = $r->fetch_assoc()) {
    echo "  " . implode('', $row) . "\n";
}
$r = $conn->query("SHOW TABLES LIKE '%product%'");
echo "\nTables matching 'product':\n";
while ($row = $r->fetch_assoc()) {
    echo "  " . implode('', $row) . "\n";
}
