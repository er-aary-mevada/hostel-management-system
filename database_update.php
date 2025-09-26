<?php
// Database update script - run this once to add missing columns
require_once "config.php";

echo "<h2>Database Update Script</h2>";

// Add address column to students table if it doesn't exist
$sql = "SHOW COLUMNS FROM students LIKE 'address'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Address column doesn't exist, add it
    $sql = "ALTER TABLE students ADD COLUMN address TEXT AFTER phone";
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Added 'address' column to students table successfully.</p>";
    } else {
        echo "<p>❌ Error adding 'address' column: " . $conn->error . "</p>";
    }
} else {
    echo "<p>✅ 'address' column already exists in students table.</p>";
}

// Check if role column exists in users table
$sql = "SHOW COLUMNS FROM users LIKE 'role'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p>✅ 'role' column exists in users table.</p>";
    
    // Update existing users to have proper roles
    $sql = "UPDATE users SET role = 'admin' WHERE username = 'admin'";
    $conn->query($sql);
    
    $sql = "UPDATE users SET role = 'student' WHERE username != 'admin' AND role IS NULL";
    $conn->query($sql);
    
    echo "<p>✅ Updated user roles.</p>";
} else {
    echo "<p>❌ 'role' column doesn't exist in users table. Please run the database setup script.</p>";
}

// Create system_settings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ System settings table created/verified.</p>";
} else {
    echo "<p>❌ Error creating system settings table: " . $conn->error . "</p>";
}

// Create user_preferences table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notifications TINYINT(1) DEFAULT 1,
    email_alerts TINYINT(1) DEFAULT 1,
    theme VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "<p>✅ User preferences table created/verified.</p>";
} else {
    echo "<p>❌ Error creating user preferences table: " . $conn->error . "</p>";
}

echo "<h3>Database Update Complete!</h3>";
echo "<p><a href='index.html'>← Back to Home</a></p>";

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Update - HMS</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h2, h3 { color: #333; }
        p { margin: 10px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>