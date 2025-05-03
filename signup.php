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
$smtp_username = "fatimahomes123@gmail.com";
$smtp_password = "tirp jvsu rmtb ebhr";
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

// Send verification email
function sendVerificationEmail($email, $firstname, $verification_code) {
    global $smtp_host, $smtp_username, $smtp_password, $smtp_port, $smtp_from_email, $smtp_from_name;
    
    // Include PHPMailer library (you need to install it)
    // You can install it via composer: composer require phpmailer/phpmailer
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
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body    = '
        <html>
        <body style="font-family: Arial, sans-serif; line-height: 1.6;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e3e3e3; border-radius: 5px;">
        <h2 style="color: #4a90e2;">Verify Your Email Address</h2>
        <p>Hello ' . htmlspecialchars($firstname) . ',</p>
        <p>Thank you for signing up! Please use the verification code below to complete your registration:</p>
        <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;">
        ' . $verification_code . '
        </div>
        <p>This code will expire in 1 hour.</p>
        <p>If you did not create an account, you can safely ignore this email.</p>
        <p>Best regards,<br>FATIMA HOME WORLD CENTER Team</p>
        </div>
        </body>
        </html>
        ';
        $mail->AltBody = 'Hello ' . $firstname . ', Your verification code is: ' . $verification_code . '. This code will expire in 1 hour.';
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

// Generate verification code
function generateVerificationCode($length = 6) {
    return substr(str_shuffle("0123456789"), 0, $length);
}

$errors = array();
$success = false;

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $connection = connectDB();
    
    // Sanitize and validate input
    $firstname = mysqli_real_escape_string($connection, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($connection, trim($_POST['lastname']));
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    // Validation
    if (empty($firstname)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastname)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = md5($password);
        
        // Generate verification code
        $verification_code = generateVerificationCode();
        $verification_expiry = date('Y-m-d H:i:s', strtotime('+2 day'));
        
        // Insert user data
        $query = "INSERT INTO users (firstname, lastname, email, password, is_verified, verification_code, verification_expiry, created_at) 
                  VALUES ('$firstname', '$lastname', '$email', '$hashed_password', 0, '$verification_code', '$verification_expiry', NOW())";
        
        
        if (mysqli_query($connection, $query)) {
            $_SESSION['email'] = $email;
            // Send verification email
            if (sendVerificationEmail($email, $firstname, $verification_code)) {
                header("Location: verification.php");
                $success = true;
                
                // Clear any existing output buffer
                ob_end_clean();
                
                // Redirect to verification page
                exit();
            } else {
                $errors[] = "Failed to send verification email. Please try again.";
            }
        } else {
            $errors[] = "Registration failed: " . mysqli_error($connection);
        }
    }
    
    mysqli_close($connection);
}

// If redirection didn't work, try JavaScript as fallback
if ($success) {
    echo "<script>window.location.href = 'verification.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            border-color: #4a90e2;
            outline: none;
        }
        
        button {
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 14px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #357abD;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .error-list {
            background-color: #fdeaea;
            border-left: 4px solid #e74c3c;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .error-list ul {
            margin-left: 20px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            border-radius: 3px;
            background-color: #eee;
            position: relative;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s, background-color 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create an Account</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form id="signup-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo isset($firstname) ? $firstname : ''; ?>">
                <div class="error-message" id="firstname-error"></div>
            </div>
            
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo isset($lastname) ? $lastname : ''; ?>">
                <div class="error-message" id="lastname-error"></div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>">
                <div class="error-message" id="email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <div class="strength-text" id="strength-text"></div>
                <div class="error-message" id="password-error"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <div class="error-message" id="confirm-password-error"></div>
            </div>
            
            <button type="submit" id="submit-btn">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signup-form');
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const strengthMeter = document.getElementById('strength-meter');
            const strengthText = document.getElementById('strength-text');
            
            // Update password strength meter
            password.addEventListener('input', function() {
                const value = password.value;
                let strength = 0;
                
                if (value.length >= 8) strength += 1;
                if (value.match(/[A-Z]/)) strength += 1;
                if (value.match(/[0-9]/)) strength += 1;
                if (value.match(/[^A-Za-z0-9]/)) strength += 1;
                
                switch(strength) {
                    case 0:
                        strengthMeter.style.width = '0%';
                        strengthMeter.style.backgroundColor = '';
                        strengthText.textContent = '';
                        break;
                    case 1:
                        strengthMeter.style.width = '25%';
                        strengthMeter.style.backgroundColor = '#e74c3c';
                        strengthText.textContent = 'Weak';
                        strengthText.style.color = '#e74c3c';
                        break;
                    case 2:
                        strengthMeter.style.width = '50%';
                        strengthMeter.style.backgroundColor = '#f39c12';
                        strengthText.textContent = 'Moderate';
                        strengthText.style.color = '#f39c12';
                        break;
                    case 3:
                        strengthMeter.style.width = '75%';
                        strengthMeter.style.backgroundColor = '#3498db';
                        strengthText.textContent = 'Good';
                        strengthText.style.color = '#3498db';
                        break;
                    case 4:
                        strengthMeter.style.width = '100%';
                        strengthMeter.style.backgroundColor = '#2ecc71';
                        strengthText.textContent = 'Strong';
                        strengthText.style.color = '#2ecc71';
                        break;
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                });
                
                // Validate first name
                if (firstname.value.trim() === '') {
                    document.getElementById('firstname-error').textContent = 'First name is required';
                    isValid = false;
                }
                
                // Validate last name
                if (lastname.value.trim() === '') {
                    document.getElementById('lastname-error').textContent = 'Last name is required';
                    isValid = false;
                }
                
                // Validate email
                if (email.value.trim() === '') {
                    document.getElementById('email-error').textContent = 'Email is required';
                    isValid = false;
                } else if (!/^\S+@\S+\.\S+$/.test(email.value)) {
                    document.getElementById('email-error').textContent = 'Invalid email format';
                    isValid = false;
                }
                
                // Validate password
                if (password.value === '') {
                    document.getElementById('password-error').textContent = 'Password is required';
                    isValid = false;
                } else if (password.value.length < 8) {
                    document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
                    isValid = false;
                }
                
                // Validate password confirmation
                if (confirmPassword.value === '') {
                    document.getElementById('confirm-password-error').textContent = 'Please confirm your password';
                    isValid = false;
                } else if (password.value !== confirmPassword.value) {
                    document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

