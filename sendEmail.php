<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'responsabili00@gmail.com'; // Replace with your email
        $mail->Password   = 'wulrhispjkftfpqj';          // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 15;

        // Recipients
        $mail->setFrom('no-reply@responsabili.com', 'RESPONSABILI');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log errors instead of displaying them
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Optional: Test email sending when directly accessing this script
if (basename($_SERVER['SCRIPT_NAME']) === 'sendEmail.php') {
    header('Content-Type: text/html');

    $to = 'responsabili00@gmail.com'; // Change this to your testing email
    $subject = 'SMTP Test Email';
    $token = bin2hex(random_bytes(32));
    $resetLink = sprintf(
        '%s://%s%sreset_password.php?token=%s',
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http',
        $_SERVER['HTTP_HOST'],
        rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/',
        $token
    );
    $body = "
        <h1>Password Reset</h1>
        <p>Click the link below to reset your password:</p>
        <a href=\"$resetLink\">$resetLink</a>
        <p>This link will expire in 1 hour.</p>
    ";

    if (sendEmail($to, $subject, $body)) {
        echo "<p style='color:green'>Test email sent successfully!</p>";
    } else {
        echo "<p style='color:red'>Failed to send test email.</p>";
    }
    exit;
}
?>
