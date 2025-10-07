<?php
require_once "config.php";

// SQL to create room_applications table
$sql = "
CREATE TABLE IF NOT EXISTS room_applications (
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
";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'room_applications' created successfully!\n";
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
}

// Create indexes
$index_sql = [
    "CREATE INDEX IF NOT EXISTS idx_application_status ON room_applications(status)",
    "CREATE INDEX IF NOT EXISTS idx_student_email ON room_applications(student_email)"
];

foreach ($index_sql as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Index created successfully!\n";
    } else {
        echo "❌ Error creating index: " . $conn->error . "\n";
    }
}

$conn->close();
echo "\n🎉 Database setup complete! Room applications table is ready.\n";
?>