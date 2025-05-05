<?php
require_once 'includes/config.php';
require_once 'includes/tip_functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

$userId = getUserId();

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("DELETE FROM liked_tips WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'count' => 0
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Failed to clear liked tips'
    ]);
}
?>