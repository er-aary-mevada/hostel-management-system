<?php
session_start();
require_once "config.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

// Only allow non-admins
if (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Handle room application
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_room'])) {
    $room_id = $_POST['room_id'];
    $student_email = $_SESSION['email'];
    
    // Get student info
    $sql_student = "SELECT id, room_id FROM students WHERE email = ?";
    if ($stmt_student = $conn->prepare($sql_student)) {
        $stmt_student->bind_param("s", $student_email);
        $stmt_student->execute();
        $result_student = $stmt_student->get_result();
        
        if ($student_row = $result_student->fetch_assoc()) {
            $student_id = $student_row['id'];
            $current_room_id = $student_row['room_id'];
            
            if ($current_room_id) {
                echo json_encode(['success' => false, 'message' => 'You are already assigned to a room!']);
            } else {
                // Check if student already has a pending application
                $sql_check_app = "SELECT status FROM room_applications WHERE student_id = ?";
                if ($stmt_check_app = $conn->prepare($sql_check_app)) {
                    $stmt_check_app->bind_param("i", $student_id);
                    $stmt_check_app->execute();
                    $result_check_app = $stmt_check_app->get_result();
                    
                    if ($app_row = $result_check_app->fetch_assoc()) {
                        $app_status = $app_row['status'];
                        if ($app_status === 'pending') {
                            echo json_encode(['success' => false, 'message' => 'You already have a pending room application!']);
                        } else if ($app_status === 'rejected') {
                            // Update existing rejected application to pending
                            $sql_update_app = "UPDATE room_applications SET room_id = ?, status = 'pending', application_date = CURRENT_TIMESTAMP WHERE student_id = ?";
                            if ($stmt_update_app = $conn->prepare($sql_update_app)) {
                                $stmt_update_app->bind_param("ii", $room_id, $student_id);
                                if ($stmt_update_app->execute()) {
                                    echo json_encode(['success' => true, 'message' => 'Room application submitted successfully! Waiting for admin approval.']);
                                } else {
                                    echo json_encode(['success' => false, 'message' => 'Error submitting application. Please try again.']);
                                }
                                $stmt_update_app->close();
                            }
                        }
                    } else {
                        // Create new application
                        $sql_create_app = "INSERT INTO room_applications (student_id, room_id, student_email, status) VALUES (?, ?, ?, 'pending')";
                        if ($stmt_create_app = $conn->prepare($sql_create_app)) {
                            $stmt_create_app->bind_param("iis", $student_id, $room_id, $student_email);
                            if ($stmt_create_app->execute()) {
                                echo json_encode(['success' => true, 'message' => 'Room application submitted successfully! Waiting for admin approval.']);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Error submitting application. Please try again.']);
                            }
                            $stmt_create_app->close();
                        }
                    }
                    $stmt_check_app->close();
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Student record not found. Please contact admin.']);
        }
        $stmt_student->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>