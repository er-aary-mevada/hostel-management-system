<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if user is admin
if ($_SESSION["username"] !== 'admin') {
    header("location: student_settings.php");
    exit;
}

$success_msg = "";
$error_msg = "";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Update profile
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        $sql = "UPDATE users SET username = ?, email = ? WHERE username = 'admin'";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $email);
            if ($stmt->execute()) {
                $_SESSION["username"] = $username;
                $_SESSION["email"] = $email;
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Error updating profile.";
            }
            $stmt->close();
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match.";
        } else {
            // Verify current password
            $sql = "SELECT password FROM users WHERE username = 'admin'";
            if ($result = $conn->query($sql)) {
                $row = $result->fetch_assoc();
                
                // Verify password (support both bcrypt and md5 for backward compatibility)
                $password_valid = false;
                if (password_verify($current_password, $row['password'])) {
                    $password_valid = true;
                } elseif (md5($current_password) === $row['password']) {
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    // Always use bcrypt for new password
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("s", $new_password_hash);
                        if ($stmt->execute()) {
                            $success_msg = "Password changed successfully!";
                        } else {
                            $error_msg = "Error changing password.";
                        }
                        $stmt->close();
                    }
                } else {
                    $error_msg = "Current password is incorrect.";
                }
            }
        }
    }
}

// Get current admin info
$admin_username = $_SESSION["username"];
$admin_email = $_SESSION["email"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - HMS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="students.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a href="rooms.php" class="nav-item">
                    <i class="fas fa-bed"></i>
                    <span>Rooms</span>
                </a>
                <a href="admin_room_requests.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Room Requests</span>
                </a>
                <a href="payments.php" class="nav-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payments</span>
                </a>
                <a href="settings.php" class="nav-item active">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>Admin Settings</h1>
                <p>Manage your profile and password</p>
            </div>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-error">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Profile Settings -->
                <div class="settings-section">
                    <h3>Profile Information</h3>
                    <form method="post" class="settings-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" 
                                   value="<?php echo htmlspecialchars($admin_username); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" 
                                   value="<?php echo htmlspecialchars($admin_email); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn">
                            Update Profile
                        </button>
                    </form>
                </div>

                <!-- Password Settings -->
                <div class="settings-section">
                    <h3>Change Password</h3>
                    <form method="post" class="settings-form">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Clean and Simple JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Password confirmation validation
            const confirmPasswordField = document.getElementById('confirm_password');
            if (confirmPasswordField) {
                confirmPasswordField.addEventListener('input', function() {
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = this.value;
                    
                    if (newPassword !== confirmPassword) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Simple Navigation Fix
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

    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .settings-section {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .settings-section h3 {
            margin: 0 0 25px 0;
            color: #333;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .settings-form {
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }

        .btn {
            padding: 12px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background: #0056b3;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .settings-section {
                padding: 20px;
            }
            
            .settings-form {
                max-width: 100%;
            }
        }
    </style>
</body>
</html>