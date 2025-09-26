<?php
require_once "config.php";

$username = $email = $password = "";
$username_err = $email_err = $password_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $hashed_password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    }

        // If no errors, insert into database
        if (empty($username_err) && empty($email_err) && empty($password_err)) {
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    $stmt->close();
                    // If not admin, also add to students table
                    if ($email !== 'admin1@gmail.com') {
                        $sql_student = "INSERT INTO students (name, email) VALUES (?, ?)";
                        if ($stmt_student = $conn->prepare($sql_student)) {
                            $stmt_student->bind_param("ss", $username, $email);
                            $stmt_student->execute();
                            $stmt_student->close();
                        }
                    }
                    $success_msg = "Account created successfully! You can now <a href='login.php'>login</a>.";
                } else {
                    $success_msg = "Error: Could not create account.";
                }
            }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Sign Up</h1>
                <p>Create a new account</p>
            </div>
            <?php 
            if(!empty($success_msg)){
                echo '<div class="alert alert-success">' . $success_msg . '</div>';
            }
            ?>
            <form action="signup.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>">
                    <span class="error-msg"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
                    <span class="error-msg"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                    <span class="error-msg"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="login-button">Sign Up</button>
                </div>
                <p class="back-link"><a href="login.php">Already have an account? Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>
