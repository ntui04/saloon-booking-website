<?php
session_start();
require_once '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    echo "Sanitized username: $username<br>"; // Debug
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $admin = fetch_one("SELECT * FROM admins WHERE username = ? AND is_active = 1", [$username]);
        
        echo "Query result: ";
        var_dump($admin); // Debug
        
        if ($admin) {
            if (verify_password($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_login_time'] = time();
                redirect('dashboard.php');
            } else {
                $error = 'Password is incorrect.';
            }
        } else {
            $error = 'Username not found or account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: var(--background);
            padding: 3rem;
            border-radius: calc(var(--radius) * 2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .admin-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .admin-logo h1 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .admin-logo p {
            color: var(--muted-foreground);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="admin-logo">
            <h1><?php echo SITE_NAME; ?></h1>
            <p>Admin Portal</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Login to Admin Panel
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
            <a href="../index.php" style="color: var(--muted-foreground); text-decoration: none; font-size: 0.9rem;">
                ← Back to Website
            </a>
        </div>
    </div>
</body>
</html>