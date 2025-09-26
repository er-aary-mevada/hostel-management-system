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
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #1976d2 0%, #2563eb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', Arial, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(21,101,192,0.18);
            padding: 40px 32px 32px 32px;
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
            text-align: center;
        }
        .login-card .login-icon {
            font-size: 2.8rem;
            color: #1976d2;
            margin-bottom: 10px;
        }
        .login-card h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .login-card p {
            color: #607d8b;
            font-size: 1.08rem;
            margin-bottom: 24px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }
        .form-group label {
            font-weight: 500;
            color: #1976d2;
            display: block;
            margin-bottom: 6px;
        }
        .form-group label i {
            margin-right: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #e3f2fd;
            background: #f4f6f8;
            font-size: 1rem;
            margin-bottom: 2px;
            transition: border 0.2s;
        }
        .form-control:focus {
            border-color: #1976d2;
            outline: none;
        }
        .login-button {
            width: 100%;
            padding: 12px 0;
            background: #1976d2;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(21,101,192,0.10);
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .login-button:hover {
            background: #2563eb;
        }
        .back-link {
            text-align: center;
            margin-top: 18px;
        }
        .back-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.08rem;
            transition: color 0.2s;
        }
        .back-link a:hover {
            color: #1976d2;
        }
        .alert-success {
            background: #e3fcec;
            color: #388e3c;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 16px;
        }
        .error-msg {
            color: #d32f2f;
            font-size: 0.98rem;
            margin-top: 2px;
            display: block;
        }
    </style>
    <div class="login-card">
        <div class="login-icon"><i class="fas fa-building"></i></div>
        <h1>Sign Up</h1>
        <p>Create a new account</p>
        <?php 
        if(!empty($success_msg)){
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>
        <form action="signup.php" method="post">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($username); ?>">
                <span class="error-msg"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
                <span class="error-msg"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
                <span class="error-msg"><?php echo $password_err; ?></span>
            </div>
            <button type="submit" class="login-button">Sign Up</button>
        </form>
        <div class="back-link"><a href="login.php">Already have an account? Login</a></div>
    </div>
</body>
</html>
