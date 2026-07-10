<?php
date_default_timezone_set('Asia/Phnom_Penh');

// Database connection
// âš ï¸  Run this once in phpMyAdmin/MySQL CLI before changing these credentials:
//   CREATE USER 'cafe_pos'@'localhost' IDENTIFIED BY 'Caf3P0S!2025#Kh';
//   GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER ON db_coffee.* TO 'cafe_pos'@'localhost';
//   FLUSH PRIVILEGES;
// Local XAMPP defaults. In production, override these via a git-ignored
// db_config.local.php (copy db_config.local.example.php) so real credentials
// never live in the repo â€” same pattern as bakong_config.local.php.
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_coffeeshop_final";

if (is_file(__DIR__ . '/db_config.local.php')) {
    require __DIR__ . '/db_config.local.php';
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// â”€â”€ CRITICAL: Force utf8mb4 so 4-byte emoji are read correctly â”€â”€
$conn->set_charset('utf8mb4');

// --- Check if constants are already defined before defining them ---
if (!defined('PAYMENT_API_URL')) {
    define('PAYMENT_API_URL', 'https://api.example.com/payment');
}
if (!defined('PAYMENT_API_TOKEN')) {
    define('PAYMENT_API_TOKEN', 'your_token_here');
}

// â”€â”€ LOAD SETTINGS FROM DB â”€â”€
$_cafe_settings = [];
$_sr = $conn->query("SELECT setting_key, setting_value FROM settings");
if ($_sr) { while ($row = $_sr->fetch_assoc()) $_cafe_settings[$row['setting_key']] = $row['setting_value']; }

// â”€â”€ Date-range check for promotions â”€â”€
$_today = date('Y-m-d');
$_hh_sd = $_cafe_settings['happy_hour_start_date'] ?? '';
$_hh_ed = $_cafe_settings['happy_hour_end_date']   ?? '';
$_hh_in_range = (($_hh_sd === '' || $_today >= $_hh_sd) && ($_hh_ed === '' || $_today <= $_hh_ed));
$_bx_sd = $_cafe_settings['buy_x_start_date'] ?? '';
$_bx_ed = $_cafe_settings['buy_x_end_date']   ?? '';
$_bx_in_range = (($_bx_sd === '' || $_today >= $_bx_sd) && ($_bx_ed === '' || $_today <= $_bx_ed));

if (!defined('HAPPY_HOUR_ENABLED'))  define('HAPPY_HOUR_ENABLED',  (bool)(int)($_cafe_settings['happy_hour_enabled']  ?? 1) && $_hh_in_range);
if (!defined('HAPPY_HOUR_START'))    define('HAPPY_HOUR_START',    (int)($_cafe_settings['happy_hour_start']    ?? 14));
if (!defined('HAPPY_HOUR_END'))      define('HAPPY_HOUR_END',      (int)($_cafe_settings['happy_hour_end']      ?? 16));
if (!defined('HAPPY_HOUR_DISCOUNT')) define('HAPPY_HOUR_DISCOUNT', (int)($_cafe_settings['happy_hour_discount'] ?? 20));
if (!defined('BUY_X_GET_1_ENABLED')) define('BUY_X_GET_1_ENABLED',(bool)(int)($_cafe_settings['buy_x_get_1_enabled'] ?? 1) && $_bx_in_range);
if (!defined('BUY_X_COUNT'))         define('BUY_X_COUNT',         (int)($_cafe_settings['buy_x_count']         ?? 3));
if (!defined('KHR_RATE'))            define('KHR_RATE',             (int)($_cafe_settings['khr_exchange_rate']   ?? 4100));
if (!defined('FREE_ITEM_PRODUCT_ID')) define('FREE_ITEM_PRODUCT_ID', (int)($_cafe_settings['free_item_product_id'] ?? 0));
if (!defined('TAX_RATE'))            define('TAX_RATE',             (float)($_cafe_settings['tax_rate']           ?? 10));
if (!defined('DAILY_SALES_TARGET'))  define('DAILY_SALES_TARGET',   (float)($_cafe_settings['daily_sales_target'] ?? 500));
if (!defined('STAND_COUNT'))         define('STAND_COUNT',          max(1, min(100, (int)($_cafe_settings['stand_count'] ?? 20))));
unset($_cafe_settings, $_sr, $_today, $_hh_sd, $_hh_ed, $_hh_in_range, $_bx_sd, $_bx_ed, $_bx_in_range);

// â”€â”€ Schema migrations tracker â”€â”€
$conn->query("CREATE TABLE IF NOT EXISTS schema_migrations (id VARCHAR(100) NOT NULL PRIMARY KEY, applied_at DATETIME DEFAULT CURRENT_TIMESTAMP) DEFAULT CHARSET=utf8mb4");
if (!function_exists('_migrate')) {
    function _migrate(mysqli $db, string $id, callable $fn): void {
        $chk = $db->prepare("SELECT id FROM schema_migrations WHERE id=?");
        $chk->bind_param("s", $id); $chk->execute();
        if ($chk->get_result()->num_rows) return;
        $fn($db);
        if ($db->errno !== 0) return; // don't mark applied if last query failed
        $ins = $db->prepare("INSERT IGNORE INTO schema_migrations (id) VALUES (?)");
        $ins->bind_param("s", $id); $ins->execute();
    }
    function _add_col(mysqli $db, string $table, string $column, string $def): void {
        $chk = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($chk && $chk->num_rows === 0) {
            $db->query("ALTER TABLE `$table` ADD COLUMN $def");
        }
    }
}

// â”€â”€ One-time schema migrations â”€â”€
_migrate($conn, 'orders_cols_v1', function($db) {
    _add_col($db, 'orders', 'prepared_by',    'VARCHAR(100) NULL DEFAULT NULL');
    _add_col($db, 'orders', 'prepared_by_role','VARCHAR(50) NULL DEFAULT NULL');
    _add_col($db, 'orders', 'table_number',   'VARCHAR(10) NULL DEFAULT NULL');
    _add_col($db, 'orders', 'customer_id',    'INT NULL');
});
_migrate($conn, 'orders_started_at_v1', function($db) {
    _add_col($db, 'orders', 'started_at', 'DATETIME NULL DEFAULT NULL');
});
_migrate($conn, 'employees_user_id', function($db) {
    _add_col($db, 'employees', 'user_id', 'INT NULL');
});
_migrate($conn, 'employees_shift_v1', function($db) {
    _add_col($db, 'employees', 'shift', "ENUM('morning','afternoon','night') NULL DEFAULT NULL");
});
// Display-only / non-POS staff (cleaner, waiter, etc.): is_pos=0 means no login, no role.
_migrate($conn, 'employees_is_pos_v1', function($db) {
    _add_col($db, 'employees', 'is_pos', 'TINYINT(1) NOT NULL DEFAULT 1');
});
_migrate($conn, 'products_badge_text', function($db) {
    _add_col($db, 'products', 'badge_text', 'VARCHAR(40) NULL DEFAULT NULL');
});
$conn->query("CREATE TABLE IF NOT EXISTS login_attempts (id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL, attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_ip_time (ip, attempted_at)) DEFAULT CHARSET=utf8mb4");

// Canonical table is cash_counts (renamed from the legacy cash_reconciliations).
// Create it under the real name so we never recreate the old zombie every load.
$conn->query("CREATE TABLE IF NOT EXISTS cash_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    shift_date DATE NOT NULL,
    login_time DATETIME NOT NULL,
    expected_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
    actual_cash DECIMAL(10,2) NOT NULL DEFAULT 0,
    difference DECIMAL(10,2) GENERATED ALWAYS AS (actual_cash - expected_cash) STORED,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, shift_date)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS announcement_reads (
    user_id INT NOT NULL,
    announcement_id INT NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, announcement_id)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS ingredient_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ingredient_id INT NOT NULL,
    change_type ENUM('order_deduct','order_restore','quick_restock','po_received','manual_adjust') NOT NULL,
    amount DECIMAL(10,4) NOT NULL,
    order_id INT NULL,
    reference VARCHAR(255) NULL,
    created_by VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ing (ingredient_id),
    INDEX idx_created (created_at)
) DEFAULT CHARSET=utf8mb4");
_migrate($conn, 'ingredient_history_enum_v1', function($db) {
    $db->query("ALTER TABLE ingredient_history MODIFY COLUMN change_type ENUM('order_deduct','order_restore','quick_restock','po_received','manual_adjust') NOT NULL");
});
_migrate($conn, 'order_remakes_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS order_remakes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        reason TEXT NOT NULL,
        remade_by VARCHAR(100) NOT NULL,
        remade_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_order (order_id)
    ) DEFAULT CHARSET=utf8mb4");
});

