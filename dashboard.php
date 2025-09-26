    <?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.html");
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
        <h1>🏢 Admin Dashboard</h1>
        <p>Welcome to the Hostel Management System. Choose a function below to manage your hostel efficiently.</p>
        
        <div class="card-grid">
            <div class="function-card">
                <h3>👥 Manage Students</h3>
                <p>Add new students, view existing records, and manage student information efficiently.</p>
                <a href="students.php" class="btn btn-primary">Open Students</a>
            </div>
            
            <div class="function-card">
                <h3>🏠 Manage Rooms</h3>
                <p>Add rooms, assign students to rooms, and track room occupancy status.</p>
                <a href="rooms.php" class="btn btn-primary">Open Rooms</a>
            </div>
            
            <div class="function-card">
                <h3>📋 Room Requests</h3>
                <p>Review student room applications and approve or reject requests.</p>
                <a href="admin_room_requests.php" class="btn btn-primary">View Requests</a>
            </div>
            
            <div class="function-card">
                <h3>💰 Payment Management</h3>
                <p>Monitor payment status, track dues, and manage financial records.</p>
                <a href="payments.php" class="btn btn-primary">View Payments</a>
            </div>
            
            <div class="function-card">
                <h3>⚙️ System Settings</h3>
                <p>Configure system preferences, update admin profile, and manage settings.</p>
                <a href="settings.php" class="btn btn-primary">Open Settings</a>
            </div>
            
            <div class="function-card logout-card">
                <h3>🚪 Logout</h3>
                <p>Safely exit the admin dashboard and end your session.</p>
                <a href="logout.php" class="btn btn-danger">Logout Now</a>
            </div>
        </div>
    </div>
</body>
</html>