<?php
require_once 'includes/functions.php';

$error = '';
$success = '';
$selected_service_id = isset($_GET['service']) ? (int)$_GET['service'] : 0;

// Get all services for the dropdown
$services = fetch_all("SELECT * FROM services WHERE is_active = 1 ORDER BY category, name");

// Get available time slots for the next 30 days
$available_slots = fetch_all("
    SELECT DISTINCT slot_date, slot_time 
    FROM time_slots 
    WHERE is_available = 1 
    AND slot_date >= CURDATE() 
    AND slot_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY slot_date, slot_time
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!is_logged_in()) {
        $error = 'Please log in to make a booking.';
    } else {
        $service_id = (int)$_POST['service_id'];
        $booking_date = sanitize_input($_POST['booking_date']);
        $booking_time = sanitize_input($_POST['booking_time']);
        $notes = sanitize_input($_POST['notes']);
        
        // Validation
        if (empty($service_id) || empty($booking_date) || empty($booking_time)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Check if the time slot is still available
            $slot_check = fetch_one("
                SELECT * FROM time_slots 
                WHERE slot_date = ? AND slot_time = ? AND is_available = 1
            ", [$booking_date, $booking_time]);
            
            if (!$slot_check) {
                $error = 'The selected time slot is no longer available.';
            } else {
                // Get service details
                $service = fetch_one("SELECT * FROM services WHERE id = ?", [$service_id]);
                
                if ($service) {
                    // Create booking
                    $stmt = execute_query("
                        INSERT INTO bookings (user_id, service_id, booking_date, booking_time, notes, total_amount) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$_SESSION['user_id'], $service_id, $booking_date, $booking_time, $notes, $service['price']]);
                    
                    if ($stmt) {
                        // Mark time slot as unavailable
                        execute_query("
                            UPDATE time_slots 
                            SET is_available = 0 
                            WHERE slot_date = ? AND slot_time = ?
                        ", [$booking_date, $booking_time]);
                        
                        $success = 'Booking request submitted successfully! We will contact you soon to confirm.';
                    } else {
                        $error = 'Booking failed. Please try again.';
                    }
                } else {
                    $error = 'Invalid service selected.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - <?php echo SITE_NAME; ?></title>
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

    <!-- Booking Form -->
    <div class="container" style="max-width: 600px; margin: 4rem auto;">
        <div class="card">
            <h2 style="text-align: center; color: var(--primary); margin-bottom: 2rem;">Book Your Appointment</h2>
            
            <?php if (!is_logged_in()): ?>
                <div class="alert alert-info">
                    Please <a href="login.php" style="color: var(--primary);">login</a> or 
                    <a href="register.php" style="color: var(--primary);">register</a> to make a booking.
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="bookingForm">
                    <div class="form-group">
                        <label for="service_id" class="form-label">Select Service *</label>
                        <select id="service_id" name="service_id" class="form-control" required>
                            <option value="">Choose a service...</option>
                            <?php 
                            $current_category = '';
                            foreach ($services as $service): 
                                if ($current_category != $service['category']):
                                    if ($current_category != '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($service['category']) . '">';
                                    $current_category = $service['category'];
                                endif;
                            ?>
                                <option value="<?php echo $service['id']; ?>" 
                                        data-price="<?php echo $service['price']; ?>"
                                        data-duration="<?php echo $service['duration']; ?>"
                                        <?php echo ($selected_service_id == $service['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service['name']); ?> - <?php echo format_currency($service['price']); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($current_category != '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_date" class="form-label">Select Date *</label>
                        <select id="booking_date" name="booking_date" class="form-control" required>
                            <option value="">Choose a date...</option>
                            <?php 
                            $dates = array_unique(array_column($available_slots, 'slot_date'));
                            foreach ($dates as $date): 
                            ?>
                                <option value="<?php echo $date; ?>">
                                    <?php echo format_date($date); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_time" class="form-label">Select Time *</label>
                        <select id="booking_time" name="booking_time" class="form-control" required>
                            <option value="">Choose a time...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Special Requests (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" 
                                  placeholder="Any special requests or notes for your appointment..."></textarea>
                    </div>
                    
                    <div id="booking-summary" style="background-color: var(--muted); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem; display: none;">
                        <h4 style="color: var(--primary); margin-bottom: 0.5rem;">Booking Summary</h4>
                        <p id="summary-service"></p>
                        <p id="summary-datetime"></p>
                        <p id="summary-price" style="font-weight: 600; color: var(--secondary);"></p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Booking Request</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Available slots data
        const availableSlots = <?php echo json_encode($available_slots); ?>;
        
        // Update time slots when date is selected
        document.getElementById('booking_date').addEventListener('change', function() {
            const selectedDate = this.value;
            const timeSelect = document.getElementById('booking_time');
            
            // Clear existing options
            timeSelect.innerHTML = '<option value="">Choose a time...</option>';
            
            if (selectedDate) {
                // Filter slots for selected date
                const slotsForDate = availableSlots.filter(slot => slot.slot_date === selectedDate);
                
                slotsForDate.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.slot_time;
                    option.textContent = formatTime(slot.slot_time);
                    timeSelect.appendChild(option);
                });
            }
            
            updateSummary();
        });
        
        // Update summary when form changes
        document.getElementById('service_id').addEventListener('change', updateSummary);
        document.getElementById('booking_time').addEventListener('change', updateSummary);
        
        function updateSummary() {
            const serviceSelect = document.getElementById('service_id');
            const dateSelect = document.getElementById('booking_date');
            const timeSelect = document.getElementById('booking_time');
            const summary = document.getElementById('booking-summary');
            
            if (serviceSelect.value && dateSelect.value && timeSelect.value) {
                const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
                const serviceName = selectedOption.text.split(' - ')[0];
                const price = selectedOption.dataset.price;
                const duration = selectedOption.dataset.duration;
                
                document.getElementById('summary-service').textContent = `Service: ${serviceName} (${duration} min)`;
                document.getElementById('summary-datetime').textContent = `Date & Time: ${dateSelect.options[dateSelect.selectedIndex].text} at ${formatTime(timeSelect.value)}`;
                document.getElementById('summary-price').textContent = `Total: $${parseFloat(price).toFixed(2)}`;
                
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
            }
        }
        
        function formatTime(time) {
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${ampm}`;
        }
    </script>
</body>
</html>
