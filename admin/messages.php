<?php
require_once '../includes/functions.php';
require_admin_login();

// Get all bookings with message counts
$bookings_with_messages = fetch_all("
    SELECT 
        b.id, b.booking_date, b.booking_time, b.status,
        u.first_name, u.last_name, u.email, u.phone,
        s.name as service_name,
        COUNT(m.id) as message_count,
        MAX(m.created_at) as last_message_time
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    LEFT JOIN messages m ON b.id = m.booking_id
    GROUP BY b.id
    HAVING message_count > 0 OR b.status IN ('pending', 'confirmed')
    ORDER BY last_message_time DESC, b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - <?php echo SITE_NAME; ?></title>
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
        .message-card {
            background: var(--background);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .customer-info h4 {
            margin: 0 0 0.5rem 0;
            color: var(--primary);
        }
        .customer-info p {
            margin: 0;
            color: var(--muted-foreground);
            font-size: 0.9rem;
        }
        .booking-info {
            text-align: right;
            font-size: 0.9rem;
            color: var(--muted-foreground);
        }
        .message-count {
            background-color: var(--primary);
            color: var(--primary-foreground);
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
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
            <h1 style="color: var(--foreground); margin-bottom: 2rem;">Customer Messages</h1>

            <?php if (empty($bookings_with_messages)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <h3 style="color: var(--muted-foreground); margin-bottom: 1rem;">No messages yet</h3>
                    <p style="color: var(--muted-foreground);">Customer messages will appear here when they contact you about their bookings.</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookings_with_messages as $booking): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="customer-info">
                                <h4><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></h4>
                                <p><?php echo htmlspecialchars($booking['email']); ?></p>
                                <p><?php echo htmlspecialchars($booking['phone']); ?></p>
                            </div>
                            <div class="booking-info">
                                <p><strong><?php echo htmlspecialchars($booking['service_name']); ?></strong></p>
                                <p><?php echo format_date($booking['booking_date']); ?> at <?php echo format_time($booking['booking_time']); ?></p>
                                <span class="status-badge badge-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <?php if ($booking['message_count'] > 0): ?>
                                    <span class="message-count"><?php echo $booking['message_count']; ?> messages</span>
                                    <?php if ($booking['last_message_time']): ?>
                                        <span style="color: var(--muted-foreground); font-size: 0.9rem; margin-left: 1rem;">
                                            Last: <?php echo format_date($booking['last_message_time']); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--muted-foreground);">No messages yet</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">
                                    View Conversation
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
