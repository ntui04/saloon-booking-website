<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function hash_password($password) {
    return password_hash($password, HASH_ALGO);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: admin/login.php');
        exit();
    }
}

// Database helper functions
function get_db_connection() {
    $database = new Database();
    return $database->getConnection();
}

function execute_query($sql, $params = []) {
    $conn = get_db_connection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetch_one($sql, $params = []) {
    $stmt = execute_query($sql, $params);
    return $stmt->fetch();
}

function fetch_all($sql, $params = []) {
    $stmt = execute_query($sql, $params);
    return $stmt->fetchAll();
}

// Utility functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_time($time) {
    return date('g:i A', strtotime($time));
}

function get_booking_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-success">Confirmed</span>',
        'completed' => '<span class="badge badge-info">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    return $badges[$status] ?? $status;
}

// Email functions (basic implementation)
function send_email($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function send_booking_confirmation($booking_id) {
    $booking = fetch_one("
        SELECT b.*, u.email, u.first_name, s.name as service_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN services s ON b.service_id = s.id 
        WHERE b.id = ?
    ", [$booking_id]);
    
    if ($booking) {
        $subject = "Booking Confirmation - " . SITE_NAME;
        $message = "
            <h2>Booking Confirmed!</h2>
            <p>Dear {$booking['first_name']},</p>
            <p>Your booking has been confirmed:</p>
            <ul>
                <li>Service: {$booking['service_name']}</li>
                <li>Date: " . format_date($booking['booking_date']) . "</li>
                <li>Time: " . format_time($booking['booking_time']) . "</li>
                <li>Total: " . format_currency($booking['total_amount']) . "</li>
            </ul>
            <p>We look forward to seeing you!</p>
        ";
        
        return send_email($booking['email'], $subject, $message);
    }
    return false;
}

// File upload functions
function upload_file($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $filename = uniqid() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}
?>
