-- Insert default admin user
INSERT INTO admins (username, email, password, full_name, role) VALUES 
('admin', 'admin@salon.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Salon Administrator', 'super_admin');

-- Insert sample services
INSERT INTO services (name, description, price, duration, category, image_url) VALUES 
('Hair Cut & Style', 'Professional haircut with styling', 45.00, 60, 'Hair', '/images/haircut.jpg'),
('Hair Color', 'Full hair coloring service', 85.00, 120, 'Hair', '/images/haircolor.jpg'),
('Manicure', 'Classic manicure with nail polish', 25.00, 45, 'Nails', '/images/manicure.jpg'),
('Pedicure', 'Relaxing pedicure treatment', 35.00, 60, 'Nails', '/images/pedicure.jpg'),
('Facial Treatment', 'Deep cleansing facial', 65.00, 75, 'Skincare', '/images/facial.jpg'),
('Eyebrow Threading', 'Precise eyebrow shaping', 20.00, 30, 'Beauty', '/images/eyebrow.jpg'),
('Makeup Application', 'Professional makeup for special events', 55.00, 45, 'Beauty', '/images/makeup.jpg');

-- Insert available time slots for next 30 days
INSERT INTO time_slots (slot_date, slot_time) 
SELECT 
    DATE_ADD(CURDATE(), INTERVAL seq.seq DAY) as slot_date,
    TIME(CONCAT(hour.hour, ':00:00')) as slot_time
FROM 
    (SELECT 0 seq UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29) seq
CROSS JOIN 
    (SELECT 9 hour UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17) hour
WHERE 
    DAYOFWEEK(DATE_ADD(CURDATE(), INTERVAL seq.seq DAY)) NOT IN (1, 7); -- Exclude Sunday (1) and Saturday (7)
