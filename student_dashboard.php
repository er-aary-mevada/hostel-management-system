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
    <style>
        body {
            background: linear-gradient(120deg, #1976d2 0%, #2563eb 100%);
            min-height: 100vh;
            font-family: 'Poppins', Arial, sans-serif;
        }
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background: #fff;
            min-width: 260px;
            max-width: 300px;
            box-shadow: 0 8px 32px rgba(21,101,192,0.10);
            border-radius: 0 24px 24px 0;
            padding: 32px 0 0 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 32px;
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 18px;
            width: 100%;
            align-items: center;
        }
        .sidebar-nav a {
            font-size: 1.08rem;
            color: #607d8b;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 10px;
            transition: background 0.2s, color 0.2s;
            width: 80%;
            text-align: center;
        }
        .sidebar-nav a.active, .sidebar-nav a:hover {
            background: #e3f2fd;
            color: #1976d2;
        }
        .sidebar-nav a.logout {
            color: #d32f2f;
            margin-top: 32px;
        }
        .sidebar-nav a.logout:hover {
            background: #ffebee;
            color: #b71c1c;
        }
        .main-content {
            flex: 1;
            background: #fff;
            border-radius: 24px;
            margin: 32px;
            box-shadow: 0 8px 32px rgba(21,101,192,0.10);
            padding: 40px 48px;
        }
        .main-content h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 18px;
        }
        .main-content p {
            color: #607d8b;
            font-size: 1.08rem;
            margin-bottom: 32px;
        }

    </style>
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <div class="sidebar-title">Student Panel</div>
            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-btn active" onclick="loadSection('student_dashboard_home.php', this); return false;">Dashboard</a>
                <a href="#profile" class="nav-btn" onclick="loadSection('profile.php', this); return false;">My Profile</a>
                <a href="student_rooms.php" class="nav-btn">View Rooms</a>
                <a href="#payments" class="nav-btn" onclick="loadSection('student_payment.php', this); return false;">My Payments</a>
                   <!-- Removed Dashboard, My Profile, View Rooms, My Payments, Account Settings, Logout -->
                   <a href="student_settings.php" class="nav-btn">Account Settings</a>
                   <a href="logout.php" class="nav-btn logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
               </nav>
        </div>
        <div class="main-content">
            <div id="main-area">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    <script>
        function loadSection(url, btn) {
            document.querySelectorAll('.nav-btn').forEach(function(b){ b.classList.remove('active'); });
            if(btn) btn.classList.add('active');
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    let tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    let content = tempDiv.querySelector('.main-content');
                    document.getElementById('main-area').innerHTML = content ? content.innerHTML : html;
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            loadSection('student_dashboard_home.php', document.querySelector('.nav-btn.active'));
        });
    </script>

