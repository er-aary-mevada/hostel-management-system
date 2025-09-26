<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Only allow non-admins
if (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com') {
    header("location: rooms.php");
    exit;
}

// Handle room application
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply_room'])) {
    $room_id = $_POST['room_id'];
    $student_email = $_SESSION['email'];
    $sql_student = "SELECT id, room_id FROM students WHERE email = ?";
    if ($stmt_student = $conn->prepare($sql_student)) {
        $stmt_student->bind_param("s", $student_email);
        $stmt_student->execute();
        $stmt_student->bind_result($student_id, $current_room_id);
        $found = $stmt_student->fetch();
        $stmt_student->close();
        if (!$found || !$student_id) {
            echo "<script>alert('Student record not found. Please contact admin.');</script>";
        } else if ($current_room_id) {
            echo "<script>alert('You are already assigned to a room.');</script>";
        } else {
            // Check if already requested
            $sql_check_req = "SELECT id FROM room_requests WHERE student_id = ? AND status = 'pending'";
            if ($stmt_check_req = $conn->prepare($sql_check_req)) {
                $stmt_check_req->bind_param("i", $student_id);
                $stmt_check_req->execute();
                $stmt_check_req->store_result();
                if ($stmt_check_req->num_rows > 0) {
                    echo "<script>alert('You already have a pending room request.');</script>";
                } else {
                    // Insert new request
                    $sql_insert = "INSERT INTO room_requests (student_id, room_id, status) VALUES (?, ?, 'pending')";
                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("ii", $student_id, $room_id);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                        echo "<script>alert('Room request submitted! Wait for admin approval.');</script>";
                    }
                }
                $stmt_check_req->close();
            }
        }
    } else {
        echo "<script>alert('Database error. Please contact admin.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rooms - HMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="student_dashboard.php" class="nav-item">Dashboard</a>
                <a href="profile.php" class="nav-item">My Profile</a>
                <a href="student_rooms.php" class="nav-item active">View Rooms</a>
                <a href="payments.php" class="nav-item">My Payments</a>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </div>
        <div class="main-content">
            <h1>Available Rooms</h1>
            <table class="rooms-table">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Current Occupancy</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM rooms ORDER BY room_number";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            $status = $row['current_occupancy'] >= $row['capacity'] ? 'Full' : 'Available';
                            $statusClass = $status === 'Full' ? 'status-full' : 'status-available';
                            echo "<tr>";
                            echo "<td>" . $row['room_number'] . "</td>";
                            echo "<td>" . $row['capacity'] . "</td>";
                            echo "<td>" . $row['current_occupancy'] . "</td>";
                            echo "<td class='" . $statusClass . "'>" . $status . "</td>";
                            echo "<td>";
                            if ($status === 'Available') {
                                $student_email = $_SESSION['email'];
                                $sql_student = "SELECT room_id FROM students WHERE email = '" . $conn->real_escape_string($student_email) . "'";
                                $result_student = $conn->query($sql_student);
                                $student_room_id = null;
                                if ($result_student && $row_student = $result_student->fetch_assoc()) {
                                    $student_room_id = $row_student['room_id'];
                                }
                                if (!$student_room_id) {
                                    echo '<form method="post" action="student_rooms.php" style="display:inline;"><input type="hidden" name="room_id" value="' . $row['id'] . '"><button type="submit" name="apply_room">Apply</button></form>';
                                } else {
                                    echo '<span>Assigned</span>';
                                }
                            } else {
                                echo '<span>Full</span>';
                            }
                            echo "</td>";
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
