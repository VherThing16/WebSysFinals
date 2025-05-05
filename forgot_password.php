<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            // Send email using PHPMailer
            require_once 'sendEmail.php';
            $resetLink = (isset($_SERVER['HTTPS']) ? "https" : "http") .
                 "://" . $_SERVER['HTTP_HOST'] .
                 dirname($_SERVER['PHP_SELF']) .
                 "/reset_password.php?token=$token";
            
            // Email template with text instead of logo
            $subject = "Password Reset Request - RESPONSABILI";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; background:#f5f5f5; padding:20px;'>
                    <div style='max-width:600px; margin:0 auto; padding:30px; background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
                        <div style='text-align:center; margin-bottom:20px;'>
                            <h1 style='color:rgb(19, 68, 8); margin:0;'>RESPONSABILI</h1>
                            <p style='color:#666; margin-top:5px;'>Sustainable Consumption Platform</p>
                        </div>
                        <h3 style='color:rgb(19, 68, 8); text-align:center;'>Password Reset Request</h3>
                        <p style='font-size:16px;'>Hello,</p>
                        <p style='font-size:16px;'>You requested a password reset. Click the button below to reset your password:</p>
                        
                        <div style='text-align:center; margin:25px 0;'>
                            <a href='{$resetLink}' style='
                                display: inline-block;
                                padding: 12px 30px;
                                background-color:rgb(19, 68, 8);
                                color: white;
                                text-decoration: none;
                                border-radius: 4px;
                                font-weight: bold;
                                font-size:16px;
                            '>Reset Password</a>
                        </div>
                        
                        <p style='font-size:14px; color:#666;'>
                            <strong>Note:</strong> This link will expire in 1 hour. If you didn't request this, please ignore this email.
                        </p>
                        
                        <div style='margin-top:30px; padding-top:20px; border-top:1px solid #eee; font-size:12px; color:#999; text-align:center;'>
                            RESPONSABILI App
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            if (sendEmail($email, $subject, $message)) {
                $success = 'Password reset link has been sent to your email';
            } else {
                $error = 'Failed to send reset email';
            }
        } else {
            $error = 'If this email exists, a reset link has been sent';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | RESPONSABILI</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="overlay">
            <div class="login-box">
                <div class="icon">
                    <img src="assets/images/store.png" alt="Logo">
                </div>
                <h1 class="title">RESPONSABILI</h1>
                <h2 class="min-title">Forgot Password</h2>

                <?php if ($error): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="forgot_password.php">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit">Send Reset Link</button>
                </form>

                <p class="signup-text">
                    Remember your password? <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>