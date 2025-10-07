<?php
// Quick database table creation script
require_once "config.php";

echo "<h2>Creating room_applications table...</h2>";

// First check if table exists
$check_sql = "SHOW TABLES LIKE 'room_applications'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Table 'room_applications' already exists!</p>";
} else {
    // Create table
    $sql = "CREATE TABLE room_applications (
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
    )";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✅ Table 'room_applications' created successfully!</p>";
        
        // Create indexes
        $index1 = "CREATE INDEX idx_application_status ON room_applications(status)";
        $index2 = "CREATE INDEX idx_student_email ON room_applications(student_email)";
        
        $conn->query($index1);
        $conn->query($index2);
        echo "<p style='color: green;'>✅ Indexes created successfully!</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Error creating table: " . $conn->error . "</p>";
    }
}

// Show current tables
echo "<h3>Current tables in database:</h3>";
$show_tables = $conn->query("SHOW TABLES");
echo "<ul>";
while ($table = $show_tables->fetch_array()) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

$conn->close();
echo "<p><a href='student_rooms.php'>Test student rooms page</a></p>";
echo "<p><a href='admin_room_applications.php'>Test admin applications page</a></p>";
?>