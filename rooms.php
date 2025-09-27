<?php
session_start();
require_once "config.php";


if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Determine if user is admin
$is_admin = isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com';

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
        $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : '';
        if ($room_number !== '' && $capacity !== '') {
            // Check for duplicate room number
            $sql_check = "SELECT id FROM rooms WHERE room_number = ?";
            if ($stmt_check = $conn->prepare($sql_check)) {
                $stmt_check->bind_param("s", $room_number);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    echo "<script>alert('Room number already exists. Please use a unique room number.');</script>";
                } else {
                    $sql = "INSERT INTO rooms (room_number, capacity) VALUES (?, ?)";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("si", $room_number, $capacity);
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
        $sql_check = "SELECT capacity, current_occupancy FROM rooms WHERE id = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("i", $room_id);
            $stmt_check->execute();
            $stmt_check->bind_result($capacity, $current_occupancy);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($current_occupancy < $capacity) {
                $sql_assign = "UPDATE students SET room_id = ? WHERE id = ?";
                if ($stmt_assign = $conn->prepare($sql_assign)) {
                    $stmt_assign->bind_param("ii", $room_id, $student_id);
                    $stmt_assign->execute();
                    $stmt_assign->close();

                    // Update room occupancy
                    $sql_update_occupancy = "UPDATE rooms SET current_occupancy = current_occupancy + 1 WHERE id = ?";
                    if ($stmt_update = $conn->prepare($sql_update_occupancy)) {
                        $stmt_update->bind_param("i", $room_id);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                }
            } else {
                echo "<script>alert('Room is full!');</script>";
            }
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        

        <!-- Main Content -->
        <div class="main-content">
            <h1>Rooms Management</h1>
            
            <!-- Add Room Form -->
            <?php if ($is_admin): ?>
                <div class="back-button">
                    <a href="admin_dashboard.php" class="btn" style="margin-top:10px;margin-bottom:20px;display:inline-block;">&larr; Back to Dashboard</a>
            <div class="add-room-section">
                <button onclick="toggleAddForm()" class="add-btn">+ Add New Room</button>
                <div id="addFormContainer" class="add-form" style="display: none;">
                    <form action="rooms.php" method="post">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Room Number</label>
                                <input type="text" name="room_number" placeholder="Enter room number" required>
                            </div>
                            <div class="form-group">
                                <label>Room Capacity</label>
                                <input type="number" name="capacity" placeholder="Enter room capacity" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="add_room" class="save-btn">✓ Save Room</button>
                            <button type="button" onclick="toggleAddForm()" class="cancel-btn">✕ Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

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
                            <th>Capacity</th>
                            <th>Current Occupancy</th>
                            <th>Status</th>
                            <th>Actions</th>
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
                            if ($is_admin) {
                                // Remove button moved to assigned rooms list
                            } else {
                                // Show assigned status if student is assigned
                                $student_email = $_SESSION['email'];
                                $sql_student = "SELECT room_id FROM students WHERE email = '" . $conn->real_escape_string($student_email) . "'";
                                $result_student = $conn->query($sql_student);
                                $student_room_id = null;
                                if ($result_student && $row_student = $result_student->fetch_assoc()) {
                                    $student_room_id = $row_student['room_id'];
                                }
                                if ($student_room_id == $row['id']) {
                                    echo '<span>Assigned</span>';
                                } else if ($status === 'Available') {
                                    echo '<form method="post" action="rooms.php" style="display:inline;"><input type="hidden" name="room_id" value="' . $row['id'] . '"><button type="submit" name="apply_room">Apply</button></form>';
                                } else {
                                    echo '<span>Full</span>';
                                }
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
