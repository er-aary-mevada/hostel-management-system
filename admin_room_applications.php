<?php
session_start();
require_once "config.php";

// Check if user is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["email"] !== 'admin1@gmail.com') {
    header("location: login.php");
    exit;
}

// Handle approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_application'])) {
        $app_id = $_POST['application_id'];
        $student_id = $_POST['student_id'];
        $room_id = $_POST['room_id'];
        
        // Check room capacity before approving
        $sql_check = "SELECT capacity, (SELECT COUNT(*) FROM students s WHERE s.room_id = ?) as current_occupancy FROM rooms WHERE id = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("ii", $room_id, $room_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($room_data = $result_check->fetch_assoc()) {
                if ($room_data['current_occupancy'] < $room_data['capacity']) {
                    // Approve application and assign room
                    $conn->begin_transaction();
                    try {
                        // Update application status
                        $sql_approve = "UPDATE room_applications SET status = 'approved', processed_date = CURRENT_TIMESTAMP, processed_by = ? WHERE id = ?";
                        $stmt_approve = $conn->prepare($sql_approve);
                        $stmt_approve->bind_param("si", $_SESSION['email'], $app_id);
                        $stmt_approve->execute();
                        
                        // Assign room to student
                        $sql_assign = "UPDATE students SET room_id = ? WHERE id = ?";
                        $stmt_assign = $conn->prepare($sql_assign);
                        $stmt_assign->bind_param("ii", $room_id, $student_id);
                        $stmt_assign->execute();
                        
                        $conn->commit();
                        $message = "Application approved successfully!";
                        $message_type = "success";
                    } catch (Exception $e) {
                        $conn->rollback();
                        $message = "Error approving application: " . $e->getMessage();
                        $message_type = "error";
                    }
                } else {
                    $message = "Cannot approve - Room is now full!";
                    $message_type = "error";
                }
            }
            $stmt_check->close();
        }
    } else if (isset($_POST['reject_application'])) {
        $app_id = $_POST['application_id'];
        $admin_comment = $_POST['admin_comment'] ?? '';
        
        $sql_reject = "UPDATE room_applications SET status = 'rejected', admin_comment = ?, processed_date = CURRENT_TIMESTAMP, processed_by = ? WHERE id = ?";
        if ($stmt_reject = $conn->prepare($sql_reject)) {
            $stmt_reject->bind_param("ssi", $admin_comment, $_SESSION['email'], $app_id);
            if ($stmt_reject->execute()) {
                $message = "Application rejected successfully!";
                $message_type = "success";
            } else {
                $message = "Error rejecting application!";
                $message_type = "error";
            }
            $stmt_reject->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Applications - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(120deg, #1976d2 0%, #2563eb 100%);
            min-height: 100vh;
            font-family: 'Poppins', Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(21,101,192,0.10);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background: #5a6268;
            text-decoration: none;
            color: white;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .applications-table th,
        .applications-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .applications-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
        .approve-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .approve-btn:hover {
            background: #218838;
        }
        .reject-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        .reject-btn:hover {
            background: #c82333;
        }
        .comment-input {
            width: 200px;
            padding: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏠 Room Applications Management</h1>
            <a href="admin_dashboard.php" class="back-btn">← Back to Admin Dashboard</a>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <h3>Pending Applications</h3>
        <table class="applications-table">
            <thead>
                <tr>
                    <th>Student Email</th>
                    <th>Room Number</th>
                    <th>Application Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get pending applications
                $sql = "SELECT ra.id, ra.student_id, ra.room_id, ra.student_email, ra.application_date, ra.status,
                               r.room_number, r.capacity,
                               (SELECT COUNT(*) FROM students s WHERE s.room_id = r.id) as current_occupancy
                        FROM room_applications ra
                        JOIN rooms r ON ra.room_id = r.id
                        WHERE ra.status = 'pending'
                        ORDER BY ra.application_date ASC";
                
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['student_email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room_number']) . " (Capacity: " . $row['capacity'] . ", Current: " . $row['current_occupancy'] . ")</td>";
                        echo "<td>" . date('Y-m-d H:i', strtotime($row['application_date'])) . "</td>";
                        echo "<td><span class='status-pending'>" . ucfirst($row['status']) . "</span></td>";
                        echo "<td>";
                        
                        // Approve form
                        echo "<form style='display: inline;' method='POST'>";
                        echo "<input type='hidden' name='application_id' value='" . $row['id'] . "'>";
                        echo "<input type='hidden' name='student_id' value='" . $row['student_id'] . "'>";
                        echo "<input type='hidden' name='room_id' value='" . $row['room_id'] . "'>";
                        echo "<button type='submit' name='approve_application' class='approve-btn' onclick='return confirm(\"Approve this application?\")'>Approve</button>";
                        echo "</form>";
                        
                        // Reject form
                        echo "<form style='display: inline;' method='POST'>";
                        echo "<input type='hidden' name='application_id' value='" . $row['id'] . "'>";
                        echo "<input type='text' name='admin_comment' placeholder='Reason (optional)' class='comment-input'>";
                        echo "<button type='submit' name='reject_application' class='reject-btn' onclick='return confirm(\"Reject this application?\")'>Reject</button>";
                        echo "</form>";
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #666;'>No pending applications</td></tr>";
                }
                ?>
            </tbody>
        </table>
        
        <h3 style="margin-top: 40px;">All Applications History</h3>
        <table class="applications-table">
            <thead>
                <tr>
                    <th>Student Email</th>
                    <th>Room Number</th>
                    <th>Application Date</th>
                    <th>Status</th>
                    <th>Processed By</th>
                    <th>Processed Date</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get all applications
                $sql_all = "SELECT ra.*, r.room_number
                           FROM room_applications ra
                           JOIN rooms r ON ra.room_id = r.id
                           ORDER BY ra.application_date DESC
                           LIMIT 20";
                
                $result_all = $conn->query($sql_all);
                if ($result_all && $result_all->num_rows > 0) {
                    while ($row = $result_all->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['student_email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                        echo "<td>" . date('Y-m-d H:i', strtotime($row['application_date'])) . "</td>";
                        $status_class = $row['status'] == 'approved' ? 'status-assigned' : ($row['status'] == 'rejected' ? 'status-rejected' : 'status-pending');
                        echo "<td><span class='$status_class'>" . ucfirst($row['status']) . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['processed_by'] ?? '-') . "</td>";
                        echo "<td>" . ($row['processed_date'] ? date('Y-m-d H:i', strtotime($row['processed_date'])) : '-') . "</td>";
                        echo "<td>" . htmlspecialchars($row['admin_comment'] ?? '-') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: #666;'>No applications found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>