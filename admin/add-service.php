<?php
require_once '../includes/functions.php';
require_admin_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $duration = (int)$_POST['duration'];
    $category = sanitize_input($_POST['category']);
    $image_url = sanitize_input($_POST['image_url']);
    
    // Validation
    if (empty($name) || empty($description) || empty($price) || empty($duration) || empty($category)) {
        $error = 'All fields are required.';
    } elseif ($price <= 0) {
        $error = 'Price must be greater than 0.';
    } elseif ($duration <= 0) {
        $error = 'Duration must be greater than 0.';
    } else {
        // Handle file upload if provided
        $uploaded_image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploaded_image = upload_file($_FILES['image']);
            if ($uploaded_image) {
                $image_url = 'uploads/' . $uploaded_image;
            }
        }
        
        // Insert service
        $stmt = execute_query("
            INSERT INTO services (name, description, price, duration, category, image_url) 
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$name, $description, $price, $duration, $category, $image_url]);
        
        if ($stmt) {
            $success = 'Service added successfully!';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to add service.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service - <?php echo SITE_NAME; ?></title>
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
                <h1 style="color: var(--foreground); margin: 0;">Add New Service</h1>
                <a href="services.php" class="btn btn-secondary">Back to Services</a>
            </div>

            <div class="form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name" class="form-label">Service Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="price" class="form-label">Price ($) *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" 
                                   value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration" class="form-label">Duration (minutes) *</label>
                            <input type="number" id="duration" name="duration" class="form-control" min="1" 
                                   value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category...</option>
                            <option value="Hair" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Hair') ? 'selected' : ''; ?>>Hair</option>
                            <option value="Nails" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Nails') ? 'selected' : ''; ?>>Nails</option>
                            <option value="Skincare" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Skincare') ? 'selected' : ''; ?>>Skincare</option>
                            <option value="Beauty" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Beauty') ? 'selected' : ''; ?>>Beauty</option>
                            <option value="Massage" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Massage') ? 'selected' : ''; ?>>Massage</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image_url" class="form-label">Image URL (Optional)</label>
                        <input type="url" id="image_url" name="image_url" class="form-control" 
                               value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>" 
                               placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">Or Upload Image (Optional)</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <small style="color: var(--muted-foreground);">Max file size: 5MB. Supported formats: JPG, PNG, GIF</small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">Add Service</button>
                        <a href="services.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
