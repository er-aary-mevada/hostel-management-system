<div>
    <h1>Welcome, Student!</h1>
    <p>Here you can view your profile, rooms, and payments.</p>
    <h2 style="text-align:center;color:#222;font-size:1.5rem;margin-bottom:18px;">Your Assigned Room</h2>
    <?php
    require_once "config.php";
    session_start();
    if (isset($_SESSION["email"])) {
        $student_email = $_SESSION["email"];
        $sql = "SELECT r.room_number, r.capacity 
                FROM students s 
                LEFT JOIN users u ON s.user_id = u.id 
                LEFT JOIN rooms r ON s.room_id = r.id 
                WHERE u.email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $student_email);
            $stmt->execute();
            $stmt->bind_result($room_number, $capacity);
            if ($stmt->fetch() && $room_number) {
                echo '<div class="status-box status-success">';
                echo '<strong>Room Number:</strong> ' . htmlspecialchars($room_number) . '<br>';
                echo '<strong>Capacity:</strong> ' . htmlspecialchars($capacity) . '';
                echo '</div>';
            } else {
                echo '<div class="status-box status-warning">';
                echo 'No room assigned yet. Please apply for a room through the "View Rooms" section below.';
                echo '</div>';
            }
            $stmt->close();
        }
    }
    ?>
</div>
