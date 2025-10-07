# 🏠 Hostel Management System

A comprehensive web-based hostel management system built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides role-based access for administrators and students to manage hostel operations efficiently with an advanced room application approval workflow.

## 🚀 Latest Features (Updated)

### 📋 **Room Application Approval System**
- **Students**: Apply for rooms (applications go to pending status)
- **Admin**: Approve or reject room applications with comments
- **Automatic Assignment**: Room is assigned only after admin approval
- **Application Tracking**: Real-time status updates (Pending/Approved/Rejected)

### 🎯 **Enhanced User Experience**
- **Clean Navigation**: Optimized sidebar navigation
- **Direct Room Access**: Dedicated room viewing page for students
- **Status Indicators**: Visual status indicators for applications
- **Error Handling**: Comprehensive error handling and validation

## ✨ Core Features

### 🔐 Authentication System
- User registration and login
- Role-based access control (Admin/Student)
- Secure session management with bcrypt password hashing
- Automatic logout functionality

### 👨‍💼 Admin Dashboard
- **Room Applications Management**: View, approve, or reject student applications
- **Room Management**: Add, edit, and manage room inventory
- **Student Management**: View all registered students and assignments
- **Application History**: Track all application decisions with timestamps
- **Capacity Monitoring**: Real-time room occupancy tracking

### 👨‍🎓 Student Dashboard  
- **Room Application**: Apply for available rooms with instant feedback
- **Application Status**: Track application status (Pending/Approved/Rejected)
- **Room Viewing**: Browse available rooms with real-time capacity
- **Profile Management**: View and manage personal information
- **Assignment Status**: View current room assignment details

### 🏢 Advanced Room Management
- **Smart Capacity Tracking**: Real-time occupancy calculation
- **Application Queue**: Manage multiple applications per room
- **Approval Workflow**: Admin-controlled room assignment process
- **Status Management**: Visual indicators for room availability

## 🛠 Installation & Setup

### Prerequisites
- XAMPP (PHP 7.4+, MySQL 5.7+, Apache)
- Web browser (Chrome, Firefox, Safari)
- Text editor (optional, for customization)

### Step 1: Download and Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project Files
1. **Clone or Download** this project
2. **Copy** the project folder to `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux/Mac)
3. **Rename** the folder to `hostel-management-system` if needed

### Step 3: Database Setup
1. **Open phpMyAdmin**: `http://localhost/phpmyadmin`
2. **Create Database**: Create a new database named `hostel_db`
3. **Import Database**: Import the provided SQL file or create tables manually

#### Manual Database Creation:
```sql
-- Create database
CREATE DATABASE hostel_db;
USE hostel_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table  
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    room_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) UNIQUE NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    room_type VARCHAR(50) DEFAULT 'Standard',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Room Applications table (NEW!)
CREATE TABLE room_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_comment TEXT,
    processed_date TIMESTAMP NULL,
    processed_by VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_application (student_id)
);

-- Insert sample admin user
INSERT INTO users (email, password, username) VALUES 
('admin1@gmail.com', '$2y$10$example_bcrypt_hash', 'admin');

-- Insert sample rooms
INSERT INTO rooms (room_number, capacity, room_type) VALUES 
('101', 1, 'Single'),
('102', 2, 'Double'),
('103', 2, 'Double'),
('201', 1, 'Single'),
('202', 2, 'Double');
```

### Step 4: Configuration
1. **Edit config.php** if needed:
```php
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Add password if set
define('DB_NAME', 'hostel_db');
```

### Step 5: Access the System
1. **Student Portal**: `http://localhost/hostel-management-system/`
2. **Admin Login**: Use admin credentials in login page
3. **Student Registration**: Create new student account via signup

## � How to Use

### For Students:
1. **Register**: Create account via signup page
2. **Login**: Access student dashboard
3. **Apply for Room**: 
   - Click "View Rooms" in sidebar
   - Browse available rooms
   - Click "Apply" for desired room
   - Wait for admin approval
