<?php
// App root - route to the right place.
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /FinalSystem/pos-cafe/dashboard');
} else {
    header('Location: login.php');
}
exit;
