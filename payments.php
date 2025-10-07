<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}

// Show different content for admin and student
$is_admin = isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com';
$dashboard_link = $is_admin ? 'admin_dashboard.php' : 'student_dashboard.php';
$dashboard_title = $is_admin ? 'Admin Dashboard' : 'Student Dashboard';
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
        <div class="main-content">
            <h1>Payments</h1>
                    <div class="back-button">
            <a href="<?php echo $dashboard_link; ?>" class="btn" style="margin-top:10px;margin-bottom:20px;display:inline-block;">&larr; Back to <?php echo $dashboard_title; ?></a>
        </div>
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
                        // Check if payment_status column exists
                        $payment_column_exists = columnExists($conn, 'students', 'payment_status');
                        
                        $sql = "SELECT s.name, s.email, r.room_number, s.room_id" . 
                               ($payment_column_exists ? ", s.payment_status" : "") . 
                               " FROM students s LEFT JOIN rooms r ON s.room_id = r.id";
                               
                        if ($result = $conn->query($sql)) {
                            while ($row = $result->fetch_assoc()) {
                                $paid = $payment_column_exists ? 
                                       safeGetColumn($row, 'payment_status', 'Not Paid') : 
                                       'Not Paid';
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
