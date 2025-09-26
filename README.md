# Hostel Management System

A comprehensive web-based hostel management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides role-based access for administrators and students to manage hostel operations efficiently.

## 🏠 System Overview

This hostel management system allows:
- **Administrators**: Complete control over room management, student assignments, and payment tracking
- **Students**: Room application, payment submission, and dashboard access

## ✨ Features

### 🔐 Authentication System
- User registration and login
- Role-based access control (Admin/Student)
- Secure session management
- Automatic logout functionality

### 👨‍💼 Admin Dashboard
- View all registered students
- Manage room inventory
- Add new rooms with details
- Assign/unassign students to rooms
- Track payment status
- View room occupancy statistics

### 👨‍🎓 Student Dashboard
- View personal profile information
- Apply for available rooms
- Submit payment information
- View room assignment status
- Track payment status

### 🏢 Room Management
- Add rooms with number, type, and capacity
- Track room availability
- Student room assignment workflow
- Room application system for students

### 💰 Payment System
- Student payment submission
- Admin payment status tracking
- Payment verification workflow

### 🎨 User Interface
- Responsive design
- Image slideshow of hostel facilities
- Clean and intuitive navigation
- Mobile-friendly layout

## 🚀 Installation Guide

### Prerequisites
- Web Server (Apache/Nginx) + PHP (7.4 or higher) + MySQL
- Web browser (Chrome, Firefox, etc.)
- Text editor (VS Code recommended)

### Environment Options

#### Option 1: XAMPP (Recommended for Windows)
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your computer
3. Start Apache and MySQL services from XAMPP Control Panel
4. Copy project to `C:\xampp\htdocs\hostel-management-system\`

#### Option 2: WAMP (Windows Alternative)
1. Download WAMP from [http://www.wampserver.com/](http://www.wampserver.com/)
2. Install and start WAMP services
3. Copy project to `C:\wamp64\www\hostel-management-system\`
4. Access via `http://localhost/hostel-management-system/`

#### Option 3: LAMP (Linux)
1. Install LAMP stack:
   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql
   ```
2. Copy project to `/var/www/html/hostel-management-system/`
3. Set proper permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/hostel-management-system/
   sudo chmod -R 755 /var/www/html/hostel-management-system/
   ```

#### Option 4: MAMP (macOS)
1. Download MAMP from [https://www.mamp.info/](https://www.mamp.info/)
2. Install and start MAMP services
3. Copy project to `/Applications/MAMP/htdocs/hostel-management-system/`

#### Option 5: Manual Setup (Any OS)
1. Install Apache Web Server
2. Install PHP (version 7.4+) with MySQL extension
3. Install MySQL Server
4. Configure Apache to serve PHP files
5. Copy project to web server document root

### Step 1: Setup Project Files
Copy the entire `hostel-management-system` folder to your web server's document root:

**XAMPP**: `C:\xampp\htdocs\`
**WAMP**: `C:\wamp64\www\`
**LAMP**: `/var/www/html/`
**MAMP**: `/Applications/MAMP/htdocs/`

Your project structure should be:
```
[web-root]/hostel-management-system/
├── index.html
├── login.php
├── dashboard.php
├── rooms.php
├── students.php
├── config.php
├── create_user.php
├── logout.php
├── style.css
├── nav.css
├── navigation.css
├── rooms.css
├── style_new.css
├── assets/
└── README.md
```

### Step 2: Create Database

#### Method 1: Using phpMyAdmin (XAMPP/WAMP/MAMP)
1. Open your web browser
2. Go to `http://localhost/phpmyadmin` (or `http://localhost:8080/phpmyadmin` for MAMP)
3. Click "New" to create a new database
4. Name it `hostel_management` and click "Create"

#### Method 2: Using MySQL Command Line
```bash
mysql -u root -p
CREATE DATABASE hostel_management;
USE hostel_management;
```

#### Method 3: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your local MySQL server
3. Create new schema named `hostel_management`

### Step 4: Create Database Tables

#### Create Users Table
```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
);
```

#### Create Students Table
```sql
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text,
  `room_id` int(11) DEFAULT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);
```

#### Create Rooms Table
```sql
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `current_occupancy` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`)
);
```

#### Create Room Requests Table
```sql
CREATE TABLE `room_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `room_requests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
);
```

### Step 4: Configure Database Connection
1. Open `config.php` file
2. Update database credentials based on your setup:

#### XAMPP Configuration:
```php
$servername = "localhost";
$username = "root";
$password = "";  // Default XAMPP MySQL password is empty
$dbname = "hostel_management";
```

#### WAMP Configuration:
```php
$servername = "localhost";
$username = "root";
$password = "";  // Default WAMP MySQL password is empty
$dbname = "hostel_management";
```

#### LAMP/Custom Configuration:
```php
$servername = "localhost";
$username = "your_mysql_username";
$password = "your_mysql_password";
$dbname = "hostel_management";
```

#### MAMP Configuration:
```php
$servername = "localhost";
$username = "root";
$password = "root";  // Default MAMP MySQL password is "root"
$dbname = "hostel_management";
```

### Step 5: Create Admin User
You can use the `database_setup.sql` file which includes sample data, or create manually:

#### Method 1: Import database_setup.sql (Recommended)
1. In phpMyAdmin, select `hostel_management` database
2. Click "Import" tab
3. Choose `database_setup.sql` file
4. Click "Go" - this will create all tables and sample data

#### Method 2: Manual Creation
1. Go to `http://localhost/hostel-management-system/create_user.php`
2. Create an admin account with role 'admin'

