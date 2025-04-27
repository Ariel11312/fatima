<?php
// login.php - User login system
session_start();
require_once './admin/database/connection.php'; // Make sure this file exists with DB connection details

$errors = array();
$email = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard or home page
    header("Location: ./");
    exit();
}

// Function to manually hash password for comparison
function hashPasswordForVerification($password) {
    // Use the same hashing method that was used during registration
    // This is assuming you used PASSWORD_DEFAULT in password_hash()
    // If you used a different method, adjust accordingly
    return password_hash($password, PASSWORD_DEFAULT);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establish database connection
    $connection = mysqli_connect("localhost", "root", "", "fatima_db");
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Sanitize input
    $email = mysqli_real_escape_string($connection, trim($_POST['email']));
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, proceed with login
    if (empty($errors)) {
        // Get user data
        $query = "SELECT id, firstname, lastname, email, password, is_verified,isAdmin FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $query);
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Try direct password verification first
            $passwordMatches = md5($password);
            
            // If the above doesn't work, try a custom approach - this is a fallback
            if (!$passwordMatches === $user["password"]) {
                // Option 1: Try with a manual hash comparison 
                // (assuming the password was stored with a simple hash function)
                $hashedInputPassword = md5($password); // or sha1, sha256, etc.
                $passwordMatches = ($hashedInputPassword === $user['password']);
                
                // Option 2: If the above doesn't work, try comparing directly for testing
                // WARNING: This is highly insecure and only for testing!
                if (!$passwordMatches && $password === $user['password']) {
                    $passwordMatches = true;
                    // In this case, you should update the password to a secure hash
                    $secureHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateQuery = "UPDATE users SET password = '$secureHash' WHERE id = " . $user['id'];
                    mysqli_query($connection, $updateQuery);
                }
            }
            
            // If password matches using any method
            if ($passwordMatches === $user['password']) {
                // Check if email is verified
                if ($user['is_verified'] === "1") {
                    if($user['is_verified'] === "1"){
                        // Check if user is admin
                        if ($user['isAdmin'] === "1") {
                            $_SESSION['isAdmin'] = "true";
                            header("Location: ./admin/products.php"); // Redirect to admin dashboard
                            return;
                        } else {
                            $_SESSION['isAdmin'] = false;
                        }
                    }
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['firstname'] = $user['firstname'];
                    $_SESSION['lastname'] = $user['lastname'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Set remember me cookie if checked
                    if ($remember) {
                        // Generate a unique token
                        $token = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $token);
                        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        // Store token in database
                        $update_query = "UPDATE users SET remember_token = '$token_hash', token_expiry = '$expiry' WHERE id = " . $user['id'];
                        mysqli_query($connection, $update_query);
                        
                        // Set cookie
                        setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                        setcookie('user_email', $email, time() + (86400 * 30), "/"); // 30 days
                    }
                    
                    // Redirect to dashboard or home page
                    header("Location: ./");
                    exit();
                } else {
                    // Email not verified
                    $_SESSION['email'] = $email;
                    $errors[] = "Email not verified. <a href='verification.php'>Verify now</a>";
                }
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
    
    mysqli_close($connection);
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['user_email'])) {
    $connection = mysqli_connect("localhost", "root", "", "fatima_db");
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    $token = $_COOKIE['remember_token'];
    $token_hash = hash('sha256', $token);
    $email = mysqli_real_escape_string($connection, $_COOKIE['user_email']);
    
    // Verify token
    $query = "SELECT id, firstname, lastname, email FROM users 
              WHERE email = '$email' AND remember_token = '$token_hash' 
              AND token_expiry > NOW()";
              
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        
        // Redirect to dashboard or home page
        header("Location: ./");
        exit();
    }
    
    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            max-width: 400px;
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
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4a90e2;
            outline: none;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
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
        
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        
        .forgot-password a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password a:hover {
            color: #4a90e2;
            text-decoration: underline;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .signup-link a {
            color: #4a90e2;
            text-decoration: none;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-login p {
            position: relative;
            margin-bottom: 20px;
            color: #666;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 35%;
            height: 1px;
            background-color: #ddd;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .social-button:hover {
            background-color: #e3e3e3;
        }
        
        .social-button img {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-list">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                <div class="error-message" id="email-error"></div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <div class="error-message" id="password-error"></div>
            </div>
            
            <div class="forgot-password">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
        
        <div class="social-login">
            <p>Or login with</p>
            <div class="social-buttons">
                <a href="#" class="social-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#DB4437"><path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/></svg>
                </a>
                <a href="#" class="social-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#3b5998"><path d="M20,2H4C2.9,2,2,2.9,2,4v16c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V4C22,2.9,21.1,2,20,2z M18.4,7.4H17c-0.9,0-1,0.3-1,1l0,1.3 h2.1L18,12h-1.9v7h-3.2v-7h-1.2V9.6h1.2V8.1c0-2,0.8-3.1,3.1-3.1h2.4V7.4z"/></svg>
                </a>
                <a href="#" class="social-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#1DA1F2"><path d="M22.46,6c-0.77,0.35-1.6,0.58-2.46,0.69c0.88-0.53,1.56-1.37,1.88-2.38c-0.83,0.5-1.75,0.85-2.72,1.05C18.37,4.5,17.26,4,16,4 c-2.35,0-4.27,1.92-4.27,4.29c0,0.34,0.04,0.67,0.11,0.98C8.28,9.09,5.11,7.38,3,4.79C2.63,5.42,2.42,6.16,2.42,6.94 c0,1.49,0.75,2.81,1.91,3.56c-0.71,0-1.37-0.2-1.95-0.5v0.03c0,2.08,1.48,3.82,3.44,4.21c-0.36,0.1-0.73,0.15-1.13,0.15 c-0.27,0-0.54-0.03-0.8-0.08c0.54,1.69,2.11,2.95,4,2.98c-1.46,1.16-3.31,1.84-5.33,1.84c-0.34,0-0.68-0.02-1.02-0.06 C3.44,20.29,5.7,21,8.12,21C16,21,20.33,14.46,20.33,8.79c0-0.19,0-0.37-0.01-0.56C21.22,7.78,21.9,6.96,22.46,6z"/></svg>
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Reset error messages
                document.querySelectorAll('.error-message').forEach(el => {
                    el.textContent = '';
                });
                
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
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>