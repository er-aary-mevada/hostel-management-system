-- Hostel Management System Database Setup
-- Run this script in phpMyAdmin after creating the database 'hostel_management'

-- Create Users Table
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

-- Create Students Table
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

-- Create Rooms Table
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

-- Create Room Requests Table
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

-- Insert Sample Admin User (Username: admin, Password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `role`) 
VALUES ('admin', 'admin@hostel.com', MD5('admin123'), 'admin');

-- Insert Sample Rooms
INSERT INTO `rooms` (`room_number`, `room_type`, `capacity`, `current_occupancy`) VALUES
('101', 'Single', 1, 0),
('102', 'Double', 2, 0),
('103', 'Triple', 3, 0),
('201', 'Single', 1, 0),
('202', 'Double', 2, 0),
('203', 'Triple', 3, 0);

-- Insert Sample Student Users
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('student1', 'student1@example.com', MD5('password123'), 'student'),
('student2', 'student2@example.com', MD5('password123'), 'student');

-- Insert Sample Students
INSERT INTO `students` (`user_id`, `name`, `phone`, `address`, `payment_status`) VALUES
(2, 'John Doe', '1234567890', '123 Main St, City', 'pending'),
(3, 'Jane Smith', '0987654321', '456 Oak Ave, City', 'pending');