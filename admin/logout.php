<?php
require_once '../includes/functions.php';

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);
unset($_SESSION['admin_login_time']);

redirect('login.php');
?>