#### Method 3: Direct SQL Insert
```sql
INSERT INTO `users` (`username`, `email`, `password`, `role`) 
VALUES ('admin', 'admin@hostel.com', MD5('admin123'), 'admin');
```

### Step 6: Access the System

#### Access URLs for Different Setups:
- **XAMPP/WAMP**: `http://localhost/hostel-management-system/`
- **MAMP**: `http://localhost:8888/hostel-management-system/`
- **LAMP**: `http://localhost/hostel-management-system/` or `http://your-server-ip/hostel-management-system/`
- **Custom Setup**: `http://your-domain/hostel-management-system/`

#### Login Process:
1. Open browser and navigate to your system URL
2. Click "Login" and use your credentials
3. Admin users will see the admin dashboard
4. Student users will see the student dashboard

## 🎯 How to Use

### For Administrators:
1. **Login** with admin credentials
2. **Add Rooms**: Go to Rooms section and add new rooms
3. **Manage Students**: View registered students in Students section
4. **Assign Rooms**: Assign available rooms to students
5. **Track Payments**: Monitor payment status of students

### For Students:
1. **Register** as a new student
2. **Login** with student credentials
3. **Apply for Room**: Browse available rooms and apply
4. **Submit Payment**: Update payment information
5. **View Status**: Check room assignment and payment status

## 📁 File Structure

```
hostel-management-system/
├── index.html              # Landing page with slideshow
├── login.php              # User authentication
├── dashboard.php          # Role-based dashboard redirect
├── student_dashboard.php  # Student-specific dashboard
├── rooms.php             # Room management and application
├── students.php          # Student management (admin)
├── payments.php          # Payment management (admin)
├── student_payment.php   # Student payment submission
├── signup.php            # User registration
├── config.php            # Database configuration
├── create_user.php       # Admin user creation
├── logout.php            # Session logout
├── style.css            # Main stylesheet
├── nav.css              # Navigation styles
├── navigation.css       # Additional navigation styles
├── rooms.css            # Room-specific styles
├── style_new.css        # Additional styles
└── assets/              # Images and media files
```

## 🔧 Troubleshooting

### Common Issues:

1. **Database Connection Error**
   - Ensure MySQL service is running (XAMPP/WAMP/LAMP/MAMP)
   - Check database credentials in `config.php`
   - Verify database name is correct
   - Test connection: `mysqli_connect($servername, $username, $password, $dbname)`

