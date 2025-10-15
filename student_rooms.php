<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Only allow non-admins (students)
if ((isset($_SESSION["username"]) && $_SESSION["username"] === 'admin') || 
    (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com')) {
    header("location: rooms.php");
    exit;
}
?>
<div class="main-content">
    <style>
        .main-content {
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
        }
        .main-content h1 {
            color: #1976d2;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .main-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .main-content table th,
        .main-content table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .main-content table th {
            background-color: #f5f5f5;
            font-weight: 600;
            color: #333;
        }
        .main-content table tr:hover {
            background-color: #f9f9f9;
        }
        .apply-btn {
            background: #1976d2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .apply-btn:hover {
            background: #1565c0;
        }
        .status-full {
            color: red;
            font-weight: bold;
        }
        .status-assigned {
            color: green;
            font-weight: bold;
        }
        .status-unavailable {
            color: gray;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
    <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    <h1>Available Rooms</h1>
    <table>
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Capacity</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if current student is already assigned to any room first
            $student_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
            $sql_student = "SELECT s.id, s.room_id FROM students s 
                            LEFT JOIN users u ON s.user_id = u.id 
                            WHERE u.email = ?";
            $student_room_id = null;
            $student_id = null;
            if ($stmt_student = $conn->prepare($sql_student)) {
                $stmt_student->bind_param("s", $student_email);
                $stmt_student->execute();
                $result_student = $stmt_student->get_result();
                if ($row_student = $result_student->fetch_assoc()) {
                    $student_room_id = $row_student['room_id'];
                    $student_id = $row_student['id'];
                }
                $stmt_student->close();
            }
            
            // Check if student has any pending application
            $pending_application = null;
            if ($student_id) {
                // Check if room_applications table exists
                $table_check = $conn->query("SHOW TABLES LIKE 'room_applications'");
                if ($table_check && $table_check->num_rows > 0) {
                    $sql_app = "SELECT room_id, status FROM room_applications WHERE student_id = ?";
                    if ($stmt_app = $conn->prepare($sql_app)) {
                        $stmt_app->bind_param("i", $student_id);
                        $stmt_app->execute();
                        $result_app = $stmt_app->get_result();
                        if ($row_app = $result_app->fetch_assoc()) {
                            $pending_application = $row_app;
                        }
                        $stmt_app->close();
                    }
                } else {
                    // Table doesn't exist - create it or show message
                    echo "<div style='background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                          ⚠️ Room applications feature is being set up. Please contact admin or run setup.
                          </div>";
                }
            }
            
            // Get rooms with actual occupancy calculation
            $sql = "SELECT r.id, r.room_number, r.capacity,
                           (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as current_occupancy
                    FROM rooms r ORDER BY r.room_number";
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $current_occupancy = $row['current_occupancy'];
                    $capacity = $row['capacity'];
                    
                    // Determine status based on room capacity only
                    if ($current_occupancy >= $capacity) {
                        $status = 'Full';
                        $status_class = 'status-full';
                    } else {
                        $status = 'Available';
                        $status_class = '';
                    }
                    
                    // Determine action based on student assignment and application status
                    if ($student_room_id == $row['id']) {
                        $action = '<span class="status-assigned">Assigned</span>';
                    } else if ($student_room_id) {
                        $action = '<span class="status-unavailable">Already Assigned</span>';
                    } else if ($pending_application && $pending_application['room_id'] == $row['id']) {
                        // Student has application for this room
                        if ($pending_application['status'] == 'pending') {
                            $action = '<span class="status-pending">Applied - Pending</span>';
                        } else if ($pending_application['status'] == 'rejected') {
                            $action = '<span class="status-rejected">Application Rejected</span>';
                        }
                    } else if ($pending_application && $pending_application['status'] == 'pending') {
                        $action = '<span class="status-unavailable">Application Pending</span>';
                    } else if ($current_occupancy >= $capacity) {
                        $action = '<span class="status-full">Full</span>';
                    } else {
                        $action = '<button onclick="applyForRoom(' . htmlspecialchars($row['id']) . ')" class="apply-btn">Apply</button>';
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['capacity']) . "</td>";
                    echo "<td><span class='$status_class'>" . htmlspecialchars($status) . "</span></td>";
                    echo "<td>$action</td>";
                    echo "</tr>";
                }
                $result->free();
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function applyForRoom(roomId) {
    if (confirm('Are you sure you want to apply for this room?')) {
        fetch('apply_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'room_id=' + roomId + '&apply_room=1'
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
