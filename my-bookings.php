<?php
require_once 'includes/functions.php';
require_login();

// Get user's bookings
$bookings = fetch_all("
    SELECT b.*, s.name as service_name, s.duration, s.category 
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    WHERE b.user_id = ? 
    ORDER BY b.booking_date DESC, b.booking_time DESC
", [$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .booking-card {
            border-left: 4px solid var(--primary);
            margin-bottom: 1.5rem;
        }
        .status-pending { border-left-color: #f59e0b; }
        .status-confirmed { border-left-color: #10b981; }
        .status-completed { border-left-color: #6b7280; }
        .status-cancelled { border-left-color: #ef4444; }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-confirmed { background-color: #d1fae5; color: #065f46; }
        .badge-completed { background-color: #f3f4f6; color: #374151; }
        .badge-cancelled { background-color: #fee2e2; color: #991b1b; }
    </style>
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
                    <li><a href="my-bookings.php">My Bookings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- My Bookings -->
    <div class="container" style="margin: 4rem auto;">
        <h1 style="color: var(--primary); margin-bottom: 2rem;">My Bookings</h1>
        
        <?php if (empty($bookings)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h3 style="color: var(--muted-foreground); margin-bottom: 1rem;">No bookings yet</h3>
                <p style="color: var(--muted-foreground); margin-bottom: 2rem;">Ready to pamper yourself?</p>
                <a href="booking.php" class="btn btn-primary">Book Your First Appointment</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="card booking-card status-<?php echo $booking['status']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="color: var(--primary); margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($booking['service_name']); ?>
                            </h3>
                            <p style="color: var(--muted-foreground); margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($booking['category']); ?> • <?php echo $booking['duration']; ?> minutes
                            </p>
                        </div>
                        <span class="status-badge badge-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <strong>Date:</strong><br>
                            <?php echo format_date($booking['booking_date']); ?>
                        </div>
                        <div>
                            <strong>Time:</strong><br>
                            <?php echo format_time($booking['booking_time']); ?>
                        </div>
                        <div>
                            <strong>Total:</strong><br>
                            <?php echo format_currency($booking['total_amount']); ?>
                        </div>
                        <div>
                            <strong>Booked:</strong><br>
                            <?php echo format_date($booking['created_at']); ?>
                        </div>
                    </div>
                    
                    <?php if ($booking['notes']): ?>
                        <div style="background-color: var(--muted); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem;">
                            <strong>Your Notes:</strong><br>
                            <?php echo htmlspecialchars($booking['notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($booking['admin_notes']): ?>
                        <div style="background-color: var(--card); padding: 1rem; border-radius: var(--radius); border: 1px solid var(--primary); margin-bottom: 1rem;">
                            <strong style="color: var(--primary);">Salon Notes:</strong><br>
                            <?php echo htmlspecialchars($booking['admin_notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Added message button for customer communication -->
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="message-booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-secondary">
                            Send Message
                        </a>
                        <?php if ($booking['status'] == 'pending'): ?>
                            <span style="color: var(--muted-foreground); font-size: 0.9rem;">
                                We'll contact you soon to confirm your appointment
                            </span>
                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                            <span style="color: var(--primary); font-size: 0.9rem;">
                                ✓ Your appointment is confirmed! We look forward to seeing you.
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="booking.php" class="btn btn-primary">Book Another Appointment</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
