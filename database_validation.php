<?php
// Database validation and setup script
require_once "config.php";

function validateAndCreateColumns($conn) {
    $validations = [];
    
    try {
        // Check if payment_status column exists in students table
        $result = $conn->query("SHOW COLUMNS FROM students LIKE 'payment_status'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE students ADD COLUMN payment_status VARCHAR(20) DEFAULT 'Not Paid'");
            $validations[] = "Added payment_status column to students table";
        }
        
        // Check if room_id column exists in students table
        $result = $conn->query("SHOW COLUMNS FROM students LIKE 'room_id'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE students ADD COLUMN room_id INT NULL");
            $validations[] = "Added room_id column to students table";
        }
        
        // Check if current_occupancy column exists in rooms table
        $result = $conn->query("SHOW COLUMNS FROM rooms LIKE 'current_occupancy'");
        if ($result->num_rows == 0) {
            $conn->query("ALTER TABLE rooms ADD COLUMN current_occupancy INT DEFAULT 0");
            $validations[] = "Added current_occupancy column to rooms table";
        }
        
        // Validate foreign key relationship
        $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE 
                               WHERE TABLE_NAME = 'students' AND COLUMN_NAME = 'room_id' 
                               AND REFERENCED_TABLE_NAME = 'rooms'");
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            try {
                $conn->query("ALTER TABLE students ADD FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL");
                $validations[] = "Added foreign key constraint for room_id";
            } catch (Exception $e) {
                $validations[] = "Foreign key constraint already exists or couldn't be added";
            }
        }
        
        return $validations;
        
    } catch (Exception $e) {
        error_log("Database validation error: " . $e->getMessage());
        return ["Error during validation: " . $e->getMessage()];
    }
}

// Run validation
$results = validateAndCreateColumns($conn);
foreach ($results as $result) {
    echo $result . "<br>";
}
echo "<br>Database validation completed successfully!";
?>