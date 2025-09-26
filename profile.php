<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Get user info from database
$email = $_SESSION["email"];
$sql = "SELECT username, email FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HMS</title>
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
                <a href="profile.php" class="nav-item active">My Profile</a>
                <a href="student_rooms.php" class="nav-item">View Rooms</a>
                <a href="student_payment.php" class="nav-item">My Payments</a>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </div>
        <div class="main-content">
            <h1>My Profile</h1>
            <div class="profile-box">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
