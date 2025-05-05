<?php
require_once 'config.php';
require_once 'tip_functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$userId = getUserId();
$likedTips = getLikedTips($userId);

echo json_encode(['tips' => $likedTips]);
?>