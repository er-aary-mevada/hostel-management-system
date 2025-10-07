-- Room Applications Table
-- This table stores student applications for rooms that require admin approval

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

-- Add index for better performance
CREATE INDEX idx_application_status ON room_applications(status);
CREATE INDEX idx_student_email ON room_applications(student_email);