<?php
session_start();
require_once "config.php";

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$is_admin = (isset($_SESSION["username"]) && $_SESSION["username"] === 'admin') || 
            (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com');

if (!$is_admin) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

// Get room ID
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

if ($room_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
    exit;
}

// Fetch room details
$sql = "SELECT r.*, 
        (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as current_occupancy 
        FROM rooms r 
        WHERE r.id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($room = $result->fetch_assoc()) {
        $current_occupancy = $room['current_occupancy'];
        $capacity = $room['capacity'];
        $status = $current_occupancy >= $capacity ? 'Full' : 'Available';
        
        $room_data = [
            'id' => $room['id'],
            'room_number' => $room['room_number'],
            'room_type' => isset($room['room_type']) ? $room['room_type'] : 'N/A',
            'capacity' => $capacity,
            'current_occupancy' => $current_occupancy,
            'status' => $status
        ];
        
        // Fetch assigned students
        $students = [];
        $sql_students = "SELECT s.id, s.name FROM students s WHERE s.room_id = ?";
        if ($stmt_students = $conn->prepare($sql_students)) {
            $stmt_students->bind_param("i", $room_id);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();
            
            while ($student = $result_students->fetch_assoc()) {
                $students[] = [
                    'id' => $student['id'],
                    'name' => $student['name']
                ];
            }
            $stmt_students->close();
        }
        
        echo json_encode([
            'success' => true,
            'room' => $room_data,
            'students' => $students
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Room not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$conn->close();
?>
