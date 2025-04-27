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
// Email configuration
$smtp_host = "smtp.gmail.com";
$smtp_username = "fatimahomeworldcenter52@gmail.com";
$smtp_password = "jajj nyfd yygj jjwr";
$smtp_port = 587;
$smtp_from_email = "noreply@gmail.com";
$smtp_from_name = "FATIMA HOME WORLD CENTER";

// Connect to database
function connectDB() {
    global $servername, $username, $password, $dbname;
    $connection = mysqli_connect($servername, $username, $password, $dbname);
    
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $connection;
}

// Send reset password email
function sendResetPasswordEmail($email, $firstname, $reset_token) {
    global $smtp_host, $smtp_username, $smtp_password, $smtp_port, $smtp_from_email, $smtp_from_name;
    
    // Include PHPMailer library
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;
        
        // Recipients
        $mail->setFrom($smtp_from_email, $smtp_from_name);
        $mail->addAddress($email);
        
        // Generate reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/fatima/reset_password.php?token=" . $reset_token;
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e3e3e3; border-radius: 5px;">
        <h2 style="color: #4a90e2;">Reset Your Password</h2>
        <p>Hello ' . htmlspecialchars($firstname) . ',</p>
        <p>We received a request to reset your password. Please click the link below to set a new password:</p>
        <div style="margin: 20px 0;">
        <a href="' . $reset_link . '" style="background-color: #4a90e2; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a>
        </div>
        <p>Or copy and paste this URL into your browser:</p>
        <p style="word-break: break-all;">' . $reset_link . '</p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request a password reset, you can safely ignore this email.</p>
        <p>Best regards,<br>FATIMA HOME WORLD CENTER Team</p>
        </div>
        </body>
        </html>
        ';
        $mail->AltBody = 'Hello ' . $firstname . ', You requested a password reset. Please follow this link to reset your password: ' . $reset_link . '. This link will expire in 1 hour.';
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Generate unique reset token
function generateResetToken() {
    return bin2hex(random_bytes(32)); // 64 character token
}

$errors = array();
$success = false;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $connection = connectDB();
    
    // Sanitize input
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists in database
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $query);
        
        if (mysqli_num_rows($result) == 0) {
            $errors[] = "Email not found in our records";
        } else {
            $user = mysqli_fetch_assoc($result);
            
            // Generate reset token and expiry
            $reset_token = generateResetToken();
            $token_expiry = date('Y-m-d H:i:s', strtotime('+ 2 days'));
            
            // Update user record with reset token
            $update_query = "UPDATE users SET reset_token = '$reset_token', reset_token_expiry = '$token_expiry' WHERE email = '$email'";
            
            if (mysqli_query($connection, $update_query)) {
                // Send reset email
                if (sendResetPasswordEmail($email, $user['firstname'], $reset_token)) {
                    $success = true;
                } else {
                    $errors[] = "Failed to send reset email. Please try again.";
                }
            } else {
                $errors[] = "System error: " . mysqli_error($connection);
            }
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
    <title>Forgot Password - FATIMA HOME WORLD CENTER</title>
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
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        
        <?php if ($success) : ?>
            <div class="alert alert-success">
                <p>A password reset link has been sent to your email address. Please check your inbox and follow the instructions to reset your password.</p>
                <p>The link will expire in 1 hour.</p>
            </div>
        <?php else : ?>
            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error) : ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                </div>
                
                <button type="submit" class="btn">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>