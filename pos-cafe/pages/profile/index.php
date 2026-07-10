<?php
require __DIR__ . '/../../../auth.php';
require __DIR__ . '/../../../config.php';

$user_id  = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';
$role     = $_SESSION['role']     ?? 'staff';

$stmt = $conn->prepare("SELECT username, security_question, must_change_password FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$rn = $conn->prepare("SELECT name, color, icon FROM roles WHERE slug = ?");
$rn->bind_param("s", $role);
$rn->execute();
$rn_row       = $rn->get_result()->fetch_assoc();
$role_display = $rn_row['name']  ?? ucfirst(str_replace('_', ' ', $role));
$role_color   = $rn_row['color'] ?? '#888888';
$role_icon    = $rn_row['icon']  ?? 'fa-user';

$toast      = '';
$toast_type = '';
$active_tab = $_POST['active_tab'] ?? 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $active_tab  = 'password';
        $current     = $_POST['current_password']  ?? '';
        $new_pass    = $_POST['new_password']       ?? '';
        $confirm     = $_POST['confirm_password']   ?? '';

        $stmt2 = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $row = $stmt2->get_result()->fetch_assoc();

        if (!password_verify($current, $row['password'])) {
            $toast = "Current password is incorrect.";
            $toast_type = 'error';
        } elseif ($new_pass !== $confirm) {
            $toast = "New passwords do not match.";
            $toast_type = 'error';
        } elseif (strlen($new_pass) < 8
               || !preg_match('/[A-Z]/', $new_pass)
               || !preg_match('/[0-9]/', $new_pass)
               || !preg_match('/[^a-zA-Z0-9]/', $new_pass)) {
            $toast = "Password must be at least 8 characters and include an uppercase letter, a number, and a symbol.";
            $toast_type = 'error';
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt3  = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE user_id = ?");
            $stmt3->bind_param("si", $hashed, $user_id);
            $stmt3->execute();
            $toast = "Password updated successfully!";
            $toast_type = 'success';
            $user['must_change_password'] = 0;
        }
    }

    elseif ($action === 'save_security') {
        $active_tab = 'security';
        $question   = trim($_POST['security_question'] ?? '');
        $answer     = strtolower(trim($_POST['security_answer'] ?? ''));
        $verify_pass = $_POST['verify_password'] ?? '';

        $stmt2 = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $row = $stmt2->get_result()->fetch_assoc();

        if (!password_verify($verify_pass, $row['password'])) {
            $toast = "Password verification failed.";
            $toast_type = 'error';
        } elseif (empty($question) || strlen($answer) < 2) {
            $toast = "Please enter both a question and an answer.";
            $toast_type = 'error';
        } else {
            $hashed_ans = password_hash($answer, PASSWORD_DEFAULT);
            $stmt3 = $conn->prepare("UPDATE users SET security_question = ?, security_answer = ? WHERE user_id = ?");
            $stmt3->bind_param("ssi", $question, $hashed_ans, $user_id);
            $stmt3->execute();
            $toast = "Security question saved successfully!";
            $toast_type = 'success';
            $user['security_question'] = $question;
        }
    }
}

$must_change = (bool)($user['must_change_password'] ?? 0);
$has_sq      = !empty($user['security_question']);
$home_url = ($role === 'barista') ? '/FinalSystem/pos-cafe/orders/board' : 'dashboard.php';
?>
<?php $embed_mode = start_embed(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | Bird's Nest Coffee</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>(function(){try{if(localStorage.getItem("theme")==="light")document.documentElement.setAttribute("data-theme","light");}catch(e){}})();</script>
<?php require __DIR__ . '/_styles.php'; ?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    darkMode: 'class',
    important: true,
    theme: {
        extend: {
            colors: {
                accent: '#14B8A6',
                'accent-light': '#5EEAD4',
                'accent-dark': '#0D9488',
            }
        }
    }
}
</script>
</head>
<body>
<div class="vo-flex-wrap" style="display:flex; min-height:100vh;">
<?php $navActive = 'profile.php'; ?>
<?php require __DIR__ . '/../../components/sidebar/index.php'; ?>
<div class="vo-main-col" style="flex:1; min-width:0;">

<style>
#appSidebar { background: #111111 !important; border-right: none !important; }
[data-theme="light"] #appSidebar { background: #111111 !important; border-right: none !important; }
[data-theme="light"] #appSidebar .nav-link { color: #9aa1ac !important; }
[data-theme="light"] #appSidebar .nav-link:hover { color: #5eead4 !important; }
[data-theme="light"] #appSidebar .nav-link.active { color: #ffffff !important; }
</style>

<?php require __DIR__ . '/_topbar.php'; ?>

<div class="page-wrap">
    <?php require __DIR__ . '/_banners.php'; ?>
    <?php require __DIR__ . '/_cards.php'; ?>
</div>

<?php if ($toast): ?>
<div class="toast <?= $toast_type ?>" id="toastEl">
    <i class="fa-solid fa-<?= $toast_type === 'success' ? 'circle-check' : 'circle-exclamation' ?>"></i>
    <span><?= htmlspecialchars($toast) ?></span>
</div>
<script>
setTimeout(function(){
    var t = document.getElementById('toastEl');
    if(t){t.style.animation='toastOut .4s ease forwards';setTimeout(()=>t.remove(),400);}
}, 4000);
</script>
<?php endif; ?>

<?php require __DIR__ . '/_scripts.php'; ?>
</div>
</div>
<?php end_embed(); ?>
