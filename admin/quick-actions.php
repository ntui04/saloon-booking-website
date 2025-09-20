<?php
require_once '../includes/functions.php';
require_admin_login();

// Handle quick actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $booking_id = (int)$_POST['booking_id'];
    
    switch ($action) {
        case 'confirm':
            execute_query("
                UPDATE bookings 
                SET status = 'confirmed', confirmed_by = ?, confirmed_at = NOW() 
                WHERE id = ?
            ", [$_SESSION['admin_id'], $booking_id]);
            send_booking_confirmation($booking_id);
            break;
            
        case 'cancel':
            execute_query("UPDATE bookings SET status = 'cancelled' WHERE id = ?", [$booking_id]);
            break;
            
        case 'complete':
            execute_query("UPDATE bookings SET status = 'completed' WHERE id = ?", [$booking_id]);
            break;
    }
    
    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Get pending bookings for quick actions
$pending_bookings = fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, u.phone, s.name as service_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    WHERE b.status = 'pending'
    ORDER BY b.booking_date ASC, b.booking_time ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Actions - <?php echo SITE_NAME; ?></title>
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
        .quick-action-card {
            background: var(--background);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
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
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 style="color: var(--foreground); margin-bottom: 2rem;">Quick Actions - Pending Bookings</h1>

            <?php if (empty($pending_bookings)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <h3 style="color: var(--muted-foreground); margin-bottom: 1rem;">No pending bookings</h3>
                    <p style="color: var(--muted-foreground);">All bookings are up to date!</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending_bookings as $booking): ?>
                    <div class="quick-action-card" id="booking-<?php echo $booking['id']; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <h4 style="color: var(--primary); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                </h4>
                                <p style="color: var(--muted-foreground); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($booking['email']); ?> | <?php echo htmlspecialchars($booking['phone']); ?>
                                </p>
                            </div>
                            <span class="status-badge badge-pending">Pending</span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <strong>Service:</strong><br>
                                <?php echo htmlspecialchars($booking['service_name']); ?>
                            </div>
                            <div>
                                <strong>Date & Time:</strong><br>
                                <?php echo format_date($booking['booking_date']) . ' at ' . format_time($booking['booking_time']); ?>
                            </div>
                            <div>
                                <strong>Amount:</strong><br>
                                <?php echo format_currency($booking['total_amount']); ?>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button onclick="quickAction('confirm', <?php echo $booking['id']; ?>)" 
                                    class="btn btn-primary btn-sm">Confirm Booking</button>
                            <button onclick="quickAction('cancel', <?php echo $booking['id']; ?>)" 
                                    class="btn btn-secondary btn-sm">Cancel Booking</button>
                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" 
                               class="btn btn-secondary btn-sm">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function quickAction(action, bookingId) {
            if (action === 'cancel' && !confirm('Are you sure you want to cancel this booking?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('booking_id', bookingId);
            
            fetch('quick-actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the booking card from view
                    const bookingCard = document.getElementById('booking-' + bookingId);
                    bookingCard.style.opacity = '0.5';
                    bookingCard.innerHTML = '<div style="text-align: center; padding: 2rem;"><h4>Action completed successfully!</h4></div>';
                    
                    // Remove after animation
                    setTimeout(() => {
                        bookingCard.remove();
                        
                        // Check if no more bookings
                        const remainingBookings = document.querySelectorAll('.quick-action-card');
                        if (remainingBookings.length === 0) {
                            location.reload();
                        }
                    }, 2000);
                } else {
                    alert('Action failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>
