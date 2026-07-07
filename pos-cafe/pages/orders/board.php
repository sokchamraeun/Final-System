<?php
declare(strict_types=1);
/* Orders board — live order queue (KDS) for staff/barista/cashier/admin.
   Call/cancel/refund/remake/delete mutations live in api/orders-board.php;
   this page only renders the board and its supporting components. */
require __DIR__ . '/../../config/app.php';
require POS_ROOT . '/middleware/auth.php';
$required_permission = 'view_orders';
require POS_ROOT . '/middleware/permission.php';

$navActive = 'orders-board';

date_default_timezone_set('Asia/Phnom_Penh');

$_flash_welcome = !empty($_SESSION['flash_welcome']); unset($_SESSION['flash_welcome']);

// Role labels (used by board-render.php's roleLabel() map)
$_vo_role   = $_SESSION['role'] ?? 'staff';
$_all_roles = [];
$_ar_res = $conn->query("SELECT slug, name, color, icon FROM roles");
while ($_ar = $_ar_res->fetch_assoc()) $_all_roles[$_ar['slug']] = $_ar;

// Clock-in status
$_is_clocked_in = false;
$_clock_since   = null;
$_att_check = $conn->query("SHOW TABLES LIKE 'attendance'");
if ($_att_check && $_att_check->num_rows > 0) {
    $_cs = $conn->prepare("SELECT clock_in FROM attendance WHERE user_id = ? AND date = CURDATE() AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
    $_cs->bind_param('i', $_SESSION['user_id']);
    $_cs->execute();
    $_crow = $_cs->get_result()->fetch_assoc();
    if ($_crow) { $_is_clocked_in = true; $_clock_since = date('g:i A', strtotime($_crow['clock_in'])); }
}

// ── Real-time socket availability (suppress console errors when offline) ──
if (!defined('SOCKET_URL')) {
    define('SOCKET_URL', 'http://localhost:3000');
}
$_socketAvailable = false;
$_fp = @fsockopen('localhost', 3000, $_errno, $_errstr, 0.3);
if ($_fp) { $_socketAvailable = true; fclose($_fp); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bird's Nest Coffee — Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>(function(){try{if(localStorage.getItem("theme")==="light")document.documentElement.setAttribute("data-theme","light");}catch(e){}})();</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { sans: ['Poppins', 'sans-serif'] },
          colors: {
            brand:   { DEFAULT: '#F59E0B', 600: '#D97706' },
            teal:    { DEFAULT: '#0F766E' },
            sidebar: '#111827',
          },
        },
      },
    };
    </script>
    <?php component('orders/board-styles'); ?>
</head>
<body>

<div class="vo-flex-wrap">
<?php require POS_ROOT . '/components/sidebar/index.php'; ?>
<style>
/* Keep sidebar black always. Must render after the sidebar component's own
   <style> block — with matching !important specificity, CSS ties resolve
   in favor of whichever rule appears later in source order. */
#appSidebar { background: #000000 !important; border-right: none !important; }
[data-theme="light"] #appSidebar { background: #000000 !important; border-right: none !important; }
[data-theme="light"] #appSidebar .nav-link { color: #888 !important; }
[data-theme="light"] #appSidebar .nav-link:hover { color: #d1904b !important; }
[data-theme="light"] #appSidebar .nav-link.active { color: #d1904b !important; }
[data-theme="light"] #appSidebar .group-label { color: #888 !important; }
[data-theme="light"] #appSidebar .group-label:hover { color: #f5f5f5 !important; }
</style>

<div class="vo-main-col">

<?php
component('orders/board-header', [
    '_is_clocked_in' => $_is_clocked_in,
    '_clock_since'   => $_clock_since,
]);
component('orders/board-page-title');
component('orders/board-stats');
component('orders/board-announcements');
component('orders/board-search', ['_vo_role' => $_vo_role]);
component('orders/board-modals');
component('orders/board-render', ['_all_roles' => $_all_roles]);
component('orders/board-actions', [
    '_socketAvailable' => $_socketAvailable,
    '_flash_welcome'   => $_flash_welcome,
]);
?>

</div><!-- /vo-main-col -->
</div><!-- /vo-flex-wrap -->
</body>
</html>
