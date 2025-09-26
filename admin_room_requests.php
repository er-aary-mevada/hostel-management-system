<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["email"] !== 'admin1@gmail.com') {
    header("location: index.html");
    exit;
}

// Approve request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_request'])) {
    $request_id = $_POST['request_id'];
    // Get student and room
    $sql = "SELECT student_id, room_id FROM room_requests WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($student_id, $room_id);
        $stmt->fetch();
        $stmt->close();
        // Assign room to student
        $sql_assign = "UPDATE students SET room_id = ? WHERE id = ?";
        if ($stmt_assign = $conn->prepare($sql_assign)) {
            $stmt_assign->bind_param("ii", $room_id, $student_id);
            $stmt_assign->execute();
            $stmt_assign->close();
        }
        // Update room occupancy
        $sql_update = "UPDATE rooms SET current_occupancy = current_occupancy + 1 WHERE id = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("i", $room_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
        // Mark request as approved
        $sql_approve = "UPDATE room_requests SET status = 'approved', approved_date = NOW() WHERE id = ?";
        if ($stmt_approve = $conn->prepare($sql_approve)) {
            $stmt_approve->bind_param("i", $request_id);
            $stmt_approve->execute();
            $stmt_approve->close();
        }
    }
}

// Reject request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reject_request'])) {
    $request_id = $_POST['request_id'];
    $sql_reject = "UPDATE room_requests SET status = 'rejected' WHERE id = ?";
    if ($stmt_reject = $conn->prepare($sql_reject)) {
        $stmt_reject->bind_param("i", $request_id);
        $stmt_reject->execute();
        $stmt_reject->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Requests - Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
                <a href="students.php" class="nav-item">Manage Students</a>
                <a href="rooms.php" class="nav-item">Manage Rooms</a>
                <a href="admin_room_requests.php" class="nav-item active">Room Requests</a>
                <a href="payments.php" class="nav-item">Payments</a>
                <a href="settings.php" class="nav-item">Settings</a>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </div>
        <div class="main-content">
            <h1>Pending Room Requests</h1>
            <table class="requests-table">
                <thead>
                    <tr><th>Student Name</th><th>Room Number</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT rr.id, s.name, r.room_number, rr.request_date FROM room_requests rr JOIN students s ON rr.student_id = s.id JOIN rooms r ON rr.room_id = r.id WHERE rr.status = 'pending' ORDER BY rr.request_date DESC";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                            echo "<td>" . $row['request_date'] . "</td>";
                            echo "<td>";
                            echo '<form method="post" style="display:inline;"><input type="hidden" name="request_id" value="' . $row['id'] . '"><button type="submit" name="approve_request">Approve</button></form> ';
                            echo '<form method="post" style="display:inline;"><input type="hidden" name="request_id" value="' . $row['id'] . '"><button type="submit" name="reject_request">Reject</button></form>';
                            echo "</td>";
                            echo "</tr>";
                        }
                        $result->free();
                    }
                    ?>
                </tbody>
            </table>
            <h2>Assignment History</h2>
            <table class="history-table">
                <thead>
                    <tr><th>Student Name</th><th>Room Number</th><th>Assigned Date</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT s.name, r.room_number, rr.approved_date FROM room_requests rr JOIN students s ON rr.student_id = s.id JOIN rooms r ON rr.room_id = r.id WHERE rr.status = 'approved' ORDER BY rr.approved_date DESC";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
                            echo "<td>" . $row['approved_date'] . "</td>";
                            echo "</tr>";
                        }
                        $result->free();
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
