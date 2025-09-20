<?php
require_once '../includes/functions.php';
require_admin_login();

// Get all users with booking counts
$users = fetch_all("
    SELECT u.*, 
           COUNT(b.id) as total_bookings,
           SUM(CASE WHEN b.status = 'completed' THEN b.total_amount ELSE 0 END) as total_spent
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bookings.php">Bookings</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="users.php" class="active">Users</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 style="color: var(--foreground); margin-bottom: 2rem;">Manage Users</h1>

            <!-- Users Table -->
            <div class="card">
                <?php if (empty($users)): ?>
                    <p style="color: var(--muted-foreground); text-align: center; padding: 2rem;">No users found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Total Bookings</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo $user['total_bookings']; ?></td>
                                    <td><?php echo format_currency($user['total_spent']); ?></td>
                                    <td><?php echo format_date($user['created_at']); ?></td>
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
