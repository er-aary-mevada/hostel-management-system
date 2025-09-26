<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_student'])) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $sql = "INSERT INTO students (name, phone, email) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $name, $phone, $email);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['delete_student'])) {
        $id = $_POST['student_id'];
        $sql = "DELETE FROM students WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - HMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-building"></i>
                <h2>HMS</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <span>Dashboard</span>
                </a>
                <a href="students.php" class="nav-item active">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span>Students</span>
                </a>
                <a href="rooms.php" class="nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <span>Rooms</span>
                </a>
                <a href="payments.php" class="nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <span>Payments</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span>Settings</span>
                </a>
                <div class="sidebar-divider"></div>
                <a href="logout.php" class="nav-item logout">
                    <div class="nav-icon">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-content">
                <div class="content-header">
                    <h1>Students Management</h1>
                    <button class="add-new-btn" onclick="toggleAddForm()">
                        <i class="fas fa-plus"></i> Add New Student
                    </button>
                </div>

                <!-- Add Student Form -->
                <div class="add-form-container" id="addFormContainer">
                    <form action="students.php" method="post" class="add-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" name="name" id="name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email">
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="add_student" class="submit-btn">
                                <i class="fas fa-save"></i> Save Student
                            </button>
                            <button type="button" class="cancel-btn" onclick="toggleAddForm()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                </div>

        <h3>All Students</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Room</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT s.id, s.name, s.phone, s.email, r.room_number FROM students s LEFT JOIN rooms r ON s.room_id = r.id";
                if ($result = $conn->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . $row['phone'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . ($row['room_number'] ? $row['room_number'] : 'Not Assigned') . "</td>";
                        echo '<td>
                                <form action="students.php" method="post" style="display:inline;">
                                    <input type="hidden" name="student_id" value="' . $row['id'] . '">
                                    <button type="submit" name="delete_student">Delete</button>
                                </form>
                              </td>';
                        echo "</tr>";
                    }
                    $result->free();
                }
                ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
    
    <script>
    function toggleAddForm() {
        const formContainer = document.getElementById('addFormContainer');
        formContainer.classList.toggle('show');
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
</body>
</html>
