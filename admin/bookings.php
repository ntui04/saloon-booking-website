<?php
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $booking_id = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    
    if ($action == 'confirm') {
        $stmt = execute_query("
            UPDATE bookings 
            SET status = 'confirmed', confirmed_by = ?, confirmed_at = NOW() 
            WHERE id = ?
        ", [$_SESSION['admin_id'], $booking_id]);
        
        if ($stmt) {
            send_booking_confirmation($booking_id);
            $message = 'Booking confirmed successfully!';
        } else {
            $error = 'Failed to confirm booking.';
        }
    } elseif ($action == 'cancel') {
        $stmt = execute_query("UPDATE bookings SET status = 'cancelled' WHERE id = ?", [$booking_id]);
        if ($stmt) {
            $message = 'Booking cancelled successfully!';
        } else {
            $error = 'Failed to cancel booking.';
        }
    } elseif ($action == 'complete') {
        $stmt = execute_query("UPDATE bookings SET status = 'completed' WHERE id = ?", [$booking_id]);
        if ($stmt) {
            $message = 'Booking marked as completed!';
        } else {
            $error = 'Failed to update booking.';
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "b.status = ?";
    $params[] = $status_filter;
}

if ($date_filter) {
    $where_conditions[] = "b.booking_date = ?";
    $params[] = $date_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get bookings
$bookings = fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, u.phone, s.name as service_name, s.duration 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    $where_clause
    ORDER BY b.booking_date DESC, b.booking_time DESC
", $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo SITE_NAME; ?></title>
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
        .filters {
            background: var(--background);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            background: var(--background);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .table th {
            background-color: var(--muted);
            font-weight: 600;
            color: var(--foreground);
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
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
                    <li><a href="bookings.php" class="active">Bookings</a></li>
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
            <h1 style="color: var(--foreground); margin-bottom: 2rem;">Manage Bookings</h1>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <form method="GET" class="filters">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="date" class="form-label">Filter by Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="bookings.php" class="btn btn-secondary">Clear</a>
            </form>

            <!-- Bookings Table -->
            <div class="card">
                <?php if (empty($bookings)): ?>
                    <p style="color: var(--muted-foreground); text-align: center; padding: 2rem;">No bookings found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong><br>
                                        <small style="color: var(--muted-foreground);">
                                            <?php echo htmlspecialchars($booking['email']); ?><br>
                                            <?php echo htmlspecialchars($booking['phone']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['service_name']); ?><br>
                                        <small style="color: var(--muted-foreground);"><?php echo $booking['duration']; ?> min</small>
                                    </td>
                                    <td>
                                        <?php echo format_date($booking['booking_date']); ?><br>
                                        <small><?php echo format_time($booking['booking_time']); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge badge-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_currency($booking['total_amount']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($booking['status'] == 'pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="confirm">
                                                    <button type="submit" class="btn btn-primary btn-sm">Confirm</button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-secondary btn-sm" 
                                                            onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</button>
                                                </form>
                                            <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="action" value="complete">
                                                    <button type="submit" class="btn btn-primary btn-sm">Complete</button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-secondary btn-sm">View</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