// â”€â”€ New tables: categories, customers â”€â”€
$conn->query("CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-circle',
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
) DEFAULT CHARSET=utf8mb4");

if ((int)$conn->query("SELECT COUNT(*) FROM categories")->fetch_row()[0] === 0) {
    $conn->query("INSERT INTO categories (slug, name, icon, display_order) VALUES
        ('Iced','Iced Beverages','fa-snowflake',1),
        ('Hot','Hot Beverages','fa-mug-hot',2),
        ('Frappe','Frappes','fa-blender',3),
        ('Juice','Juices','fa-lemon',4),
        ('Milk Tea','Milk Tea','fa-circle-dot',5)");
}

$conn->query("CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4");

// â”€â”€ RBAC: create tables â”€â”€
$conn->query("CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    sort_order INT DEFAULT 0
) DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS role_permissions (
    role VARCHAR(50) NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role, permission_id)
) DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-user',
    color VARCHAR(20) DEFAULT '#888888',
    description VARCHAR(200) DEFAULT '',
    is_system TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) DEFAULT CHARSET=utf8mb4");

if ((int)$conn->query("SELECT COUNT(*) FROM roles")->fetch_row()[0] === 0) {
    $conn->query("INSERT INTO roles (slug, name, icon, color, description, is_system) VALUES
        ('admin',            'Admin',            'fa-user-shield',  '#d1904b', 'Full system access â€” cannot be restricted', 1),
        ('manager',          'Manager',          'fa-user-tie',     '#3498db', 'Operational access â€” configure below',     1),
        ('staff',            'Cashier',          'fa-user',         '#55e087', 'Limited access â€” configure below',         1),
        ('barista',          'Barista',          'fa-mug-hot',      '#d1904b', 'Kitchen display + recipe reference',        0),
        ('supervisor',       'Supervisor',       'fa-user-check',   '#f39c12', 'Shift runner â€” operational oversight',      0),
        ('inventory_clerk',  'Inventory',        'fa-box-open',     '#1abc9c', 'Stock and procurement management',          0)");
}
$conn->query("UPDATE roles SET name='Inventory' WHERE slug='inventory_clerk' AND name='Inventory Clerk'");

// â”€â”€ RBAC: seed permissions + defaults (runs once) â”€â”€
if ((int)$conn->query("SELECT COUNT(*) FROM permissions")->fetch_row()[0] === 0) {
    $perms = [
        ['Dashboard',          'dashboard',       'Overview',    1],
        ['Find Unpaid Orders', 'find_orders',     'Orders',      2],
        ['View Orders',        'view_orders',     'Orders',      3],
        ['Loyalty Card',       'loyalty',         'Loyalty',     4],
        ['Products',           'products',        'Inventory',   5],
        ['Ingredients',        'ingredients',     'Inventory',   6],
        ['Drink Recipe',       'recipes',         'Inventory',   7],
        ['Manage Recipes',     'manage_recipes',  'Inventory',   17],
        ['Suppliers',          'suppliers',       'Procurement', 8],
        ['Purchase Orders',    'purchase_orders', 'Procurement', 9],
        ['Daily Report',       'report',          'Analytics',   10],
        ['Employees',          'employees',       'Staff',       11],
        ['Announcements',      'announcements',   'Staff',       12],
        ['Attendance',         'attendance',      'Staff',       13],
        ['Promotions',         'promotions',      'Staff',       14],
        ['Manage Roles',       'manage_roles',    'Admin',       15],
        ['Reset Password',     'reset_password',  'Staff',       18],
    ];
    $ps = $conn->prepare("INSERT IGNORE INTO permissions (name,slug,module,sort_order) VALUES (?,?,?,?)");
    foreach ($perms as $p) { $ps->bind_param("sssi",$p[0],$p[1],$p[2],$p[3]); $ps->execute(); }

    // Default manager permissions
    $conn->query("INSERT IGNORE INTO role_permissions (role,permission_id) SELECT 'manager',id FROM permissions WHERE slug IN ('dashboard','find_orders','view_orders','loyalty','products','categories','ingredients','recipes','manage_recipes','suppliers','purchase_orders','report','announcements','attendance','promotions','reset_password')");

    // Default staff permissions
    $conn->query("INSERT IGNORE INTO role_permissions (role,permission_id) SELECT 'staff',id FROM permissions WHERE slug IN ('dashboard','find_orders','loyalty')");
}

// â”€â”€ RBAC: register newly-added permissions for existing installs (run once via migrations) â”€â”€
_migrate($conn, 'rbac_perm_upgrades_v1', function($db) {
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Manage Recipes', 'manage_recipes', 'Inventory', 17)");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager', id FROM permissions WHERE slug='manage_recipes'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager', id FROM permissions WHERE slug='promotions'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'barista', id FROM permissions WHERE slug IN ('view_orders','recipes')");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'supervisor', id FROM permissions WHERE slug IN (
        'dashboard','find_orders','view_orders','loyalty',
        'ingredients','recipes','manage_recipes','suppliers',
        'announcements','attendance'
    )");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'inventory_clerk', id FROM permissions WHERE slug IN ('products','ingredients','recipes','suppliers','purchase_orders')");
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Reset Password', 'reset_password', 'Staff', 18)");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager', id FROM permissions WHERE slug='reset_password'");
});

