<?php

require_once './admin/database/connection.php';
session_start();
$errors = array();
$success = false;

// Check if email exists in session
if (!isset($_SESSION['email'])) {
    header("Location: signup.php");
    exit();
}

$email = $_SESSION['email'];

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get verification code from form
    $verification_code = mysqli_real_escape_string($connection, trim($_POST['verification_code']));
    
    // Validation
    if (empty($verification_code)) {
        $errors[] = "Verification code is required";
    } else {
        // Check if verification code is valid
        $query = "SELECT * FROM users WHERE email = '$email' AND verification_code = '$verification_code' AND verification_expiry > NOW() AND is_verified = 0";
        $result = mysqli_query($connection, $query);
        
        if (mysqli_num_rows($result) > 0) {
            // Update user as verified
            $update_query = "UPDATE users SET is_verified = 1, verification_code = NULL, verification_expiry = NULL WHERE email = '$email'";
            
            if (mysqli_query($connection, $update_query)) {
                $success = true;
                // Clear email session
                unset($_SESSION['email']);
            } else {
                $errors[] = "Verification failed: " . mysqli_error($connection);
            }
        } else {
            // Check if code is expired
            $check_expiry = "SELECT * FROM users WHERE email = '$email' AND verification_code = '$verification_code' AND verification_expiry <= NOW()";
            $expiry_result = mysqli_query($connection, $check_expiry);
            
            if (mysqli_num_rows($expiry_result) > 0) {
                $errors[] = "Verification code has expired. Please request a new one.";
            } else {
                $errors[] = "Invalid verification code. Please try again.";
            }
        }
    }
    
    mysqli_close($connection);
}

// Resend verification code
if (isset($_GET['resend']) && $_GET['resend'] == 'true') {
    $connection = connectDB();
    
    // Generate new verification code
    $verification_code = generateVerificationCode();
    $verification_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Get firstname for email
    $query = "SELECT firstname FROM users WHERE email = '$email'";
    $result = mysqli_query($connection, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $firstname = $row['firstname'];
        
        // Update verification code
        $update_query = "UPDATE users SET verification_code = '$verification_code', verification_expiry = '$verification_expiry' WHERE email = '$email'";
        
        if (mysqli_query($connection, $update_query)) {
            // Send verification email
            if (sendVerificationEmail($email, $firstname, $verification_code)) {
                $_SESSION['resend_success'] = true;
            } else {
                $errors[] = "Failed to send verification email. Please try again.";
            }
        } else {
            $errors[] = "Failed to generate new verification code: " . mysqli_error($connection);
        }
    } else {
        $errors[] = "Email not found.";
    }
    
    mysqli_close($connection);
    
    // Redirect to avoid form resubmission
    header("Location: verify.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
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
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .verification-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .verification-input input {
            width: 50px;
            height: 60px;
            font-size: 24px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .verification-input input:focus {
            border-color: #4a90e2;
            outline: none;
        }
        
        button {
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #357abD;
        }
        
        .success-message {
            background-color: #e7f7ef;
            border-left: 4px solid #2ecc71;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #27ae60;
        }
        
        .error-message {
            background-color: #fdeaea;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #c0392b;
        }
        
        .resend-link {
            color: #4a90e2;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
        
        .hidden-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success-message">
                <h2>Email Verified!</h2>
                <p>Your email has been successfully verified. You can now log in to your account.</p>
                <a href="login.php"><button>Log In</button></a>
            </div>
        <?php else: ?>
            <h1>Verify Your Email</h1>
            <p>We've sent a 6-digit verification code to <strong><?php echo htmlspecialchars($email); ?></strong>. Enter the code below to confirm your email address.</p>
            
            <?php if (isset($_SESSION['resend_success'])): ?>
                <div class="success-message">
                    A new verification code has been sent to your email.
                </div>
                <?php unset($_SESSION['resend_success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <div class="verification-input">
                        <input type="text" maxlength="1" class="code-input" data-index="0" autofocus>
                        <input type="text" maxlength="1" class="code-input" data-index="1">
                        <input type="text" maxlength="1" class="code-input" data-index="2">
                        <input type="text" maxlength="1" class="code-input" data-index="3">
                        <input type="text" maxlength="1" class="code-input" data-index="4">
                        <input type="text" maxlength="1" class="code-input" data-index="5">
                    </div>
                    <input type="hidden" name="verification_code" id="verification_code">
                </div>
                
                <button type="submit" id="verify-btn">Verify Email</button>
            </form>
            
            <p>Didn't receive the code? <a href="verify.php?resend=true" class="resend-link">Resend Code</a></p>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const codeInputs = document.querySelectorAll('.code-input');
            const verificationCodeInput = document.getElementById('verification_code');
            const form = document.querySelector('form');
            
            // Focus next input on input
            codeInputs.forEach(input => {
                input.addEventListener('input', function(e) {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    const index = parseInt(this.dataset.index);
                    
                    // Move to next input if value is entered and not last input
                    if (this.value && index < codeInputs.length - 1) {
                        codeInputs[index + 1].focus();
                    }
                    
                    // Update hidden input with full code
                    updateVerificationCode();
                });
                
                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    const index = parseInt(this.dataset.index);
                    
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        codeInputs[index - 1].focus();
                        codeInputs[index - 1].value = '';
                        updateVerificationCode();
                    }
                });
            });
            
            // Update hidden input with verification code
            function updateVerificationCode() {
                let code = '';
                codeInputs.forEach(input => {
                    code += input.value;
                });
                verificationCodeInput.value = code;
            }
            
            // Submit form when all inputs are filled
            codeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    let allFilled = true;
                    codeInputs.forEach(input => {
                        if (!input.value) {
                            allFilled = false;
                        }
                    });
                    
                    if (allFilled) {
                        // Slight delay to ensure code is populated
                        setTimeout(() => {
                            form.submit();
                        }, 300);
                    }
                });
            });
        });
    </script>
</body>
</html>
