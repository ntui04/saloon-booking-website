<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/functions.php';

$username = 'newadmin';
$password = '12345678';

$admin = fetch_one("SELECT * FROM admins WHERE username = ? AND is_active = 1", [$username]);
var_dump($admin);

if ($admin) {
    echo "\nPassword verification result: " . (verify_password($password, $admin['password']) ? "TRUE" : "FALSE");
}
?>