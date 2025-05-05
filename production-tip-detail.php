<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_GET['id']) || !isLoggedIn()) {
    header("Location: production.php");
    exit();
}

$tipId = (int)$_GET['id'];
$userId = getUserId();

// Fetch the production tip with details
$stmt = $pdo->prepare("
    SELECT pt.*, 
           COUNT(lt.id) AS like_count,
           EXISTS(SELECT 1 FROM liked_tips WHERE user_id = ? AND tip_id = ?) AS is_liked
    FROM production_tips pt
    LEFT JOIN liked_tips lt ON pt.id = lt.tip_id
    WHERE pt.id = ?
    GROUP BY pt.id
");
$stmt->execute([$userId, $tipId, $tipId]);
$tip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tip) {
    header("Location: production.php");
    exit();
}

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $pdo->beginTransaction();
            
            if ($_POST['action'] === 'like') {
                $stmt = $pdo->prepare("
                    INSERT INTO liked_tips (user_id, tip_id, tip_text)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$userId, $tipId, $tip['tip_text']]);
                $tip['is_liked'] = 1;
                $tip['like_count']++;
            } elseif ($_POST['action'] === 'unlike') {
                $stmt = $pdo->prepare("
                    DELETE FROM liked_tips 
                    WHERE user_id = ? AND tip_id = ?
                ");
                $stmt->execute([$userId, $tipId]);
                $tip['is_liked'] = 0;
                $tip['like_count']--;
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            // Error handling
        }
    }
    header("Location: production-tip-detail.php?id=$tipId");
    exit();
}

// Get related production tips
$stmt = $pdo->prepare("
    SELECT pt.* 
    FROM production_tips pt
    WHERE pt.id != ?
    ORDER BY RAND()
    LIMIT 3
");
$stmt->execute([$tipId]);
$relatedTips = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tip['tip_text']) ?> | RESPONSABILI</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .tip-detail-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .related-tips {
            margin-top: 30px;
        }
        .related-tip {
            padding: 10px;
            margin: 5px 0;
            border-left: 3px solid #4CAF50;
            background-color: #f1f1f1;
            cursor: pointer;
        }
        .related-tip:hover {
            background-color: #e8f5e9;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 15px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-overlay">
            <header class="profile-header">
                <img src="assets/images/shelf.png" alt="Background" class="profile-background">
                <div class="profile-navbar">
                    <div class="profile-logo">
                        <div class="icon">
                            <img src="assets/images/store.png" alt="Logo">
                        </div>
                        <span>RESPONSABILI</span>
                    </div>
                </div>
            </header>
            <div class="nav-buttons">    
                <div class="nav-button">
                    <a href="profile.php">
                        <img src="assets/images/acc.png" alt="User Icon">
                    </a>
                </div>
                <div class="nav-button">
                    <a href="landingpage.php">
                        <img src="assets/images/home.png" alt="Home Icon">
                    </a>
                </div>
            </div>
        </div>
        
        <div class="containers">
            <div class="tip-detail">
                <a href="production.php" class="back-button">&larr; Back to Production Tips</a>
                
                <div class="tip-header">
                    <h2><?= htmlspecialchars($tip['tip_text']) ?></h2>
                    <div class="like-container">
                        <form method="post">
                            <button type="submit" name="action" value="<?= $tip['is_liked'] ? 'unlike' : 'like' ?>" class="like-button">
                                <img src="assets/images/<?= $tip['is_liked'] ? 'full-heart' : 'heart' ?>.png" alt="Like">
                            </button>
                            <span class="like-count"><?= $tip['like_count'] ?></span>
                        </form>
                    </div>
                </div>
                
                <div class="tip-detail-content">
                    <h3>Implementation Guide</h3>
                    <p>Here's how to implement this production strategy in your business:</p>
                    <ul>
                        <li>Conduct an assessment of your current production processes</li>
                        <li>Identify areas where this tip can be most effectively applied</li>
                        <li>Develop a phased implementation plan</li>
                        <li>Train your staff on the new procedures</li>
                        <li>Monitor and measure the impact of changes</li>
                    </ul>
                    
                    <h3>Business Benefits</h3>
                    <p>Adopting this practice can lead to:</p>
                    <ul>
                        <li>Reduced operational costs</li>
                        <li>Improved regulatory compliance</li>
                        <li>Enhanced brand reputation</li>
                        <li>Increased operational efficiency</li>
                        <li>Better employee engagement</li>
                    </ul>
                </div>
                
                <div class="related-tips">
                    <h3>Related Production Tips</h3>
                    <?php foreach ($relatedTips as $relatedTip): ?>
                        <div class="related-tip" onclick="window.location='production-tip-detail.php?id=<?= $relatedTip['id'] ?>'">
                            <?= htmlspecialchars($relatedTip['tip_text']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>