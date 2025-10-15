<?php
session_start();
require_once "config.php";


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Check if user is admin - ADMIN ONLY ACCESS
$is_admin = (isset($_SESSION["username"]) && $_SESSION["username"] === 'admin') || 
            (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com');

// Redirect non-admin users to student dashboard
if (!$is_admin) {
    header("location: student_rooms.php");
    exit;
}

$dashboard_link = 'admin_dashboard.php';
$dashboard_title = 'Admin Dashboard';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($is_admin && isset($_POST['unassign_room'])) {
        $room_id = $_POST['room_id'];
        $student_id = $_POST['student_id'];
        // Unassign student from room
        $sql_unassign = "UPDATE students SET room_id = NULL WHERE id = ?";
        if ($stmt_unassign = $conn->prepare($sql_unassign)) {
            $stmt_unassign->bind_param("i", $student_id);
            $stmt_unassign->execute();
            $stmt_unassign->close();
        }
        // Decrement room occupancy
        $sql_update = "UPDATE rooms SET current_occupancy = current_occupancy - 1 WHERE id = ? AND current_occupancy > 0";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("i", $room_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
    if ($is_admin && isset($_POST['add_room'])) {
        $room_number = isset($_POST['room_number']) ? $_POST['room_number'] : '';
        $room_type = isset($_POST['room_type']) ? $_POST['room_type'] : '';
        $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : '';
        if ($room_number !== '' && $room_type !== '' && $capacity !== '') {
            // Check for duplicate room number
            $sql_check = "SELECT id FROM rooms WHERE room_number = ?";
            if ($stmt_check = $conn->prepare($sql_check)) {
                $stmt_check->bind_param("s", $room_number);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    echo "<script>alert('Room number already exists. Please use a unique room number.');</script>";
                } else {
                    $sql = "INSERT INTO rooms (room_number, room_type, capacity) VALUES (?, ?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("ssi", $room_number, $room_type, $capacity);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                $stmt_check->close();
            }
        } else {
            echo "<script>alert('Please fill in all fields.');</script>";
        }
    } elseif ($is_admin && isset($_POST['delete_room'])) {
        $room_id = $_POST['room_id'];
        // Remove room and unassign students
        $sql_unassign = "UPDATE students SET room_id = NULL WHERE room_id = ?";
        if ($stmt_unassign = $conn->prepare($sql_unassign)) {
            $stmt_unassign->bind_param("i", $room_id);
            $stmt_unassign->execute();
            $stmt_unassign->close();
        }
        $sql_delete = "DELETE FROM rooms WHERE id = ?";
        if ($stmt_delete = $conn->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $room_id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    } elseif (isset($_POST['assign_student'])) {
        } elseif ($is_admin && isset($_POST['assign_student'])) {
        $student_id = $_POST['student_id'];
        $room_id = $_POST['room_id'];

        // Check if the room has capacity
        $sql_check = "SELECT capacity" . 
                     (columnExists($conn, 'rooms', 'current_occupancy') ? ", current_occupancy" : "") . 
                     " FROM rooms WHERE id = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("i", $room_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            $room_data = $result->fetch_assoc();
            $stmt_check->close();

            $capacity = $room_data['capacity'];
            $current_occupancy = safeGetColumn($room_data, 'current_occupancy', 0);

            if ($current_occupancy < $capacity) {
                // Check if room_id column exists in students table
                if (columnExists($conn, 'students', 'room_id')) {
                    $sql_assign = "UPDATE students SET room_id = ? WHERE id = ?";
                    if ($stmt_assign = $conn->prepare($sql_assign)) {
                        $stmt_assign->bind_param("ii", $room_id, $student_id);
                        $stmt_assign->execute();
                        $stmt_assign->close();

                        // Update room occupancy if column exists
                        if (columnExists($conn, 'rooms', 'current_occupancy')) {
                            $sql_update_occupancy = "UPDATE rooms SET current_occupancy = current_occupancy + 1 WHERE id = ?";
                            if ($stmt_update = $conn->prepare($sql_update_occupancy)) {
                                $stmt_update->bind_param("i", $room_id);
                                $stmt_update->execute();
                                $stmt_update->close();
                            }
                        }
                    }
                } else {
                    echo "<script>alert('Room assignment not available - missing database column.');</script>";
                }
            } else {
                echo "<script>alert('Room is full!');</script>";
            }
        }
    } elseif (isset($_POST['apply_room'])) {
        // Handle student room application
        $room_id = $_POST['room_id'];
        $student_email = $_SESSION['email'];
        
        // Get student ID by joining with users table
        $sql_get_student = "SELECT s.id FROM students s 
                            LEFT JOIN users u ON s.user_id = u.id 
                            WHERE u.email = ?";
        if ($stmt_get_student = $conn->prepare($sql_get_student)) {
            $stmt_get_student->bind_param("s", $student_email);
            $stmt_get_student->execute();
            $result_student = $stmt_get_student->get_result();
            
            if ($student_row = $result_student->fetch_assoc()) {
                $student_id = $student_row['id'];
                
                // Check if student is already assigned to any room
                if (columnExists($conn, 'students', 'room_id')) {
                    $sql_check_assigned = "SELECT room_id FROM students WHERE id = ?";
                    if ($stmt_check_assigned = $conn->prepare($sql_check_assigned)) {
                        $stmt_check_assigned->bind_param("i", $student_id);
                        $stmt_check_assigned->execute();
                        $result_assigned = $stmt_check_assigned->get_result();
                        $assigned_data = $result_assigned->fetch_assoc();
                        
                        if ($assigned_data && $assigned_data['room_id']) {
                            echo "<script>alert('You are already assigned to a room!');</script>";
                        } else {
                            // Check room capacity
                            $sql_check_room = "SELECT capacity" . 
                                             (columnExists($conn, 'rooms', 'current_occupancy') ? ", current_occupancy" : "") . 
                                             " FROM rooms WHERE id = ?";
                            if ($stmt_check_room = $conn->prepare($sql_check_room)) {
                                $stmt_check_room->bind_param("i", $room_id);
                                $stmt_check_room->execute();
                                $result_room = $stmt_check_room->get_result();
                                $room_data = $result_room->fetch_assoc();
                                
                                $capacity = $room_data['capacity'];
                                $current_occupancy = safeGetColumn($room_data, 'current_occupancy', 0);
                                
                                if ($current_occupancy < $capacity) {
                                    // Assign room to student
                                    $sql_assign = "UPDATE students SET room_id = ? WHERE id = ?";
                                    if ($stmt_assign = $conn->prepare($sql_assign)) {
                                        $stmt_assign->bind_param("ii", $room_id, $student_id);
                                        $stmt_assign->execute();
                                        
                                        // Update room occupancy
                                        if (columnExists($conn, 'rooms', 'current_occupancy')) {
                                            $sql_update_occupancy = "UPDATE rooms SET current_occupancy = current_occupancy + 1 WHERE id = ?";
                                            if ($stmt_update = $conn->prepare($sql_update_occupancy)) {
                                                $stmt_update->bind_param("i", $room_id);
                                                $stmt_update->execute();
                                                $stmt_update->close();
                                            }
                                        }
                                        
                                        $stmt_assign->close();
                                        echo "<script>alert('Room assigned successfully!'); window.location.reload();</script>";
                                    }
                                } else {
                                    echo "<script>alert('Room is full!');</script>";
                                }
                                $stmt_check_room->close();
                            }
                        }
                        $stmt_check_assigned->close();
                    }
                }
            }
            $stmt_get_student->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS - Rooms Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f4f6fb;
            color: #222;
        }
        .main-content h1, .main-content h2, .main-content h3 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .rooms-table, .assigned-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .rooms-table th, .rooms-table td, .assigned-table th, .assigned-table td {
            border: 1px solid #e0e0e0;
            padding: 12px 16px;
            text-align: left;
            font-size: 15px;
        }
        .rooms-table th, .assigned-table th {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }
        .rooms-table tr:nth-child(even), .assigned-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .status-full {
            color: #d32f2f;
            font-weight: bold;
        }
        .status-available {
            color: #388e3c;
            font-weight: bold;
        }
        .add-room-section, .assigned-rooms-list {
            margin-bottom: 32px;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
        }
        .form-group input {
            padding: 10px 14px;
            border: 1.5px solid #bdbdbd;
            border-radius: 5px;
            font-size: 15px;
            width: 100%;
        }
        .form-actions button, .add-btn, .save-btn, .cancel-btn, button[name="apply_room"], button[name="unassign_room"] {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 15px;
            cursor: pointer;
            margin-right: 8px;
            transition: background 0.2s;
        }
        .form-actions button:hover, .add-btn:hover, .save-btn:hover, .cancel-btn:hover, button[name="apply_room"]:hover, button[name="unassign_room"]:hover {
            background: #1565c0;
        }
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #bdbdbd;
            border-radius: 5px;
            font-size: 15px;
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .stat-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.08);
            padding: 18px 24px;
            margin-right: 18px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-box i {
            font-size: 2rem;
            color: #1976d2;
        }
        .stat-info h3 {
            margin: 0 0 6px 0;
            font-size: 1.1rem;
            color: #1976d2;
        }
        .stat-info p {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
            font-weight: 500;
        }
        .stats-overview {
            display: flex;
            gap: 18px;
            margin-bottom: 32px;
        }
        @media (max-width: 900px) {
            .stats-overview { flex-direction: column; gap: 12px; }
            .stat-box { margin-right: 0; }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            animation: slideDown 0.3s;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #e3f2fd;
            padding-bottom: 15px;
        }
        .modal-header h2 {
            color: #1976d2;
            margin: 0;
            font-size: 1.5rem;
        }
        .close {
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            transition: color 0.2s;
        }
        .close:hover {
            color: #d32f2f;
        }
        .modal-body {
            padding: 10px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-label {
            font-weight: 600;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .students-list {
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .students-list h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
        }
        .student-item {
            padding: 8px;
            margin: 5px 0;
            background: #fff;
            border-radius: 5px;
            border-left: 3px solid #1976d2;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        

        <!-- Main Content -->
        <div class="main-content">
            <h1>Rooms Management</h1>
            
            <div class="back-button">
                <a href="<?php echo $dashboard_link; ?>" class="btn" style="margin-top:10px;margin-bottom:20px;display:inline-block;">&larr; Back to <?php echo $dashboard_title; ?></a>
            </div>
            
            <!-- Add Room Form -->
            <div class="add-room-section">
                <button onclick="toggleAddForm()" class="add-btn"><i class="fas fa-plus-circle"></i> Add New Room</button>
                <div id="addFormContainer" class="add-form" style="display: none;">
                    <form action="rooms.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="text" name="room_number" placeholder="Enter room number" required>
                            </div>
                            <div class="form-group">
                                <label>Room Type</label>
                                <select name="room_type" required style="padding: 10px 14px; border: 1.5px solid #bdbdbd; border-radius: 5px; font-size: 15px; width: 100%;">
                                    <option value="">Select Room Type</option>
                                    <option value="Single">Single</option>
                                    <option value="Double">Double</option>
                                    <option value="Triple">Triple</option>
                                    <option value="Quad">Quad</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Room Capacity</label>
                                <input type="number" name="capacity" placeholder="Enter room capacity" min="1" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="add_room" class="save-btn">✓ Save Room</button>
                            <button type="button" onclick="toggleAddForm()" class="cancel-btn">✕ Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-box">
                    <i class="fas fa-door-open"></i>
                    <div class="stat-info">
                        <h3>Total Rooms</h3>
                        <p><?php
                            $sql = "SELECT COUNT(*) as total FROM rooms";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'];
                        ?></p>
                    </div>
                </div>

                <div class="stat-box">
                    <i class="fas fa-bed"></i>
                    <div class="stat-info">
                        <h3>Total Capacity</h3>
                        <p><?php
                            $sql = "SELECT SUM(capacity) as total FROM rooms";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ?? 0;
                        ?></p>
                    </div>
                </div>

                <div class="stat-box">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-info">
                        <h3>Current Occupancy</h3>
                        <p><?php
                            $sql = "SELECT SUM(current_occupancy) as total FROM rooms";
                            $result = $conn->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ?? 0;
                        ?></p>
                    </div>
                </div>
            </div>

            <!-- Rooms Table -->
            <div class="rooms-section">
                <div class="section-header">
                    <h2>All Rooms</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search rooms..." onkeyup="searchRooms()">
                    </div>
                </div>

                <table class="rooms-table">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Capacity</th>
                            <th>Current Occupancy</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $sql = "SELECT r.*, 
                           (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as actual_occupancy 
                           FROM rooms r ORDER BY r.room_number";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            // Use actual count from students table for accuracy
                            $current_occupancy = $row['actual_occupancy'];
                            $capacity = $row['capacity'];
                            $room_type = isset($row['room_type']) ? $row['room_type'] : 'N/A';
                            $status = $current_occupancy >= $capacity ? 'Full' : 'Available';
                            $statusClass = $status === 'Full' ? 'status-full' : 'status-available';
                            
                            // Update current_occupancy in rooms table if it exists and is incorrect
                            if (columnExists($conn, 'rooms', 'current_occupancy') && 
                                safeGetColumn($row, 'current_occupancy', 0) != $current_occupancy) {
                                $update_sql = "UPDATE rooms SET current_occupancy = ? WHERE id = ?";
                                if ($update_stmt = $conn->prepare($update_sql)) {
                                    $update_stmt->bind_param("ii", $current_occupancy, $row['id']);
                                    $update_stmt->execute();
                                    $update_stmt->close();
                                }
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($room_type) . "</td>";
                            echo "<td>" . $capacity . "</td>";
                            echo "<td>" . $current_occupancy . "</td>";
                            echo "<td class='" . $statusClass . "'>" . $status . "</td>";
                            echo "<td>";
                            // Admin actions
                            echo '<button onclick="viewRoomDetails(' . $row['id'] . ')" style="background:#1976d2; color:#fff; border:none; padding:8px 16px; border-radius:5px; cursor:pointer; margin-right:5px;">
                            <i class="fas fa-eye"></i> View</button>';
                            echo '<form method="post" action="rooms.php" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this room?\');">
                            <input type="hidden" name="room_id" value="' . $row['id'] . '">
                            <button type="submit" name="delete_room" style="background:#d32f2f; color:#fff; border:none; padding:8px 16px; border-radius:5px; cursor:pointer;">
                            <i class="fas fa-trash"></i> Delete</button>
                            </form>';
                            echo "</td>";
                            echo "</tr>";
                        }
                        $result->free();
                    }
                    ?>
            </tbody>
        </table>
    </div>

    <!-- Assigned Rooms Section -->
    <div class="assigned-rooms-section" style="margin-top: 40px;">
        <h2><i class="fas fa-user-check"></i> Assigned Students</h2>
        <table class="assigned-table rooms-table">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Room Type</th>
                    <th>Student Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT r.id as room_id, r.room_number, r.room_type, s.id as student_id, s.name 
                    FROM students s 
                    JOIN rooms r ON s.room_id = r.id 
                    ORDER BY r.room_number";
            if ($result = $conn->query($sql)) {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $room_type = isset($row['room_type']) ? $row['room_type'] : 'N/A';
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($room_type) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo '<td><form method="post" action="rooms.php" style="display:inline;">';
                        echo '<input type="hidden" name="room_id" value="' . $row['room_id'] . '">';
                        echo '<input type="hidden" name="student_id" value="' . $row['student_id'] . '">';
                        echo '<button type="submit" name="unassign_room" style="background:#d32f2f; color:#fff; border:none; padding:8px 16px; border-radius:5px; cursor:pointer;" onclick="return confirm(\'Are you sure you want to unassign this student?\');"><i class="fas fa-user-times"></i> Remove</button>';
                        echo '</form></td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#999;'>No students assigned to rooms yet.</td></tr>";
                }
                $result->free();
            }
            ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Room Details Modal -->
<div id="roomModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-door-open"></i> Room Details</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

</body>
<script>
function toggleAddForm() {
    var form = document.getElementById('addFormContainer');
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

function viewRoomDetails(roomId) {
    // Show modal
    document.getElementById('roomModal').style.display = 'block';
    
    // Fetch room details via AJAX
    fetch('get_room_details.php?room_id=' + roomId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-hashtag"></i> Room Number:</span>
                        <span class="detail-value">${data.room.room_number}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-home"></i> Room Type:</span>
                        <span class="detail-value">${data.room.room_type}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-users"></i> Capacity:</span>
                        <span class="detail-value">${data.room.capacity} persons</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-user-check"></i> Current Occupancy:</span>
                        <span class="detail-value">${data.room.current_occupancy} / ${data.room.capacity}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="fas fa-info-circle"></i> Status:</span>
                        <span class="detail-value" style="color: ${data.room.status === 'Available' ? '#4caf50' : '#f44336'}">
                            ${data.room.status}
                        </span>
                    </div>
                `;
                
                if (data.students && data.students.length > 0) {
                    html += `
                        <div class="students-list">
                            <h4><i class="fas fa-user-graduate"></i> Assigned Students:</h4>`;
                    data.students.forEach(student => {
                        html += `<div class="student-item">📌 ${student.name}</div>`;
                    });
                    html += `</div>`;
                } else {
                    html += `
                        <div class="students-list">
                            <h4><i class="fas fa-user-graduate"></i> Assigned Students:</h4>
                            <p style="color: #999; margin: 10px 0;">No students assigned yet</p>
                        </div>`;
                }
                
                document.getElementById('modalBody').innerHTML = html;
            } else {
                document.getElementById('modalBody').innerHTML = '<p style="color: red;">Error loading room details</p>';
            }
        })
        .catch(error => {
            document.getElementById('modalBody').innerHTML = '<p style="color: red;">Failed to load room details</p>';
            console.error('Error:', error);
        });
}

function closeModal() {
    document.getElementById('roomModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('roomModal');
    if (event.target == modal) {
        closeModal();
    }
}

function searchRooms() {
    var input = document.querySelector('.search-box input');
    var filter = input.value.toUpperCase();
    var table = document.querySelector('.rooms-table');
    var tr = table.getElementsByTagName('tr');
    
    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td')[0]; // Room Number column
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

// Simple and Clean Navigation Fix
document.addEventListener('DOMContentLoaded', function() {
    // Get all navigation items
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(function(item) {
        // Prevent text selection and unwanted effects
        item.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });
        
        // Remove focus after click to prevent stuck states
        item.addEventListener('click', function(e) {
            setTimeout(() => {
                this.blur();
            }, 50);
        });
        
        // Prevent right-click context menu
        item.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
});
</script>
</html>

                                <?php if ($is_admin): ?>
                                <div class="assigned-rooms-list">
                                    <h3>Assigned Rooms</h3>
                                    <table class="assigned-table">
                                        <thead>
                                            <tr><th>Room Number</th><th>Student Name</th><th>Action</th></tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $sql = "SELECT r.id as room_id, r.room_number, s.id as student_id, s.name FROM students s JOIN rooms r ON s.room_id = r.id ORDER BY r.room_number";
                                        if ($result = $conn->query($sql)) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                                echo '<td><form method="post" action="rooms.php" style="display:inline;">';
                                                echo '<input type="hidden" name="room_id" value="' . $row['room_id'] . '">';
                                                echo '<input type="hidden" name="student_id" value="' . $row['student_id'] . '">';
                                                echo '<button type="submit" name="unassign_room" onclick="return confirm(\'Are you sure you want to unassign this student from the room?\');">Remove</button>';
                                                echo '</form></td>';
                                                echo "</tr>";
                                            }
                                            $result->free();
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
