<?php
require_once 'includes/functions.php';
require_login();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Get booking details (only user's own bookings)
$booking = fetch_one("
    SELECT b.*, s.name as service_name, s.duration, s.category
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.id = ? AND b.user_id = ?
", [$booking_id, $_SESSION['user_id']]);

if (!$booking) {
    redirect('my-bookings.php');
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message_text = sanitize_input($_POST['message']);
    
    if (!empty($message_text)) {
        $stmt = execute_query("
            INSERT INTO messages (booking_id, sender_type, sender_id, message) 
            VALUES (?, 'user', ?, ?)
        ", [$booking_id, $_SESSION['user_id'], $message_text]);
        
        if ($stmt) {
            $message = 'Message sent successfully! We will respond soon.';
        } else {
            $error = 'Failed to send message.';
        }
    } else {
        $error = 'Message cannot be empty.';
    }
}

// Get all messages for this booking
$messages = fetch_all("
    SELECT m.*, 
           CASE 
               WHEN m.sender_type = 'admin' THEN a.full_name
               ELSE CONCAT(u.first_name, ' ', u.last_name)
           END as sender_name
    FROM messages m
    LEFT JOIN admins a ON m.sender_type = 'admin' AND m.sender_id = a.id
    LEFT JOIN users u ON m.sender_type = 'user' AND m.sender_id = u.id
    WHERE m.booking_id = ?
    ORDER BY m.created_at ASC
", [$booking_id]);

// Mark admin messages as read
execute_query("UPDATE messages SET is_read = 1 WHERE booking_id = ? AND sender_type = 'admin'", [$booking_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message About Booking - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .messages-container {
            background: var(--card);
            border-radius: var(--radius);
            padding: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: var(--radius);
        }
        .message.admin {
            background-color: var(--primary);
            color: var(--primary-foreground);
            margin-right: 2rem;
        }
        .message.user {
            background-color: var(--muted);
            color: var(--foreground);
            margin-left: 2rem;
        }
        .message-header {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }
        .booking-summary {
            background: var(--card);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="booking.php">Book Now</a></li>
                    <li><a href="my-bookings.php">My Bookings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Message Interface -->
    <div class="container" style="max-width: 800px; margin: 4rem auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: var(--primary);">Message About Your Booking</h1>
            <a href="my-bookings.php" class="btn btn-secondary">Back to My Bookings</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Booking Summary -->
        <div class="booking-summary">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Booking Details</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <strong>Service:</strong><br>
                    <?php echo htmlspecialchars($booking['service_name']); ?>
                </div>
                <div>
                    <strong>Date & Time:</strong><br>
                    <?php echo format_date($booking['booking_date']) . ' at ' . format_time($booking['booking_time']); ?>
                </div>
                <div>
                    <strong>Status:</strong><br>
                    <span class="status-badge badge-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
                <div>
                    <strong>Total:</strong><br>
                    <?php echo format_currency($booking['total_amount']); ?>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="card">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Conversation</h3>
            
            <div class="messages-container">
                <?php if (empty($messages)): ?>
                    <p style="color: var(--muted-foreground); text-align: center;">No messages yet. Send a message below to start the conversation.</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_type']; ?>">
                            <div class="message-header">
                                <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong>
                                <span style="float: right;"><?php echo format_date($msg['created_at']) . ' ' . format_time($msg['created_at']); ?></span>
                            </div>
                            <div><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Send Message Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="message" class="form-label">Send Message</label>
                    <textarea id="message" name="message" class="form-control" rows="4" 
                              placeholder="Ask questions about your booking, request changes, or share any concerns..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Auto-scroll messages to bottom
        const messagesContainer = document.querySelector('.messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    </script>
</body>
</html>
