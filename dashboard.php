    <?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.html");
    exit;
}

// Determine user type for consistent navigation
$is_admin = isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com';
$dashboard_link = $is_admin ? 'admin_dashboard.php' : 'student_dashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-nav {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .nav-link {
            background: #1976d2;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            transition: background 0.3s;
            font-weight: 500;
        }
        .nav-link:hover {
            background: #1565c0;
            text-decoration: none;
            color: white;
        }
        .nav-link.danger {
            background: #dc3545;
        }
        .nav-link.danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>🏢 Admin Dashboard</h1>
        <p>Welcome to the Hostel Management System Administration Panel.</p>
    </div>
</body>
</html>