_migrate($conn, 'rbac_my_profile_v1', function($db) {
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('My Profile', 'my_profile', 'Staff', 19)");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager', id FROM permissions WHERE slug='my_profile'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'staff', id FROM permissions WHERE slug='my_profile'");
});

_migrate($conn, 'rbac_my_profile_v2', function($db) {
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'barista', id FROM permissions WHERE slug='my_profile'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'supervisor', id FROM permissions WHERE slug='my_profile'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'inventory_clerk', id FROM permissions WHERE slug='my_profile'");
});

_migrate($conn, 'rbac_barista_station_recon_v1', function($db) {
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Barista Station', 'barista_station', 'Operations', 20)");
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Cash Count', 'cash_reconciliation', 'Analytics', 21)");
    // Barista station: all operational roles
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'admin',    id FROM permissions WHERE slug='barista_station'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager',  id FROM permissions WHERE slug='barista_station'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'supervisor',id FROM permissions WHERE slug='barista_station'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'staff',    id FROM permissions WHERE slug='barista_station'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'barista',  id FROM permissions WHERE slug='barista_station'");
    // Cash reconciliation report: managers and admins only
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'admin',   id FROM permissions WHERE slug='cash_reconciliation'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'manager', id FROM permissions WHERE slug='cash_reconciliation'");
});

// â”€â”€ Add customer_display permission â”€â”€
_migrate($conn, 'rbac_customer_display_v1', function($db) {
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Customer Display', 'customer_display', 'Operations', 21)");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'supervisor', id FROM permissions WHERE slug='customer_display'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'staff',      id FROM permissions WHERE slug='customer_display'");
    $db->query("INSERT IGNORE INTO role_permissions (role, permission_id) SELECT 'barista',    id FROM permissions WHERE slug='customer_display'");
});

