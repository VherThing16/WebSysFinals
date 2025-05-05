<?php
session_start();
require 'includes/config.php';

$error = '';
$success = '';

// Check for token
if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

// Validate token
$stmt = $pdo->prepare("SELECT email, reset_token_expiry FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user || strtotime($user['reset_token_expiry']) < time()) {
    die("Token is invalid or has expired.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $error = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = "Password must contain at least one number.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $stmt->execute([$hashedPassword, $token]);

        $success = "Your password has been reset. You may now <a href='login.php'>log in</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=2">
    <style>
        .password-requirements {
            text-align: left;
            margin: 10px 0;
            font-size: 14px;
            color: #666;
        }
        .requirement {
            margin: 5px 0;
        }
        .requirement.met {
            color: green;
        }
        .requirement.unmet {
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="overlay">
        <div class="login-box">
            <div class="icon">
            <img src="assets/images/store.png" alt="Logo">
            </div>
            <h1 class="title">RESPONSABILI</h1>
            <h2 class="min-title">Reset Password</h2>

            <?php if ($error): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success"><?= $success ?></div>
            <?php else: ?>
                <form method="POST" id="resetForm">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    
                    <div class="password-requirements">
                        <p>Password must meet these requirements:</p>
                        <div class="requirement" id="length-req">✓ At least 8 characters</div>
                        <div class="requirement" id="lower-req">✓ At least one lowercase letter</div>
                        <div class="requirement" id="upper-req">✓ At least one uppercase letter</div>
                        <div class="requirement" id="number-req">✓ At least one number</div>
                    </div>
                    
                    <button type="submit">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('new_password');
    const lengthReq = document.getElementById('length-req');
    const lowerReq = document.getElementById('lower-req');
    const upperReq = document.getElementById('upper-req');
    const numberReq = document.getElementById('number-req');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Check length
        if (password.length >= 8) {
            lengthReq.classList.add('met');
            lengthReq.classList.remove('unmet');
        } else {
            lengthReq.classList.add('unmet');
            lengthReq.classList.remove('met');
        }
        
        // Check lowercase
        if (/[a-z]/.test(password)) {
            lowerReq.classList.add('met');
            lowerReq.classList.remove('unmet');
        } else {
            lowerReq.classList.add('unmet');
            lowerReq.classList.remove('met');
        }
        
        // Check uppercase
        if (/[A-Z]/.test(password)) {
            upperReq.classList.add('met');
            upperReq.classList.remove('unmet');
        } else {
            upperReq.classList.add('unmet');
            upperReq.classList.remove('met');
        }
        
        // Check number
        if (/[0-9]/.test(password)) {
            numberReq.classList.add('met');
            numberReq.classList.remove('unmet');
        } else {
            numberReq.classList.add('unmet');
            numberReq.classList.remove('met');
        }
    });
});
</script>
</body>
</html>