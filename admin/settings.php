<?php
require_once '../includes/functions.php';
require_admin_login();

$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current admin data
        $admin = fetch_one("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
        
        if (!verify_password($current_password, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (!empty($new_password) && strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            // Update profile
            if (!empty($new_password)) {
                $hashed_password = hash_password($new_password);
                $stmt = execute_query("
                    UPDATE admins 
                    SET full_name = ?, email = ?, password = ? 
                    WHERE id = ?
                ", [$full_name, $email, $hashed_password, $_SESSION['admin_id']]);
            } else {
                $stmt = execute_query("
                    UPDATE admins 
                    SET full_name = ?, email = ? 
                    WHERE id = ?
                ", [$full_name, $email, $_SESSION['admin_id']]);
            }
            
            if ($stmt) {
                $_SESSION['admin_name'] = $full_name;
                $message = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}

// Get current admin data
$admin = fetch_one("SELECT * FROM admins WHERE id = ?", [$_SESSION['admin_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
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
        .form-container {
            max-width: 600px;
            background: var(--background);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                    <li><a href="settings.php" class="active">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <h1 style="color: var(--foreground); margin-bottom: 2rem;">Settings</h1>

            <div class="form-container">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">Update Profile</h3>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" class="form-control" 
                               value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                        <small style="color: var(--muted-foreground);">Username cannot be changed</small>
                    </div>
                    
                    <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">
                    
                    <h4 style="color: var(--primary); margin-bottom: 1rem;">Change Password</h4>
                    <p style="color: var(--muted-foreground); margin-bottom: 1rem;">Leave password fields empty if you don't want to change your password.</p>
                    
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
