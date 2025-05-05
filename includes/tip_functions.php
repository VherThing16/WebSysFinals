<?php
require_once 'config.php';

function getRandomConsumptionTips($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT tip_text FROM consumption_tips ORDER BY RAND() LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getRandomProductionTips($limit = 5) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT tip_text FROM production_tips ORDER BY RAND() LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function likeTip($userId, $tipText) {
    global $pdo;
    
    // Check if already liked
    $stmt = $pdo->prepare("SELECT id FROM liked_tips WHERE user_id = ? AND tip_text = ?");
    $stmt->execute([$userId, $tipText]);
    
    if ($stmt->rowCount() > 0) {
        return false; // Already liked
    }
    
    // Add to liked tips
    $stmt = $pdo->prepare("INSERT INTO liked_tips (user_id, tip_text) VALUES (?, ?)");
    $stmt->execute([$userId, $tipText]);
    return true;
}

function unlikeTip($userId, $tipText) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM liked_tips WHERE user_id = ? AND tip_text = ?");
    return $stmt->execute([$userId, $tipText]);
}

function getLikedTips($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT tip_text FROM liked_tips WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function isTipLiked($userId, $tipText) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM liked_tips WHERE user_id = ? AND tip_text = ?");
    $stmt->execute([$userId, $tipText]);
    return $stmt->rowCount() > 0;
}

function getLikedTipsCount($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM liked_tips WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}
?>