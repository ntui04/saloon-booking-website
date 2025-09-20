<?php
require_once 'includes/functions.php';

// Get featured services
$featured_services = fetch_all("SELECT * FROM services WHERE is_active = 1 LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Premium Beauty Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="booking.php">Book Now</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="my-bookings.php">My Bookings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
            <p>Experience luxury beauty services tailored just for you. Book your appointment today and let us pamper you with our premium treatments.</p>
            <a href="booking.php" class="btn btn-primary">Book Your Appointment</a>
        </div>
    </section>

    <!-- Featured Services -->
    <section class="container" style="margin: 4rem auto;">
        <h2 style="text-align: center; margin-bottom: 3rem; color: var(--primary);">Our Featured Services</h2>
        <div class="services-grid">
            <?php foreach ($featured_services as $service): ?>
                <div class="service-card">
                    <img src="<?php echo $service['image_url'] ?: '/placeholder.svg?height=200&width=300'; ?>" 
                         alt="<?php echo htmlspecialchars($service['name']); ?>" 
                         class="service-image">
                    <div class="service-content">
                        <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p style="color: var(--muted-foreground); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($service['description']); ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="service-price"><?php echo format_currency($service['price']); ?></span>
                            <span style="color: var(--muted-foreground);"><?php echo $service['duration']; ?> min</span>
                        </div>
                        <a href="booking.php?service=<?php echo $service['id']; ?>" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">
                            Book Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- About Section -->
    <section style="background-color: var(--card); padding: 4rem 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
                <div>
                    <h2 style="color: var(--primary); margin-bottom: 1.5rem;">Why Choose Us?</h2>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                            <span style="color: var(--primary); margin-right: 0.5rem;">✓</span>
                            Professional and experienced staff
                        </li>
                        <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                            <span style="color: var(--primary); margin-right: 0.5rem;">✓</span>
                            Premium quality products and equipment
                        </li>
                        <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                            <span style="color: var(--primary); margin-right: 0.5rem;">✓</span>
                            Relaxing and luxurious environment
                        </li>
                        <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                            <span style="color: var(--primary); margin-right: 0.5rem;">✓</span>
                            Flexible scheduling and easy booking
                        </li>
                    </ul>
                </div>
                <div>
                    <img src="/placeholder.svg?height=400&width=500" 
                         alt="Salon Interior" 
                         style="width: 100%; border-radius: var(--radius); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p>Contact us: <?php echo ADMIN_EMAIL; ?> | Phone: (555) 123-4567</p>
        </div>
    </footer>
</body>
</html>
