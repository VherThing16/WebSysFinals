<?php
require 'sendEmail.php';

// Test with different configurations
$configs = [
    ['port' => 587, 'encryption' => PHPMailer::ENCRYPTION_STARTTLS],
    ['port' => 465, 'encryption' => PHPMailer::ENCRYPTION_SMTPS]
];

foreach ($configs as $config) {
    echo "<h2>Trying Port {$config['port']} with {$config['encryption']}...</h2>";
    ob_start();
    $result = sendEmail(
        'your-email@example.com',
        'Test from Port '.$config['port'],
        'Testing email delivery'
    );
    $output = ob_get_clean();
    echo $output;
    echo "<hr>";
}
?>