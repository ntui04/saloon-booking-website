<?php
require_once '../includes/functions.php';
require_admin_login();

// Get dashboard statistics
$stats = [
    'total_bookings' => fetch_one("SELECT COUNT(*) as count FROM bookings")['count'],
    'pending_bookings' => fetch_one("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'],
    'confirmed_bookings' => fetch_one("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'total_users' => fetch_one("SELECT COUNT(*) as count FROM users")['count'],
    'total_services' => fetch_one("SELECT COUNT(*) as count FROM services WHERE is_active = 1")['count'],
    'today_bookings' => fetch_one("SELECT COUNT(*) as count FROM bookings WHERE booking_date = CURDATE()")['count']
];

// Get recent bookings
$recent_bookings = fetch_all("
    SELECT b.*, u.first_name, u.last_name, u.email, s.name as service_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN services s ON b.service_id = s.id 
    ORDER BY b.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: var(--background);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--muted-foreground);
            font-size: 0.9rem;
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--foreground); margin: 0;">Dashboard</h1>
                <div style="color: var(--muted-foreground);">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_bookings']; ?></div>
                    <div class="stat-label">Pending Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['confirmed_bookings']; ?></div>
                    <div class="stat-label">Confirmed Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['today_bookings']; ?></div>
                    <div class="stat-label">Today's Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_services']; ?></div>
                    <div class="stat-label">Active Services</div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">Recent Bookings</h3>
                <?php if (empty($recent_bookings)): ?>
                    <p style="color: var(--muted-foreground); text-align: center; padding: 2rem;">No bookings yet.</p>
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
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong><br>
                                        <small style="color: var(--muted-foreground);"><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['service_name']); ?></td>
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
                                        <a href="booking-details.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                                            View
                                        </a>
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
