<?php
session_start();
require_once "config.php";
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.html");
    exit;
}
if (isset($_SESSION["email"]) && $_SESSION["email"] === 'admin1@gmail.com') {
    header("location: admin_dashboard.php");
    exit;
}
$success = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
    $success = true;
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
<div>
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
