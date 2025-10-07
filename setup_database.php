<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Room Applications</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🏠 Room Applications Database Setup</h2>
        <p class="info">This will create the room_applications table for the approval system.</p>
        
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
            echo "<p class='success'>✅ Table 'room_applications' created successfully!</p>";
        } else {
            echo "<p class='error'>❌ Error creating table: " . $conn->error . "</p>";
        }

        // Create indexes
        $index_sql = [
            "CREATE INDEX IF NOT EXISTS idx_application_status ON room_applications(status)",
            "CREATE INDEX IF NOT EXISTS idx_student_email ON room_applications(student_email)"
        ];

        foreach ($index_sql as $sql) {
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success'>✅ Index created successfully!</p>";
            } else {
                echo "<p class='error'>❌ Error creating index: " . $conn->error . "</p>";
            }
        }

        $conn->close();
        echo "<p class='success'>🎉 Database setup complete! Room applications table is ready.</p>";
        echo "<p class='info'>You can now close this page and continue with the application.</p>";
        ?>
    </div>
</body>
</html>