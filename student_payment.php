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
<div class="main-content" style="max-width:600px;margin:0 auto;">
    <!-- Removed Make Payment heading as requested -->
    <!-- Success message only inside payment-area -->
    <div id="payment-area">
    <?php if ($success): ?>
        <div style="background:#e3f2fd;color:#1976d2;padding:16px 24px;border-radius:8px;margin-bottom:24px;text-align:center;font-weight:500;">Payment successful! (Dummy)</div>
        <button onclick="backToDashboard()" style="margin:24px auto 0 auto;display:block;background:linear-gradient(90deg,#1976d2 60%,#64b5f6 100%);color:#fff;font-weight:600;padding:12px 32px;border-radius:8px;border:none;font-size:1.1rem;cursor:pointer;">Back to Dashboard</button>
        <script>
        function backToDashboard() {
            if (window.parent && window.parent.loadSection) {
                window.parent.loadSection('student_dashboard_home.php', document.querySelector('.nav-btn'));
            } else if (typeof loadSection === 'function') {
                loadSection('student_dashboard_home.php', document.querySelector('.nav-btn'));
            } else {
                window.location.href = 'student_dashboard.php';
            }
        }
        </script>
    <?php else: ?>
        <form id="paymentForm" method="post" action="student_payment.php" class="payment-form" style="background:#fff;padding:32px 24px;border-radius:16px;box-shadow:0 4px 24px rgba(21,101,192,0.07);">
            <div class="form-group" style="margin-bottom:24px;">
                <label for="amount" style="font-weight:600;margin-bottom:8px;display:block;">Amount</label>
                <input type="text" id="amount" name="amount" value="1000" class="form-control" style="width:100%;padding:12px 16px;border-radius:8px;border:1px solid #e3eafc;font-size:1.1rem;" />
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label for="method" style="font-weight:600;margin-bottom:8px;display:block;">Payment Method</label>
                <select id="method" name="method" class="form-control" style="width:100%;padding:12px 16px;border-radius:8px;border:1px solid #e3eafc;font-size:1.1rem;">
                    <option value="card">Credit/Debit Card</option>
                    <option value="upi">UPI</option>
                    <option value="netbanking">Net Banking</option>
                </select>
            </div>
            <button type="submit" name="pay" class="btn btn-primary" style="background:linear-gradient(90deg,#1976d2 60%,#64b5f6 100%);color:#fff;font-weight:600;padding:12px 32px;border-radius:8px;border:none;font-size:1.1rem;cursor:pointer;">Pay Now</button>
        </form>
    <?php endif; ?>
                <script>
                document.getElementById('paymentForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    var form = e.target;
                    var formData = new FormData(form);
                    fetch('student_payment.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Replace only the payment-area div, not the whole main-content
                        var container = document.getElementById('payment-area');
                        if (container) {
                            container.innerHTML = html;
                        }
                    });
                });
                </script>
                </form>