<?php
require_once '../includes/functions.php';
require_admin_login();

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Get booking details
$booking = fetch_one("
    SELECT b.*, u.first_name, u.last_name, u.email, u.phone, s.name as service_name, s.duration, s.category
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    WHERE b.id = ?
", [$booking_id]);

if (!$booking) {
    redirect('messages.php');
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $message_text = sanitize_input($_POST['message']);
    
    if (!empty($message_text)) {
        $stmt = execute_query("
            INSERT INTO messages (booking_id, sender_type, sender_id, message) 
            VALUES (?, 'admin', ?, ?)
        ", [$booking_id, $_SESSION['admin_id'], $message_text]);
        
        if ($stmt) {
            $message = 'Message sent successfully!';
        } else {
            $error = 'Failed to send message.';
        }
    } else {
        $error = 'Message cannot be empty.';
    }
}

// Handle admin notes update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_notes'])) {
    $admin_notes = sanitize_input($_POST['admin_notes']);
    
    $stmt = execute_query("UPDATE bookings SET admin_notes = ? WHERE id = ?", [$admin_notes, $booking_id]);
    
    if ($stmt) {
        $message = 'Notes updated successfully!';
        $booking['admin_notes'] = $admin_notes;
    } else {
        $error = 'Failed to update notes.';
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

// Mark messages as read
execute_query("UPDATE messages SET is_read = 1 WHERE booking_id = ? AND sender_type = 'user'", [$booking_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: var(--sidebar);
            border-right: 1px solid var(--sidebar-border);
            padding: 2rem 0;
        }
        .sidebar-header {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }
        .sidebar-nav {
            list-style: none;
            padding: 0;
        }
        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }
        .sidebar-nav a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--sidebar-foreground);
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: var(--sidebar-accent);
            color: var(--sidebar-accent-foreground);
        }
        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: var(--muted);
        }
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .messages-container {
            background: var(--background);
            border-radius: var(--radius);
            padding: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 1rem;
        }
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: var(--radius);
        }
        .message.admin {
            background-color: var(--primary);
            color: var(--primary-foreground);
            margin-left: 2rem;
        }
        .message.user {
            background-color: var(--muted);
            color: var(--foreground);
            margin-right: 2rem;
        }
        .message-header {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            opacity: 0.8;
        }
        .message-form {
            background: var(--background);
            padding: 1.5rem;
            border-radius: var(--radius);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2 style="color: var(--sidebar-primary); margin: 0;"><?php echo SITE_NAME; ?></h2>
                <p style="color: var(--sidebar-foreground); font-size: 0.9rem; margin: 0.5rem 0 0 0;">Admin Panel</p>
            </div>
            <nav>
                <ul class="sidebar-nav">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bookings.php">Bookings</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="messages.php" class="active">Messages</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--foreground); margin: 0;">Booking Details</h1>
                <a href="messages.php" class="btn btn-secondary">Back to Messages</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Booking Information -->
            <div class="booking-details">
                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
                </div>
                
                <div class="card">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Booking Information</h3>
                    <p><strong>Service:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($booking['category']); ?></p>
                    <p><strong>Duration:</strong> <?php echo $booking['duration']; ?> minutes</p>
                    <p><strong>Date:</strong> <?php echo format_date($booking['booking_date']); ?></p>
                    <p><strong>Time:</strong> <?php echo format_time($booking['booking_time']); ?></p>
                    <p><strong>Amount:</strong> <?php echo format_currency($booking['total_amount']); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge badge-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </p>
                </div>
            </div>

            <?php if ($booking['notes']): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">Customer Notes</h3>
                    <p><?php echo htmlspecialchars($booking['notes']); ?></p>
                </div>
            <?php endif; ?>

            <!-- Admin Notes -->
            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="color: var(--primary); margin-bottom: 1rem;">Admin Notes</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <textarea name="admin_notes" class="form-control" rows="3" 
                                  placeholder="Add internal notes about this booking..."><?php echo htmlspecialchars($booking['admin_notes']); ?></textarea>
                    </div>
                    <button type="submit" name="update_notes" class="btn btn-primary">Update Notes</button>
                </form>
            </div>

            <!-- Messages -->
            <div class="card">
                <h3 style="color: var(--primary); margin-bottom: 1rem;">Conversation</h3>
                
                <div class="messages-container">
                    <?php if (empty($messages)): ?>
                        <p style="color: var(--muted-foreground); text-align: center;">No messages yet.</p>
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
                <div class="message-form">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="message" class="form-label">Send Message to Customer</label>
                            <textarea id="message" name="message" class="form-control" rows="3" 
                                      placeholder="Type your message here..." required></textarea>
                        </div>
                        <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-scroll messages to bottom
        const messagesContainer = document.querySelector('.messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    </script>
</body>
</html>
