<?php
require_once 'config.php';
require_once 'tip_functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

$tipText = isset($_POST['tip']) ? sanitizeInput($_POST['tip']) : null;

if (empty($tipText)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Tip text is required']);
    exit();
}

$userId = getUserId();
$result = likeTip($userId, $tipText);

if ($result) {
    echo json_encode([
        'success' => true,
        'count' => getLikedTipsCount($userId)
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>