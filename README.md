# Bella Beauty Salon - Booking System

A comprehensive salon booking website built with PHP, HTML, and CSS. Features a complete booking system for customers and a powerful admin panel for salon management.

## Features

### Customer Features
- **User Registration & Login** - Secure account creation and authentication
- **Service Browsing** - View all available salon services organized by category
- **Online Booking** - Easy appointment scheduling with real-time availability
- **Booking Management** - View, track, and manage appointments
- **Messaging System** - Communicate directly with salon staff about bookings

### Admin Features
- **Secure Admin Panel** - Strong authentication system for salon staff
- **Booking Management** - Confirm, cancel, and track all customer bookings
- **Service Management** - Full CRUD operations for salon services
- **Customer Communication** - Message customers and manage inquiries
- **User Management** - View customer information and booking history
- **Dashboard Analytics** - Overview of bookings, revenue, and key metrics

### Technical Features
- **Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Security** - Password hashing, input sanitization, and CSRF protection
- **Database Integration** - MySQL database with proper relationships
- **Email Notifications** - Automatic booking confirmations
- **File Upload** - Image management for services
- **Real-time Updates** - Dynamic booking availability

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone or Download** the project files to your web server directory

2. **Database Setup**
   - Create a new MySQL database named `salon_booking`
   - Run the SQL scripts in the `scripts/` folder:
     - First run `01_create_database.sql`
     - Then run `02_seed_data.sql`

3. **Configuration**
   - Update database credentials in `config/database.php`
   - Modify site settings in `config/config.php`

4. **File Permissions**
   - Ensure the `uploads/` directory is writable (755 or 777)
   - Set proper permissions for the entire project

5. **Admin Access**
   - Default admin login: `admin` / `password`
   - **Important**: Change the default password immediately after installation

## File Structure

\`\`\`
salon-booking/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Admin dashboard
│   ├── bookings.php       # Booking management
│   ├── services.php       # Service management
│   └── ...
├── assets/
│   └── css/
│       └── style.css      # Main stylesheet
├── config/
│   ├── database.php       # Database configuration
│   └── config.php         # Site configuration
├── includes/
│   └── functions.php      # Core functions
├── scripts/
│   ├── 01_create_database.sql
│   └── 02_seed_data.sql
├── uploads/               # File upload directory
├── index.php             # Homepage
├── booking.php           # Booking form
├── services.php          # Services listing
├── login.php             # User login
├── register.php          # User registration
└── my-bookings.php       # User booking management
\`\`\`

## Usage

### For Customers
1. Visit the website and browse available services
2. Register for an account or login
3. Select a service and choose an available time slot
4. Complete the booking form
5. Receive confirmation and track your appointment status
6. Use the messaging system to communicate with the salon

### For Salon Staff
1. Access the admin panel at `/admin/login.php`
2. Login with your admin credentials
3. View the dashboard for an overview of bookings and statistics
4. Manage bookings - confirm, cancel, or mark as completed
5. Add, edit, or remove services
6. Communicate with customers through the messaging system
7. View customer information and booking history

## Security Features

- **Password Hashing** - All passwords are securely hashed using PHP's password_hash()
- **Input Sanitization** - All user inputs are sanitized to prevent XSS attacks
- **SQL Injection Prevention** - Prepared statements used for all database queries
- **Session Management** - Secure session handling with timeout
- **File Upload Security** - Restricted file types and secure upload handling
- **Admin Authentication** - Strong authentication system for admin access

## Customization

### Styling
- Modify `assets/css/style.css` to change the appearance
- The design uses CSS custom properties for easy color scheme changes
- Responsive design adapts to all screen sizes

### Configuration
- Update site name, email, and other settings in `config/config.php`
- Modify database connection settings in `config/database.php`
- Customize email templates in the functions file

### Services
- Add new service categories through the admin panel
- Upload service images for better presentation
- Set pricing and duration for each service

## Support

For technical support or questions about the salon booking system:
- Check the code comments for detailed explanations
- Ensure all file permissions are set correctly
- Verify database connection settings
- Check PHP error logs for troubleshooting

## License

This salon booking system is provided as-is for educational and commercial use. Feel free to modify and customize according to your needs.
