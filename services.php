<?php
require_once 'includes/functions.php';

// Get all active services grouped by category
$services_by_category = [];
$services = fetch_all("SELECT * FROM services WHERE is_active = 1 ORDER BY category, name");

foreach ($services as $service) {
    $services_by_category[$service['category']][] = $service;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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

    <!-- Page Header -->
    <section style="background: linear-gradient(135deg, var(--card) 0%, var(--background) 100%); padding: 3rem 0; text-align: center;">
        <div class="container">
            <h1 style="color: var(--primary); margin-bottom: 1rem;">Our Premium Services</h1>
            <p style="font-size: 1.2rem; color: var(--muted-foreground);">Discover our full range of beauty and wellness treatments</p>
        </div>
    </section>

    <!-- Services by Category -->
    <div class="container" style="margin: 4rem auto;">
        <?php foreach ($services_by_category as $category => $category_services): ?>
            <section style="margin-bottom: 4rem;">
                <h2 style="color: var(--primary); margin-bottom: 2rem; text-align: center; font-size: 2rem;">
                    <?php echo htmlspecialchars($category); ?>
                </h2>
                <div class="services-grid">
                    <?php foreach ($category_services as $service): ?>
                        <div class="service-card">
                            <img src="<?php echo $service['image_url'] ?: '/placeholder.svg?height=200&width=300&query=' . urlencode($service['name']); ?>" 
                                 alt="<?php echo htmlspecialchars($service['name']); ?>" 
                                 class="service-image">
                            <div class="service-content">
                                <h3 class="service-title"><?php echo htmlspecialchars($service['name']); ?></h3>
                                <p style="color: var(--muted-foreground); margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($service['description']); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <span class="service-price"><?php echo format_currency($service['price']); ?></span>
                                    <span style="color: var(--muted-foreground); font-weight: 500;">
                                        <?php echo $service['duration']; ?> minutes
                                    </span>
                                </div>
                                <a href="booking.php?service=<?php echo $service['id']; ?>" class="btn btn-primary" style="width: 100%;">
                                    Book This Service
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Call to Action -->
    <section style="background-color: var(--primary); color: var(--primary-foreground); padding: 4rem 0; text-align: center;">
        <div class="container">
            <h2 style="margin-bottom: 1rem;">Ready to Book Your Appointment?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">Experience the luxury and professionalism you deserve</p>
            <a href="booking.php" class="btn" style="background-color: var(--background); color: var(--primary); font-weight: 600;">
                Book Now
            </a>
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