// â”€â”€ Remove barista_station from management roles (they use full dashboard, not barista display) â”€â”€
_migrate($conn, 'rbac_barista_station_mgmt_fix_v1', function($db) {
    $db->query("DELETE rp FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE p.slug = 'barista_station'
                  AND rp.role IN ('admin', 'manager', 'supervisor')");
});

// â”€â”€ Drop legacy token_number_old column (unused) â”€â”€
_migrate($conn, 'orders_drop_token_number_old_v1', function($db) {
    $db->query("ALTER TABLE orders DROP COLUMN IF EXISTS token_number_old");
});

// â”€â”€ Remove redundant Table Management (cafe_tables) â€” superseded by stand numbers â”€â”€
_migrate($conn, 'remove_cafe_tables_v1', function($db) {
    $db->query("DELETE rp FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE p.slug = 'tables'");
    $db->query("DELETE FROM permissions WHERE slug = 'tables'");
    $db->query("DROP TABLE IF EXISTS cafe_tables");
});

// â”€â”€ Migrate role_permissions: replace role VARCHAR with role_id INT FK â”€â”€
_migrate($conn, 'rbac_role_permissions_int_fk_v1', function($db) {
    // Add role_id column (idempotent)
    $chk = $db->query("SHOW COLUMNS FROM role_permissions LIKE 'role_id'");
    if ($chk && $chk->num_rows === 0) $db->query("ALTER TABLE role_permissions ADD COLUMN role_id INT NULL");
    if ($db->errno) return;

    // Populate role_id from slug
    $db->query("UPDATE role_permissions rp JOIN roles r ON r.slug = rp.role SET rp.role_id = r.id WHERE rp.role_id IS NULL");
    if ($db->errno) return;

    // Remove rows that cannot be migrated â€” orphaned permission_id or unrecognised role slug
    $db->query("DELETE FROM role_permissions WHERE permission_id NOT IN (SELECT id FROM permissions)");
    if ($db->errno) return;
    $db->query("DELETE FROM role_permissions WHERE role_id IS NULL");
    if ($db->errno) return;

    // Restructure: drop old composite PK, add auto-increment id as PK,
    // add created_at, make role_id NOT NULL, drop old role VARCHAR, add FK constraints
    $db->query("ALTER TABLE role_permissions
        DROP PRIMARY KEY,
        ADD COLUMN id INT NOT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (id),
        ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        MODIFY COLUMN role_id INT NOT NULL,
        DROP COLUMN role,
        ADD UNIQUE KEY uq_role_perm (role_id, permission_id),
        ADD CONSTRAINT fk_rp_role FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
        ADD CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE");
});

// â”€â”€ Migrate users: replace role VARCHAR with role_id INT FK â”€â”€
_migrate($conn, 'rbac_users_role_id_v1', function($db) {
    _add_col($db, 'users', 'role_id', 'INT NULL');
    if ($db->errno) return;
    $db->query("UPDATE users u JOIN roles r ON r.slug = u.role SET u.role_id = r.id WHERE u.role_id IS NULL");
    if ($db->errno) return;
    // Fallback: any user whose role slug has no match â†’ map to 'staff'
    $db->query("UPDATE users u JOIN roles r ON r.slug='staff' SET u.role_id = r.id WHERE u.role_id IS NULL");
    if ($db->errno) return;
    $db->query("ALTER TABLE users
        MODIFY COLUMN role_id INT NOT NULL,
        DROP COLUMN role,
        ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)");
});

// â”€â”€ Audit log table â”€â”€
_migrate($conn, 'role_audit_log_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS role_audit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50) NOT NULL,
        role_slug VARCHAR(50) NOT NULL,
        detail TEXT NULL,
        performed_by VARCHAR(100) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_role (role_slug),
        INDEX idx_created (created_at)
    ) DEFAULT CHARSET=utf8mb4");
});