4. **Track Status**: Monitor application status in real-time

### For Administrators:
1. **Login**: Use admin credentials
2. **Access Applications**: 
   - Go to dashboard
   - Click "Room Applications"
3. **Review Applications**:
   - View pending applications
   - Check student details and room capacity
   - Approve or reject with optional comments
4. **Monitor System**: Track all application history

## 🔧 Advanced Configuration

### Setting up on Different Devices:

#### **Windows:**
- Install XAMPP
- Place project in `C:\xampp\htdocs\`
- Access via `http://localhost/hostel-management-system/`

#### **Mac:**
- Install XAMPP for Mac
- Place project in `/Applications/XAMPP/htdocs/`
- Access via `http://localhost/hostel-management-system/`

#### **Linux:**
```bash
# Install LAMP stack
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql

# Place project files
sudo cp -r hostel-management-system /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/hostel-management-system
sudo chmod -R 755 /var/www/html/hostel-management-system
```

### Network Access Setup:
1. **Find your IP**: `ipconfig` (Windows) or `ifconfig` (Linux/Mac)
2. **Configure Apache**: Edit `httpd.conf` to allow network access
3. **Access from other devices**: `http://YOUR_IP/hostel-management-system/`

## 🚨 Troubleshooting

### Common Issues:

#### Database Connection Failed:
- Verify MySQL is running in XAMPP
- Check database credentials in `config.php`
- Ensure `hostel_db` database exists

#### Room Applications Not Working:
- Verify `room_applications` table exists
- Check if application shows pending status
- Ensure admin approval workflow is followed

#### Access Denied Errors:
- Check user roles and permissions
- Verify login credentials
- Clear browser cache and cookies

#### Styling Issues:
- Ensure `style.css` is accessible
- Check file permissions
- Verify web server is serving CSS files

## 🆕 What's New in This Version

### ✅ **Major Updates:**
- **🔄 Complete Workflow Redesign**: Room applications now require admin approval
- **📊 Application Management**: Comprehensive admin interface for managing applications
- **🎯 Enhanced UX**: Better navigation and user feedback
- **🧹 Code Optimization**: Removed unused code and optimized performance
- **🔒 Improved Security**: Better error handling and validation

### ✅ **Bug Fixes:**
- Fixed direct room assignment issue
- Resolved navigation consistency problems
- Improved error messaging
- Enhanced mobile responsiveness

### ✅ **New Features:**
- Application status tracking
- Admin comment system
- Real-time capacity monitoring
- Application history management

## 📱 Browser Compatibility
- ✅ Chrome 80+
- ✅ Firefox 75+  
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Mobile browsers

## 🤝 Support & Development

### Getting Help:
1. Check this README thoroughly
2. Verify installation steps
3. Check browser developer console for errors
4. Review PHP error logs

### Contributing:
1. Fork the repository
2. Create feature branch
3. Make your changes
4. Test thoroughly
5. Submit pull request

## 📋 System Requirements

### Minimum:
- PHP 7.4+
- MySQL 5.7+
- Apache 2.4+
- 512MB RAM
- 100MB storage

### Recommended:
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4+
- 1GB RAM
- 500MB storage

## 🔐 Security Features

- **Password Hashing**: bcrypt for admin, md5 for students (legacy)
- **SQL Injection Protection**: Prepared statements throughout
- **Session Security**: Proper session management
- **Role-based Access**: Strict permission controls
- **Input Validation**: Comprehensive form validation

---

## 🎉 Quick Start Commands

```bash
# Start XAMPP services
# Windows: Open XAMPP Control Panel
# Linux: sudo /opt/lampp/lampp start

# Access application
# http://localhost/hostel-management-system/

# Admin access
# Email: admin1@gmail.com
# Password: [set during installation]

# Create student account
# Use signup page or admin interface
```

**🏠 Happy Hostel Managing! ✨**

---
*Last Updated: October 2025 | Version: 2.0 | Room Application Approval System*

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