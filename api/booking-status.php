<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Simple API endpoint to check booking status
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    
    $booking = fetch_one("
        SELECT b.status, b.confirmed_at, s.name as service_name 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        WHERE b.id = ?
    ", [$booking_id]);
    
    if ($booking) {
        echo json_encode([
            'success' => true,
            'status' => $booking['status'],
            'service_name' => $booking['service_name'],
            'confirmed_at' => $booking['confirmed_at']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Booking not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?>
