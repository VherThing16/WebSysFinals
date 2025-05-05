<?php
require 'sendEmail.php';

// Simple test
$result = sendEmail(
    'responsabili00@gmail.com', // CHANGE TO YOUR EMAIL
    'SMTP Test', 
    '<h1>Testing SMTP</h1><p>This is a test email from RESPONSABILI.</p>'
);

echo $result ? "Email sent successfully!" : "Failed to send email";
?>