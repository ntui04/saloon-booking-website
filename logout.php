<?php
require_once 'includes/functions.php';

// Destroy session
session_destroy();
redirect('index.php');
?>