2. **Page Not Loading**
   - Ensure Apache/web server is running
   - Check file paths and permissions
   - Verify URL is correct for your setup
   - Check Apache error logs

3. **Login Issues**
   - Check if users table exists and has data
   - Verify password encryption method (MD5)
   - Clear browser cache and cookies
   - Check session configuration in PHP

4. **Permission Errors (Linux/macOS)**
   - Set proper file permissions: `chmod 644 *.php`
   - Set directory permissions: `chmod 755 directories`
   - Ensure web server can read files: `chown www-data:www-data`

5. **Port Conflicts**
   - **XAMPP**: Default ports Apache:80, MySQL:3306
   - **MAMP**: Default ports Apache:8888, MySQL:8889
   - **WAMP**: Default ports Apache:80, MySQL:3306
   - Change ports if conflicts occur

6. **PHP Version Issues**
   - Ensure PHP 7.4 or higher is installed
   - Check PHP extensions: `mysqli`, `session`
   - Verify PHP configuration in `phpinfo()`

## 🚀 Quick Setup Summary

### For XAMPP (Windows - Recommended):
1. **Install XAMPP** → Start Apache & MySQL
2. **Copy project** to `C:\xampp\htdocs\hostel-management-system\`
3. **Create database** `hostel_management` in phpMyAdmin
4. **Import** `database_setup.sql`
5. **Access**: `http://localhost/hostel-management-system/`

### For WAMP (Windows Alternative):
1. **Install WAMP** → Start services
2. **Copy project** to `C:\wamp64\www\hostel-management-system\`
3. **Create database** via phpMyAdmin
4. **Import** `database_setup.sql`
5. **Access**: `http://localhost/hostel-management-system/`

### For LAMP (Linux):
1. **Install LAMP** → `sudo apt install apache2 mysql-server php php-mysql`
2. **Copy project** to `/var/www/html/hostel-management-system/`
3. **Set permissions** → `sudo chown -R www-data:www-data`
4. **Create database** → Import `database_setup.sql`
5. **Access**: `http://localhost/hostel-management-system/`

### For MAMP (macOS):
1. **Install MAMP** → Start services
2. **Copy project** to `/Applications/MAMP/htdocs/hostel-management-system/`
3. **Create database** via phpMyAdmin (port 8080)
4. **Import** `database_setup.sql`
5. **Access**: `http://localhost:8888/hostel-management-system/`

## ☁️ Cloud Deployment Options

### Hosting Platforms:
1. **Shared Hosting** (Hostinger, Bluehost, GoDaddy)
   - Upload files via FTP/cPanel
   - Create MySQL database in hosting panel
   - Import `database_setup.sql`
   - Update `config.php` with hosting credentials

2. **VPS/Cloud Servers** (DigitalOcean, AWS, Google Cloud)
   - Install LAMP stack
   - Upload project files
   - Configure domain/subdomain
   - Set up SSL certificate

3. **Free Hosting** (000webhost, InfinityFree)
   - Limited resources but good for testing
   - Follow shared hosting steps

### Configuration Changes for Production:
```php
// config.php for production
$servername = "your-hosting-mysql-server";
$username = "your-db-username";
$password = "your-secure-password";
$dbname = "your-db-name";

// Enable error reporting only in development
ini_set('display_errors', 0); // Set to 0 for production
```

## 🆕 Recent Updates

- ✅ Role-based dashboard separation
- ✅ Admin-only room management
- ✅ Student room application workflow
- ✅ Payment tracking system
- ✅ Responsive UI design
- ✅ Image slideshow on landing page
- ✅ Bug fixes and optimizations

## 🤝 Support

If you encounter any issues:
1. Check the troubleshooting section above
2. Verify all installation steps were followed correctly
3. Ensure XAMPP services are running
4. Check browser console for JavaScript errors
5. Review PHP error logs in XAMPP

## 📝 Notes

- Default admin credentials can be created using `create_user.php`
- Students are automatically added to the students table upon registration
- Room assignments require admin approval
- Payment status affects dashboard display
- All forms include proper validation and security measures

---

**Happy Managing! 🏠✨**