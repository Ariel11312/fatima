<?php
session_start();
        
// First, make sure we're not sending any output before the headers
ob_start();
// Include only once to prevent duplicate inclusion
include_once("./admin/database/connection.php");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fatima_db";

// Connect to database
function connectDB() {
    global $servername, $username, $password, $dbname;
    $connection = mysqli_connect($servername, $username, $password, $dbname);
    
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $connection;
}

$errors = array();
$success = false;
$valid_token = false;
$user = null;

// Check if token is provided in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $connection = connectDB();
    
    // Get user with matching token that hasn't expired
    $query = "SELECT * FROM users WHERE reset_token = '" . mysqli_real_escape_string($connection, $token) . "' AND reset_token_expiry > NOW()";
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $valid_token = true;
    } else {
        $errors[] = "Invalid or expired token. Please request a new password reset link.";
    }
    
    mysqli_close($connection);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $connection = connectDB();
    
    // Sanitize and validate input
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = mysqli_real_escape_string($connection, $_POST['token']);
    
    // Validation
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($new_password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, proceed with password update
    if (empty($errors)) {
        // Hash password
        $hashed_password = md5($new_password);
        
        // Update user password and clear reset token
        $update_query = "UPDATE users SET 
                         password = '$hashed_password', 
                         reset_token = NULL, 
                         reset_token_expiry = NULL 
                         WHERE reset_token = '$token'";
        
        if (mysqli_query($connection, $update_query)) {
            $success = true;
        } else {
            $errors[] = "Failed to update password: " . mysqli_error($connection);
        }
    }
    
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FATIMA HOME WORLD CENTER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #4a90e2;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }
        .btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #357bd8;
        }
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .password-rules {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        
        <?php if ($success) : ?>
            <div class="alert alert-success">
                <p>Your password has been successfully reset!</p>
                <p>You can now log in with your new password.</p>
            </div>
            <div class="back-link">
                <a href="login.php" class="btn">Go to Login</a>
            </div>
        <?php elseif ($valid_token) : ?>
            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error) : ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-container">
                        <input type="password" id="new_password" name="new_password" required>
                        <span class="toggle-password" onclick="togglePassword('new_password')"><i class="fas fa-eye"></i></span>
                    </div>
                    <div class="password-rules">
                        Password must be at least 8 characters long.
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-container">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
        <?php else : ?>
            <div class="alert alert-danger">
                <?php 
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        echo "<p>$error</p>";
                    }
                } else {
                    echo "<p>Invalid or missing reset token. Please request a new password reset link.</p>";
                }
                ?>
            </div>
            <div class="back-link">
                <a href="forgot-password.php">Request new reset link</a>
            </div>
        <?php endif; ?>
        
        <?php if (!$success) : ?>
            <div class="back-link">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>