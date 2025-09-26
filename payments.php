<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Show different content for admin and student
$is_admin = isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - HMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo $is_admin ? 'Admin Panel' : 'Student Panel'; ?></h2>
            </div>
            <nav class="sidebar-nav">
                <?php if ($is_admin): ?>
                    <a href="admin_dashboard.php" class="nav-item">Dashboard</a>
                    <a href="students.php" class="nav-item">Manage Students</a>
                    <a href="rooms.php" class="nav-item">Manage Rooms</a>
                    <a href="admin_room_requests.php" class="nav-item">Room Requests</a>
                    <a href="payments.php" class="nav-item active">Payments</a>
                    <a href="settings.php" class="nav-item">Settings</a>
                    <a href="logout.php" class="nav-item logout">Logout</a>
                <?php else: ?>
                    <a href="student_dashboard.php" class="nav-item">Dashboard</a>
                    <a href="profile.php" class="nav-item">My Profile</a>
                    <a href="student_rooms.php" class="nav-item">View Rooms</a>
                    <a href="student_payment.php" class="nav-item">My Payments</a>
                    <a href="logout.php" class="nav-item logout">Logout</a>
                <?php endif; ?>
            </nav>
        </div>
        <div class="main-content">
            <h1>Payments</h1>
            <?php if ($is_admin): ?>
                <h2>Student Payment Details</h2>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Room</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT s.name, s.email, r.room_number, s.room_id, s.payment_status FROM students s LEFT JOIN rooms r ON s.room_id = r.id";
                        if ($result = $conn->query($sql)) {
                            while ($row = $result->fetch_assoc()) {
                                $paid = ($row['payment_status'] === 'Paid') ? 'Paid' : 'Not Paid';
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . ($row['room_number'] ? $row['room_number'] : 'Not Assigned') . "</td>";
                                echo "<td>1000</td>";
                                echo "<td>" . $paid . "</td>";
                                echo "</tr>";
                            }
                            $result->free();
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Make your payment below. (Demo)</p>
                <form method="post" action="student_payment.php" class="payment-form">
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" value="1000" readonly>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="method">
                            <option value="card">Credit/Debit Card</option>
                            <option value="upi">UPI</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <button type="submit" name="pay" class="pay-btn">Pay Now</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
