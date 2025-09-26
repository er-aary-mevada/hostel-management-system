<?php
session_start();
require_once "config.php";

// Check if the user is already logged in

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM users WHERE email = ?";

    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("s", $param_email);
        $param_email = $email;

        if($stmt->execute()){
            $stmt->store_result();

            if($stmt->num_rows == 1){
                $stmt->bind_result($id, $username, $hashed_password);
                if($stmt->fetch()){
                    if(password_verify($password, $hashed_password)){
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["email"] = $email;
                        if ($email === 'admin1@gmail.com') {
                            header("location: admin_dashboard.php");
                        } else {
                            header("location: student_dashboard.php");
                        }
                    } else{
                        echo "The password you entered was not valid.";
                    }
                }
            } else{
                echo "No account found with that email.";
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .alert-danger {
            background: #ffebee;
            color: #d32f2f;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 16px;
        }
    </style>
    <div class="login-card">
        <div class="login-icon"><i class="fas fa-building"></i></div>
        <h1>HMS</h1>
        <p>Login to Hostel Management System</p>
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        <div class="back-link"><a href="index.html"><i class="fas fa-arrow-left"></i> Back to Home</a></div>
    </div>
</body>
</html>