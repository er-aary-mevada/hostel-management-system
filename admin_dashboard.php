<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
// Check if user is admin (add your own logic, e.g. user_type column)
// For demo, assume admin if username is 'admin'
if($_SESSION["username"] !== 'admin'){
    header("location: student_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item active">Dashboard</a>
                <a href="students.php" class="nav-item">Manage Students</a>
                <a href="rooms.php" class="nav-item">Manage Rooms</a>
                <a href="admin_room_requests.php" class="nav-item">Room Requests</a>
                <a href="payments.php" class="nav-item">Payments</a>
                <a href="settings.php" class="nav-item">Settings</a>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </div>
        <div class="main-content">
            <h1>Welcome, Admin!</h1>
            <p>Here you can manage students, rooms, payments, and settings.</p>
        </div>
    </div>
</body>
</html>
