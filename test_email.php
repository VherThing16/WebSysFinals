<?php
require 'sendEmail.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Attempting to send email...<br>";

if (sendEmail(
    'responsabili00@gmail.com', // Replace with your email
    'Test Email', 
    '<h1>It works!</h1><p>This is a test email.</p>'
)) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email. Check error logs.";
}
?>