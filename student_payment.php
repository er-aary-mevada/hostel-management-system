<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Only allow non-admins
if (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com') {
    header("location: admin_dashboard.php");
    exit;
}

// Dummy payment logic
$success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
    $success = true;
    // Mark payment as paid for this student
    if (isset($_SESSION['email'])) {
        $student_email = $_SESSION['email'];
        $sql = "UPDATE students SET payment_status = 'Paid' WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $student_email);
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
    <title>Student Payment - HMS</title>
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
                <a href="profile.php" class="nav-item">My Profile</a>
                <a href="student_rooms.php" class="nav-item">View Rooms</a>
                <a href="student_payment.php" class="nav-item active">My Payments</a>
                <a href="logout.php" class="nav-item logout">Logout</a>
            </nav>
        </div>
        <div class="main-content">
            <h1>Make Payment</h1>
            <?php if ($success): ?>
                <div class="alert alert-success">Payment successful! (Dummy)</div>
            <?php endif; ?>
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
        </div>
    </div>
</body>
</html>