// â”€â”€ Split cancel/refund columns out of orders into dedicated tables â”€â”€
_migrate($conn, 'orders_split_cancel_refund_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS order_cancellations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL UNIQUE,
        cancel_reason VARCHAR(255) NOT NULL,
        cancelled_at DATETIME NOT NULL,
        cancelled_by VARCHAR(100) NOT NULL DEFAULT '',
        CONSTRAINT fk_oc_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("CREATE TABLE IF NOT EXISTS order_refunds (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL UNIQUE,
        refund_amount DECIMAL(10,2) NOT NULL,
        refund_reason VARCHAR(255) NOT NULL DEFAULT '',
        refunded_at DATETIME NOT NULL,
        refunded_by VARCHAR(100) NOT NULL DEFAULT '',
        CONSTRAINT fk_ref_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    // Migrate existing cancellation data
    $db->query("INSERT IGNORE INTO order_cancellations (order_id, cancel_reason, cancelled_at, cancelled_by)
        SELECT order_id, cancel_reason, COALESCE(cancelled_at, NOW()), COALESCE(cancelled_by, '')
        FROM orders WHERE cancel_reason IS NOT NULL AND cancel_reason != ''");
    if ($db->errno) return;

    // Migrate existing refund data
    $db->query("INSERT IGNORE INTO order_refunds (order_id, refund_amount, refund_reason, refunded_at, refunded_by)
        SELECT order_id, refund_amount, COALESCE(refund_reason, ''), COALESCE(refunded_at, NOW()), COALESCE(refunded_by, '')
        FROM orders WHERE is_refunded = 1");
    if ($db->errno) return;

    $db->query("ALTER TABLE orders
        DROP COLUMN cancel_reason,
        DROP COLUMN cancelled_at,
        DROP COLUMN cancelled_by,
        DROP COLUMN refund_amount,
        DROP COLUMN refund_reason,
        DROP COLUMN refunded_at,
        DROP COLUMN refunded_by,
        DROP COLUMN is_refunded");
});

// â”€â”€ Add missing FK constraints across all tables â”€â”€
_migrate($conn, 'add_missing_fks_v1', function($db) {
    // Nullify orphaned rows before attaching FKs
    $db->query("UPDATE orders SET employee_id = NULL WHERE employee_id IS NOT NULL AND employee_id NOT IN (SELECT employee_id FROM employees)");
    if ($db->errno) return;
    $db->query("UPDATE ingredient_history SET order_id = NULL WHERE order_id IS NOT NULL AND order_id NOT IN (SELECT order_id FROM orders)");
    if ($db->errno) return;

    // orders â†’ users / customers / employees (all nullable â†’ SET NULL on delete)
    $db->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_user     FOREIGN KEY (user_id)     REFERENCES users(user_id)           ON DELETE SET NULL");
    if ($db->errno) return;
    $db->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(customer_id)   ON DELETE SET NULL");
    if ($db->errno) return;
    $db->query("ALTER TABLE orders ADD CONSTRAINT fk_orders_employee FOREIGN KEY (employee_id) REFERENCES employees(employee_id)   ON DELETE SET NULL");
    if ($db->errno) return;

    // employees â†’ users (nullable â†’ SET NULL)
    $db->query("ALTER TABLE employees ADD CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL");
    if ($db->errno) return;

    // attendance â†’ users (NOT NULL â†’ RESTRICT so records are preserved)
    $db->query("ALTER TABLE attendance ADD CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT");
    if ($db->errno) return;

    // announcement_reads â†’ users + announcements (CASCADE: delete reads when parent goes)
    $db->query("ALTER TABLE announcement_reads ADD CONSTRAINT fk_ar_user         FOREIGN KEY (user_id)         REFERENCES users(user_id)   ON DELETE CASCADE");
    if ($db->errno) return;
    $db->query("ALTER TABLE announcement_reads ADD CONSTRAINT fk_ar_announcement FOREIGN KEY (announcement_id) REFERENCES announcements(id) ON DELETE CASCADE");
    if ($db->errno) return;

    // cash_counts â†’ users (RESTRICT: keep financial history)
    $db->query("ALTER TABLE cash_counts ADD CONSTRAINT fk_cr_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT");
    if ($db->errno) return;

    // ingredients â†’ suppliers (nullable â†’ SET NULL when supplier deleted)
    $db->query("ALTER TABLE ingredients ADD CONSTRAINT fk_ingredients_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL");
    if ($db->errno) return;

    // ingredient_daily_stock â†’ ingredients (CASCADE: stock rows belong to ingredient)
    $db->query("ALTER TABLE ingredient_daily_stock ADD CONSTRAINT fk_ids_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE");
    if ($db->errno) return;

    // ingredient_history â†’ ingredients (RESTRICT) + orders (nullable â†’ SET NULL)
    $db->query("ALTER TABLE ingredient_history ADD CONSTRAINT fk_ih_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE RESTRICT");
    if ($db->errno) return;
    $db->query("ALTER TABLE ingredient_history ADD CONSTRAINT fk_ih_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL");
    if ($db->errno) return;

    // order_remakes â†’ orders (CASCADE: remakes belong to the order)
    $db->query("ALTER TABLE order_remakes ADD CONSTRAINT fk_or_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE");
    if ($db->errno) return;

    // stock_refills â†’ ingredients (RESTRICT: keep refill history)
    $db->query("ALTER TABLE stock_refills ADD CONSTRAINT fk_sr_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE RESTRICT");
});

// â”€â”€ Add category_id FK to products (categories table already exists) â”€â”€
_migrate($conn, 'products_category_fk_v1', function($db) {
    _add_col($db, 'products', 'category_id', 'INT NULL');
    if ($db->errno) return;
    // Populate from slug match (all existing slugs match exactly)
    $db->query("UPDATE products p JOIN categories c ON c.slug = p.category SET p.category_id = c.category_id WHERE p.category_id IS NULL");
    if ($db->errno) return;
    $db->query("ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL");
});

// â”€â”€ Rename cash_reconciliations â†’ cash_counts (legacy installs only) â”€â”€
_migrate($conn, 'rename_cash_reconciliations_to_cash_counts_v1', function($db) {
    // Only rename when the old table still exists and the new one doesn't.
    // Fresh installs already create cash_counts directly above, so this is a no-op there.
    $hasOld = $db->query("SHOW TABLES LIKE 'cash_reconciliations'")->num_rows > 0;
    $hasNew = $db->query("SHOW TABLES LIKE 'cash_counts'")->num_rows > 0;
    if ($hasOld && !$hasNew) {
        $db->query("RENAME TABLE cash_reconciliations TO cash_counts");
    }
});

// â”€â”€ Drop the zombie cash_reconciliations table â”€â”€
// A stale CREATE used to recreate it (empty) on every page load after the rename
// above had already moved real data to cash_counts. The CREATE now targets
// cash_counts, so this one-time drop sticks. Safe: no code writes the old table.
_migrate($conn, 'drop_zombie_cash_reconciliations_v1', function($db) {
    $db->query("DROP TABLE IF EXISTS cash_reconciliations");
});

// â”€â”€ Rename permission display name â”€â”€
_migrate($conn, 'rename_permission_cash_reconciliation_to_cash_count_v1', function($db) {
    $db->query("UPDATE permissions SET name = 'Cash Count' WHERE slug = 'cash_reconciliation'");
});

// â”€â”€ Remove test permission â”€â”€
_migrate($conn, 'delete_test_permission_only_sigma_boy_v3', function($db) {
    $db->query("DELETE FROM permissions WHERE name = 'OnlySigmaBoy' OR slug IN ('only_sigma_boy','onlysigmaboy')");
    $db->query("DELETE FROM role_permissions WHERE permission_id NOT IN (SELECT id FROM permissions)");
});

// â”€â”€ Stock Count: tables + permission + role grants â”€â”€
_migrate($conn, 'stock_count_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS stock_counts (
        count_id      INT AUTO_INCREMENT PRIMARY KEY,
        business_date DATE NOT NULL,
        status        ENUM('draft','submitted') NOT NULL DEFAULT 'draft',
        created_by    VARCHAR(100) NULL,
        submitted_by  VARCHAR(100) NULL,
        submitted_at  DATETIME NULL,
        notes         TEXT NULL,
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_business_date (business_date)
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("CREATE TABLE IF NOT EXISTS stock_count_items (
        item_id       INT AUTO_INCREMENT PRIMARY KEY,
        count_id      INT NOT NULL,
        ingredient_id INT NOT NULL,
        opening_stock DECIMAL(10,4) NOT NULL DEFAULT 0,
        system_used   DECIMAL(10,4) NOT NULL DEFAULT 0,
        expected_qty  DECIMAL(10,4) NOT NULL DEFAULT 0,
        actual_qty    DECIMAL(10,4) NULL,
        variance      DECIMAL(10,4) NULL,
        UNIQUE KEY uq_count_ingredient (count_id, ingredient_id),
        CONSTRAINT fk_sci_count      FOREIGN KEY (count_id)      REFERENCES stock_counts(count_id)          ON DELETE CASCADE,
        CONSTRAINT fk_sci_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id)      ON DELETE RESTRICT
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Stock Count', 'stock_count', 'Reconciliation', 22)");
    if ($db->errno) return;

    // Grant to admin, manager, inventory_clerk, supervisor by default
    foreach (['admin','manager','inventory_clerk','supervisor'] as $role) {
        $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.slug='$role' AND p.slug='stock_count'");
    }
});

_migrate($conn, 'stock_count_module_fix_v2', function($db) {
    $db->query("UPDATE permissions SET module='Reconciliation' WHERE slug IN ('stock_count','cash_reconciliation')");
});

// â”€â”€ Re-grant barista_station via role_id â”€â”€
// The legacy rbac_barista_station_recon_v1 inserted into a `role` (slug) column
// that was later dropped in favour of role_id, so those grants silently failed
// and NO role actually held barista_station â€” only admin (can() bypass) could
// reach barista_display.php. Re-grant to the operational roles using role_id.
// admin bypasses can(), so it does not need an explicit row.
_migrate($conn, 'rbac_barista_station_roleid_v1', function($db) {
    foreach (['barista', 'manager'] as $role) {
        $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.slug='$role' AND p.slug='barista_station'");
    }
});

// â”€â”€ Drink sizes: products.has_sizes, product_sizes table, order_items size columns â”€â”€
_migrate($conn, 'drink_sizes_v1', function($db) {
    _add_col($db, 'products', 'has_sizes', 'TINYINT(1) NOT NULL DEFAULT 0');
    if ($db->errno) return;

    $db->query("CREATE TABLE IF NOT EXISTS product_sizes (
        size_id     INT(11) NOT NULL AUTO_INCREMENT,
        product_id  INT(11) NOT NULL,
        size_code   VARCHAR(10) NOT NULL,
        label       VARCHAR(20) NOT NULL,
        price       DECIMAL(10,2) NOT NULL,
        size_factor DECIMAL(4,2) NOT NULL DEFAULT 1.00,
        sort_order  INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (size_id),
        UNIQUE KEY uq_product_size (product_id, size_code),
        CONSTRAINT fk_product_sizes_product FOREIGN KEY (product_id)
            REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    _add_col($db, 'order_items', 'size_code',  'VARCHAR(10) NULL');
    _add_col($db, 'order_items', 'size_label', 'VARCHAR(20) NULL');
});

// â”€â”€ Loyalty history: widen type ENUM so adjustment rows store correctly â”€â”€
// Code writes 'adjusted_add'/'adjusted_deduct' (cancel reversal, order-edit point sync).
// The original ENUM lacked them â†’ on strict-mode MySQL those INSERTs fail; on lax mode
// they silently stored ''. Add the values so every loyalty path records accurately.
_migrate($conn, 'loyalty_history_type_enum_v1', function($db) {
    $db->query("ALTER TABLE loyalty_history MODIFY COLUMN type ENUM('earned','redeemed','bonus','created','adjusted_add','adjusted_deduct') NOT NULL");
});

// â”€â”€ Add image column to categories + permission â”€â”€
_migrate($conn, 'categories_image_v2', function($db) {
    $has = $db->query("SHOW COLUMNS FROM categories LIKE 'image'")->num_rows > 0;
    if (!$has) $db->query("ALTER TABLE categories ADD image VARCHAR(255) DEFAULT NULL AFTER icon");
    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Categories', 'categories', 'Inventory', 13)");
    foreach (['admin','manager','inventory_clerk'] as $role) {
        $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.slug='$role' AND p.slug='categories'");
    }
});

_migrate($conn, 'categories_customization_toggles_v1', function($db) {
    $cols = ['enable_ice' => 1, 'enable_sugar' => 1, 'enable_milk' => 1, 'enable_addons' => 1];
    foreach ($cols as $col => $def) {
        $chk = $db->query("SHOW COLUMNS FROM categories LIKE '$col'");
        if ($chk && $chk->num_rows === 0) {
            $db->query("ALTER TABLE categories ADD `$col` TINYINT(1) NOT NULL DEFAULT $def");
        }
    }
});

// â”€â”€ SANITIZE FUNCTION â”€â”€

// ---- Size / Ice / Sugar levels ----
_migrate($conn, 'customize_levels_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS size_levels (
        id            INT(11) NOT NULL AUTO_INCREMENT,
        name          VARCHAR(50) NOT NULL,
        display_order INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;
    $db->query("INSERT IGNORE INTO size_levels (id, name, display_order) VALUES (1, 'Small', 1), (2, 'Medium', 2), (3, 'Large', 3)");

    $db->query("CREATE TABLE IF NOT EXISTS ice_levels (
        id            INT(11) NOT NULL AUTO_INCREMENT,
        name          VARCHAR(50) NOT NULL,
        display_order INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("CREATE TABLE IF NOT EXISTS sugar_levels (
        id            INT(11) NOT NULL AUTO_INCREMENT,
        name          VARCHAR(50) NOT NULL,
        display_order INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Customize Levels', 'manage_levels', 'Inventory', 19)");
    foreach (['admin','manager'] as $role) {
        $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.slug='$role' AND p.slug='manage_levels'");
    }
});

// ---- Default size levels seed ----
_migrate($conn, 'size_levels_seed_v1', function($db) {
    $db->query("INSERT IGNORE INTO size_levels (id, name, display_order) VALUES (1, 'Small', 1), (2, 'Medium', 2), (3, 'Large', 3)");
});

// ---- Product ice/sugar level columns ----
_migrate($conn, 'product_levels_v1', function($db) {
    if (!$db->query("SHOW COLUMNS FROM products LIKE 'ice_level_id'")->num_rows) {
        $db->query("ALTER TABLE products ADD ice_level_id INT(11) DEFAULT NULL AFTER has_sizes");
        $db->query("ALTER TABLE products ADD sugar_level_id INT(11) DEFAULT NULL AFTER ice_level_id");
    }
});

// ---- Product ice/sugar level pivot tables (many-to-many) ----
_migrate($conn, 'product_levels_pivot_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS product_ice_levels (
        product_id INT(11) NOT NULL,
        ice_level_id INT(11) NOT NULL,
        PRIMARY KEY (product_id, ice_level_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db->query("CREATE TABLE IF NOT EXISTS product_sugar_levels (
        product_id INT(11) NOT NULL,
        sugar_level_id INT(11) NOT NULL,
        PRIMARY KEY (product_id, sugar_level_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($db->query("SHOW COLUMNS FROM products LIKE 'ice_level_id'")->num_rows) {
        $db->query("ALTER TABLE products DROP COLUMN ice_level_id");
        $db->query("ALTER TABLE products DROP COLUMN sugar_level_id");
    }
});

// ---- Link product_sizes to size_levels ----
_migrate($conn, 'product_sizes_level_id_v1', function($db) {
    if (!$db->query("SHOW COLUMNS FROM product_sizes LIKE 'size_level_id'")->num_rows) {
        $db->query("ALTER TABLE product_sizes ADD size_level_id INT(11) DEFAULT NULL AFTER product_id");
        $db->query("UPDATE product_sizes SET size_level_id = CASE size_code WHEN 'S' THEN 1 WHEN 'M' THEN 2 WHEN 'L' THEN 3 END WHERE size_code IN ('S','M','L')");
    }
});
_migrate($conn, 'product_sizes_promo_pct_v1', function($db) {
    $chk = $db->query("SHOW COLUMNS FROM `product_sizes` LIKE 'promo_pct'");
    if ($chk && $chk->num_rows === 0) {
        $db->query("ALTER TABLE `product_sizes` ADD COLUMN `promo_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00");
    }
});

// ---- Milk levels ----
_migrate($conn, 'milk_levels_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS milk_levels (
        id            INT(11) NOT NULL AUTO_INCREMENT,
        name          VARCHAR(50) NOT NULL,
        display_order INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;
    $db->query("INSERT IGNORE INTO milk_levels (id, name, display_order) VALUES (1, 'Fresh Milk', 1), (2, 'Almond Milk', 2), (3, 'Soy Milk', 3), (4, 'Oat Milk', 4)");
});

// ---- Product milk level pivot table ----
_migrate($conn, 'product_milk_pivot_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS product_milk_levels (
        product_id INT(11) NOT NULL,
        milk_level_id INT(11) NOT NULL,
        PRIMARY KEY (product_id, milk_level_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
});

// ---- Sugar column on order_items ----
_migrate($conn, 'order_items_sugar_v1', function($db) {
    if (!$db->query("SHOW COLUMNS FROM order_items LIKE 'sugar'")->num_rows) {
        $db->query("ALTER TABLE order_items ADD sugar VARCHAR(50) DEFAULT NULL AFTER ice");
    }
});

// ---- Addons + Addon Ingredients ----
_migrate($conn, 'addons_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS addons (
        addon_id   INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(100) NOT NULL,
        price      DECIMAL(10,2) NOT NULL DEFAULT 0,
        image      VARCHAR(255) DEFAULT NULL,
        is_active  TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("CREATE TABLE IF NOT EXISTS addon_ingredients (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        addon_id      INT NOT NULL,
        ingredient_id INT NOT NULL,
        amount_used   DECIMAL(10,4) NOT NULL DEFAULT 0,
        UNIQUE KEY uq_addon_ingredient (addon_id, ingredient_id),
        CONSTRAINT fk_ai_addon      FOREIGN KEY (addon_id)      REFERENCES addons(addon_id)          ON DELETE CASCADE,
        CONSTRAINT fk_ai_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE RESTRICT
    ) DEFAULT CHARSET=utf8mb4");
    if ($db->errno) return;

    $db->query("INSERT IGNORE INTO permissions (name, slug, module, sort_order) VALUES ('Addons', 'addons', 'Inventory', 23)");
    foreach (['admin','manager','inventory_clerk'] as $role) {
        $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r, permissions p
            WHERE r.slug='$role' AND p.slug='addons'");
    }
});

// ---- Product <-> Addon pivot (which addons a product offers) ----
_migrate($conn, 'product_addons_pivot_v1', function($db) {
    $db->query("CREATE TABLE IF NOT EXISTS product_addons (
        product_id INT(11) NOT NULL,
        addon_id   INT(11) NOT NULL,
        PRIMARY KEY (product_id, addon_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
});

if (!function_exists('sanitizeForReceipt')) {
    function sanitizeForReceipt(string $text): string {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//IGNORE', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text ?? '');
        return trim($text);
    }
}

// â”€â”€ LOYALTY SYSTEM FUNCTIONS â”€â”€

if (!function_exists('generateLoyaltyId')) {
    function generateLoyaltyId() {
        return 'CARD-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('getLoyaltyCard')) {
    function getLoyaltyCard($conn, $loyalty_id) {
        $stmt = $conn->prepare("
            SELECT * FROM loyalty_cards
            WHERE loyalty_id = ? AND is_active = 1
        ");
        $stmt->bind_param("s", $loyalty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

if (!function_exists('getLoyaltyHistory')) {
    function getLoyaltyHistory($conn, $card_id, $limit = 10) {
        $stmt = $conn->prepare("
            SELECT * FROM loyalty_history
            WHERE card_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $card_id, $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}

if (!function_exists('getAvailableRewards')) {
    function getAvailableRewards($conn) {
        $stmt = $conn->prepare("SELECT * FROM rewards WHERE is_active = 1 ORDER BY points_required ASC");
        $stmt->execute();
        return $stmt->get_result();
    }
}

// â”€â”€ RBAC: can() â€” check if current session role has a permission â”€â”€
if (!function_exists('can')) {
    function can(string $slug): bool {
        global $conn;
        static $perms    = null;
        static $is_admin = null;
        if ($is_admin === null) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $role     = $_SESSION['role'] ?? 'staff';
            $is_admin = ($role === 'admin');
            if (!$is_admin) {
                $perms = [];
                $r = $conn->prepare("SELECT p.slug FROM permissions p JOIN role_permissions rp ON rp.permission_id=p.id JOIN roles ro ON ro.id=rp.role_id WHERE ro.slug=?");
                $r->bind_param("s", $role);
                $r->execute();
                $res = $r->get_result();
                while ($row = $res->fetch_assoc()) $perms[$row['slug']] = true;
            }
        }
        return $is_admin || isset($perms[$slug]);
    }
}

// â”€â”€ Strip CSS rules that conflict with the dashboard layout â”€â”€
if (!function_exists('clean_embed_styles')) {
    function clean_embed_styles(string $css): string {
        // Remove CSS comments
        $css = preg_replace('/\/\*.*?\*\//s', '', $css);
        // Build a list of selectors to entirely remove
        $kill = [
            '*', 'html', 'body',
            '.sidebar', '.sidebar-header', '.sidebar-nav',
            '.nav-group-label', '.nav-group-items', '.nav-item',
            '.main',
        ];
        // Remove simple rules: selector { ... } â€” these never nest
        foreach ($kill as $sel) {
            $esc = preg_quote($sel, '/');
            $css = preg_replace('/' . $esc . '\s*\{[^}]*\}/i', '', $css);
        }
        // Remove entire @media blocks that reference a killed selector.
        // CSS @media rules only nest one level deep, so handle that.
        $css = preg_replace_callback('/@media[^{]*\{(?:[^{}]|\{[^{}]*\})*\}/s', function ($m) {
            $kill_re = '/(sidebar|nav-item|nav-group|nav-label|\.main|\bbody\b)/i';
            return preg_match($kill_re, $m[0]) ? '' : $m[0];
        }, $css);
        // Clean up empty rules and extra whitespace
        $css = preg_replace('/[^},]\s*\{\s*\}/', '', $css);
        $css = preg_replace('/\n{2,}/', "\n", $css);
        return trim($css);
    }
}

// â”€â”€ AJAX embed mode helpers â”€â”€
if (!function_exists('start_embed')) {
    function start_embed(): bool {
        $embed = isset($_GET['embed']);
        if ($embed) ob_start();
        return $embed;
    }
}
if (!function_exists('end_embed')) {
    function end_embed(): void {
        if (isset($_GET['embed'])) {
            $html = ob_get_clean();
            // Extract <style> blocks from <head>
            $styles = '';
            if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $m)) {
                foreach ($m[1] as $s) {
                    // :root kept as-is so [data-theme="light"] cascade works correctly
                    $s = clean_embed_styles($s);
                    if (trim($s)) $styles .= '<style>' . $s . '</style>' . "\n";
                }
            }
            // Extract everything after <body> (</body> was stripped when we replaced </body></html> with end_embed())
            $start = strpos($html, '<body');
            $body  = '';
            if ($start !== false) {
                $start = strpos($html, '>', $start) + 1;
                $end = strrpos($html, '</body>');
                if ($end === false) {
                    $body = rtrim(substr($html, $start));
                } else {
                    $body = substr($html, $start, $end - $start);
                }
            }
            echo $styles . $body;
            return;
        }
        // Non-embed: close the tags (the page's own </body></html> was replaced with this call)
        echo '</body></html>';
    }
}
?>