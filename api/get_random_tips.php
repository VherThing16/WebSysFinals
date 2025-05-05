<?php
require_once 'config.php';
require_once 'tip_functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'consumption';
$tips = [];

if ($type === 'consumption') {
    $tips = getRandomConsumptionTips();
} elseif ($type === 'production') {
    $tips = getRandomProductionTips();
}

echo json_encode(['tips' => $tips]);
?>