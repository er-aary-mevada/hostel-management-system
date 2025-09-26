<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if($_SESSION["username"] === 'admin'){
    header("location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - HMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>🎓 Student Dashboard</h1>
        <p>Welcome to your student portal! Manage your hostel life and stay updated with all your information.</p>

        <?php
        require_once "config.php";
        if (isset($_SESSION["email"])) {
            $student_email = $_SESSION["email"];
            $sql = "SELECT r.room_number, r.capacity FROM students s LEFT JOIN rooms r ON s.room_id = r.id WHERE s.email = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $student_email);
                $stmt->execute();
                $stmt->bind_result($room_number, $capacity);
                if ($stmt->fetch() && $room_number) {
                    echo '<div class="status-box status-success">';
                    echo '<h3>🏠 Your Assigned Room</h3>';
                    echo '<p><strong>Room Number:</strong> ' . htmlspecialchars($room_number) . '</p>';
                    echo '<p><strong>Capacity:</strong> ' . htmlspecialchars($capacity) . ' persons</p>';
                    echo '<p>✅ Room successfully assigned to you!</p>';
                    echo '</div>';
                } else {
                    echo '<div class="status-box status-warning">';
                    echo '<h3>📋 Room Status</h3>';
                    echo '<p>No room assigned yet. Please apply for a room through the "View Rooms" section below.</p>';
                    echo '<p>💡 Tip: Check available rooms and submit your application today!</p>';
                    echo '</div>';
                }
                $stmt->close();
            }
        }
        ?>
        
        <div class="card-grid">
            <div class="function-card">
                <h3>👤 My Profile</h3>
                <p>View and update your personal information, contact details, and profile settings.</p>
                <a href="profile.php" class="btn btn-primary">View Profile</a>
            </div>
            
            <div class="function-card">
                <h3>🏠 Browse Rooms</h3>
                <p>Explore available hostel rooms and submit applications for your preferred accommodation.</p>
                <a href="student_rooms.php" class="btn btn-primary">Browse Rooms</a>
            </div>
            
            <div class="function-card">
                <h3>💳 My Payments</h3>
                <p>Check your payment history, view due amounts, and manage your financial records.</p>
                <a href="student_payment.php" class="btn btn-primary">View Payments</a>
            </div>
            
            <div class="function-card">
                <h3>⚙️ Account Settings</h3>
                <p>Update your account preferences, change password, and manage notification settings.</p>
                <a href="student_settings.php" class="btn btn-primary">Open Settings</a>
            </div>
            
            <div class="function-card logout-card">
                <h3>🚪 Logout</h3>
                <p>Safely exit your student dashboard and end your current session.</p>
                <a href="logout.php" class="btn btn-danger">Logout Now</a>
            </div>
        </div>
    </div>
</body>
</html>
