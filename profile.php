<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$email = $_SESSION["email"];

// Get user and student details with room info
$sql = "SELECT u.username, u.email, u.role, s.name, s.phone, s.address, s.room_id, s.payment_status, r.room_number, r.room_type 
        FROM users u 
        LEFT JOIN students s ON u.id = s.user_id 
        LEFT JOIN rooms r ON s.room_id = r.id 
        WHERE u.email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Check if user is student
$is_student = $user_data && isset($user_data['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #1976d2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .profile-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .profile-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .profile-body {
            padding: 40px;
        }
        .back-button {
            margin-bottom: 20px;
        }
        .back-button a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .back-button a:hover {
            background: #1565c0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #1976d2;
            font-size: 1.3rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3f2fd;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #1976d2;
        }
        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-value {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .status-paid {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-pending {
            background: #fff3e0;
            color: #ef6c00;
        }
        .status-assigned {
            background: #e3f2fd;
            color: #1565c0;
        }
        .status-not-assigned {
            background: #fce4ec;
            color: #c2185b;
        }
        .edit-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        .edit-button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1><?php echo htmlspecialchars($is_student ? $user_data['name'] : $user_data['username']); ?></h1>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user_data['email']); ?></p>
        </div>
        
        <div class="profile-body">
            <div class="back-button">
                <a href="student_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>

            <?php if ($is_student): ?>
            <!-- Personal Information -->
            <div class="info-section">
                <h2><i class="fas fa-user-circle"></i> Personal Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-id-card"></i> Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user"></i> Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone Number</div>
                        <div class="info-value"><?php echo $user_data['phone'] ? htmlspecialchars($user_data['phone']) : 'Not provided'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="info-section">
                <h2><i class="fas fa-map-marker-alt"></i> Address</h2>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-home"></i> Residential Address</div>
                    <div class="info-value"><?php echo $user_data['address'] ? htmlspecialchars($user_data['address']) : 'Not provided'; ?></div>
                </div>
            </div>

            <!-- Room Information -->
            <div class="info-section">
                <h2><i class="fas fa-door-open"></i> Room Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-building"></i> Room Status</div>
                        <div class="info-value">
                            <?php if ($user_data['room_id']): ?>
                                <span class="status-badge status-assigned">
                                    <i class="fas fa-check-circle"></i> Assigned
                                </span>
                            <?php else: ?>
                                <span class="status-badge status-not-assigned">
                                    <i class="fas fa-times-circle"></i> Not Assigned
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($user_data['room_id']): ?>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hashtag"></i> Room Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['room_number']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-home"></i> Room Type</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['room_type'] ?? 'N/A'); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="info-section">
                <h2><i class="fas fa-credit-card"></i> Payment Information</h2>
                <div class="info-item">
                    <div class="info-label"><i class="fas fa-money-bill-wave"></i> Payment Status</div>
                    <div class="info-value">
                        <?php 
                        $payment_status = $user_data['payment_status'] ?? 'pending';
                        if ($payment_status === 'paid'): ?>
                            <span class="status-badge status-paid">
                                <i class="fas fa-check-circle"></i> Paid
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-pending">
                                <i class="fas fa-clock"></i> Pending
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Button -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="edit_profile.php" class="edit-button">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>

            <?php else: ?>
            <!-- For Admin or users without student profile -->
            <div class="info-section">
                <h2><i class="fas fa-user-shield"></i> Account Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user"></i> Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-shield-alt"></i> Role</div>
                        <div class="info-value"><?php echo ucfirst($user_data['role']); ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
