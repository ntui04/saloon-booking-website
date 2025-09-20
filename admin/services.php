<?php
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle service deletion
if (isset($_GET['delete'])) {
    $service_id = (int)$_GET['delete'];
    $stmt = execute_query("UPDATE services SET is_active = 0 WHERE id = ?", [$service_id]);
    if ($stmt) {
        $message = 'Service deleted successfully!';
    } else {
        $error = 'Failed to delete service.';
    }
}

// Get all services
$services = fetch_all("SELECT * FROM services ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - <?php echo SITE_NAME; ?></title>
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
        .service-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius);
        }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
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
                    <li><a href="services.php" class="active">Services</a></li>
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
                <h1 style="color: var(--foreground); margin: 0;">Manage Services</h1>
                <a href="add-service.php" class="btn btn-primary">Add New Service</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Services Table -->
            <div class="card">
                <?php if (empty($services)): ?>
                    <p style="color: var(--muted-foreground); text-align: center; padding: 2rem;">No services found.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Service Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr style="<?php echo !$service['is_active'] ? 'opacity: 0.6;' : ''; ?>">
                                    <td>
                                        <img src="<?php echo $service['image_url'] ?: '/placeholder.svg?height=60&width=60&query=' . urlencode($service['name']); ?>" 
                                             alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                             class="service-image">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['name']); ?></strong><br>
                                        <small style="color: var(--muted-foreground);">
                                            <?php echo htmlspecialchars(substr($service['description'], 0, 50)); ?>...
                                        </small>
                                    </td>
                                    <td><?php echo htmlspecialchars($service['category']); ?></td>
                                    <td><?php echo format_currency($service['price']); ?></td>
                                    <td><?php echo $service['duration']; ?> min</td>
                                    <td>
                                        <span class="status-badge <?php echo $service['is_active'] ? 'badge-confirmed' : 'badge-cancelled'; ?>">
                                            <?php echo $service['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit-service.php?id=<?php echo $service['id']; ?>" 
                                               class="btn btn-primary btn-sm">Edit</a>
                                            <?php if ($service['is_active']): ?>
                                                <a href="services.php?delete=<?php echo $service['id']; ?>" 
                                                   class="btn btn-secondary btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
                                            <?php endif; ?>
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
