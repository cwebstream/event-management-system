# Event Management System

A comprehensive event management solution with multiple modules for handling various aspects of event operations.

## Features

- Super Admin and Event Admin roles
- Event creation and configuration
- Module-based functionality:
  - Check-in System
  - Access Zone Control
  - F&B Distribution
  - Goodies Distribution
  - Digital Wallet
  - Analytics Dashboard
- Real-time statistics and reporting
- Modern, responsive interface using Tailwind CSS

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/event-management-system.git
cd event-management-system
```

2. Create a MySQL database:
```sql
CREATE DATABASE event_management;
```

3. Import the database schema:
```bash
mysql -u your_username -p event_management < database.sql
```

4. Configure database connection:
   - Open `config.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'event_management');
     ```

5. Set up the web server:
   - Configure your web server to serve the application
   - Ensure the document root points to the project directory

6. Create Super Admin account:
```sql
INSERT INTO super_admins (username, password) 
VALUES ('admin', '$2y$10$YOUR_HASHED_PASSWORD');
```

## Usage

1. Access the system through your web browser:
```
http://your-domain/login.php
```

2. Log in as Super Admin:
   - Use the credentials created in step 6 of installation
   - Create new events and configure modules
   - Generate Event Admin accounts

3. Event Admin Access:
   - Log in through admin_login.php
   - Access assigned modules based on permissions
   - Manage event operations

## Module Overview

### Check-in System
- Record participant arrivals
- View check-in history
- Track attendance statistics

### Access Zone Control
- Manage entry to different areas
- Track zone occupancy
- Monitor access patterns

### F&B Distribution
- Track food and beverage distribution
- Monitor inventory
- View consumption patterns

### Goodies Distribution
- Record swag bag distribution
- Prevent duplicate collections
- Track distribution progress

### Digital Wallet
- Handle digital currency transactions
- Credit and debit operations
- View transaction history

### Analytics Dashboard
- Real-time event statistics
- Visual data representation
- Downloadable reports

## Security

- Password hashing using PHP's password_hash()
- SQL injection prevention
- Session-based authentication
- Role-based access control

## Directory Structure

```
event-management-system/
├── config.php           # Database and system configuration
├── database.sql        # Database schema
├── index.php          # Super Admin dashboard
├── login.php          # Super Admin login
├── admin_login.php    # Event Admin login
├── modules/           # Module-specific files
│   ├── checkin.php
│   ├── access_zones.php
│   ├── fb_counter.php
│   ├── goodies_counter.php
│   ├── wallet_system.php
│   └── analytics.php
└── README.md          # Documentation
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
