<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Determine user type for consistent navigation
$is_admin = (isset($_SESSION["username"]) && $_SESSION["username"] === 'admin') || 
            (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com');
$dashboard_link = $is_admin ? 'admin_dashboard.php' : 'student_dashboard.php';
$dashboard_title = $is_admin ? 'Admin Dashboard' : 'Student Dashboard';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (isset($_POST['add_student'])) {
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);

            // Validate input
            if (empty($name) || empty($email)) {
                throw new Exception("Name and email are required fields.");
            }

            // Check for duplicate email in users table
            $check_sql = "SELECT id FROM users WHERE email = ?";
            if ($check_stmt = $conn->prepare($check_sql)) {
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_stmt->store_result();
                
                if ($check_stmt->num_rows > 0) {
                    throw new Exception("A user with this email already exists.");
                }
                $check_stmt->close();
            }

            // Insert into users table first
            $sql_user = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')";
            $default_password = password_hash('password123', PASSWORD_DEFAULT); // Default password
            if ($stmt_user = $conn->prepare($sql_user)) {
                $stmt_user->bind_param("sss", $name, $email, $default_password);
                if (!$stmt_user->execute()) {
                    throw new Exception("Failed to create user account.");
                }
                $user_id = $conn->insert_id;
                $stmt_user->close();
                
                // Then insert into students table with user_id
                $sql = "INSERT INTO students (user_id, name, phone) VALUES (?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("iss", $user_id, $name, $phone);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to add student.");
                    }
                    $stmt->close();
                    $success_msg = "Student added successfully! Default password: password123";
                } else {
                    throw new Exception("Database error occurred.");
                }
            } else {
                throw new Exception("Database error occurred.");
            }
        } elseif (isset($_POST['delete_student'])) {
            $id = (int)$_POST['student_id'];
            
            if ($id <= 0) {
                throw new Exception("Invalid student ID.");
            }

            $sql = "DELETE FROM students WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to delete student.");
                }
                $stmt->close();
                $success_msg = "Student deleted successfully!";
            } else {
                throw new Exception("Database error occurred.");
            }
        }
    } catch (Exception $e) {
        error_log("Students.php error: " . $e->getMessage());
        $error_msg = $e->getMessage();
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
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f4f6fb;
            color: #222;
        }
        .main-content h1, .main-content h3 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 12px 16px;
            text-align: left;
            font-size: 15px;
        }
        th {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
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
        .form-actions button, .add-new-btn, .submit-btn, .cancel-btn, button[name="delete_student"] {
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
        .form-actions button:hover, .add-new-btn:hover, .submit-btn:hover, .cancel-btn:hover, button[name="delete_student"]:hover {
            background: #1565c0;
        }
        .back-button a {
            background: #1976d2;
            color: #fff;
            border-radius: 5px;
            padding: 8px 18px;
            text-decoration: none;
            font-size: 15px;
            margin-bottom: 10px;
            display: inline-block;
        }
        .back-button a:hover {
            background: #1565c0;
        }
        @media (max-width: 900px) {
            table { font-size: 13px; }
            th, td { padding: 8px 10px; }
        }
    </style>
</head>
<body class="dashboard-body">
    <div class="dashboard-container">
        <!-- Sidebar -->

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-content">
                <div class="content-header">
                    <h1>Students Management</h1>
                     <div class ="back-button">
                     <a href="<?php echo $dashboard_link; ?>" class="btn" style="margin-top:10px;margin-bottom:20px;display:inline-block;">&larr; Back to <?php echo $dashboard_title; ?></a>
                     </div>
                    <button class="add-new-btn" onclick="toggleAddForm()">
                        <i class="fas fa-plus"></i> Add New Student
                    </button>
                </div>

                <?php if (isset($success_msg)): ?>
                    <div class="success-message" style="background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;">
                        <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_msg)): ?>
                    <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;">
                        <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>

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
                $sql = "SELECT s.id, s.name, s.phone, u.email, r.room_number 
                        FROM students s 
                        LEFT JOIN users u ON s.user_id = u.id 
                        LEFT JOIN rooms r ON s.room_id = r.id";
                if ($result = $conn->query($sql)) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['name'] . "</td>";
                        echo "<td>" . ($row['phone'] ? $row['phone'] : 'N/A') . "</td>";
                        echo "<td>" . ($row['email'] ? $row['email'] : 'N/A') . "</td>";
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
