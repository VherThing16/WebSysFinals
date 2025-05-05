<?php
require_once __DIR__ . '/includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = getUserId();

// Fetch consumption tips with like counts using prepared statements
$stmt = $pdo->prepare("
    SELECT ct.*, 
           COUNT(lt.id) AS like_count,
           EXISTS(SELECT 1 FROM liked_tips WHERE user_id = ? AND tip_id = ct.id AND tip_type = 'consumption') AS is_liked
    FROM consumption_tips ct
    LEFT JOIN liked_tips lt ON ct.id = lt.tip_id AND lt.tip_type = 'consumption'
    GROUP BY ct.id
    ORDER BY like_count DESC
");
$stmt->execute([$userId]);
$tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dynamicContent = [
    "Small changes in your consumption habits can make a big difference.",
    "Being a conscious consumer helps protect our planet's resources.",
    "Every purchase you make is a vote for the kind of world you want.",
    "Sustainable consumption starts with mindful shopping decisions."
];
$randomContent = $dynamicContent[array_rand($dynamicContent)];

// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tipId = (int)$_POST['tip_id'];
    
    try {
        $pdo->beginTransaction();
        
        if ($_POST['action'] === 'like') {
            // First get the tip text
            $stmt = $pdo->prepare("SELECT tip_text FROM consumption_tips WHERE id = ?");
            $stmt->execute([$tipId]);
            $tip = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tip) {
                $stmt = $pdo->prepare("
                    INSERT INTO liked_tips (user_id, tip_id, tip_text, tip_type)
                    VALUES (?, ?, ?, 'consumption')
                ");
                $stmt->execute([$userId, $tipId, $tip['tip_text']]);
            }
        } elseif ($_POST['action'] === 'unlike') {
            $stmt = $pdo->prepare("
                DELETE FROM liked_tips 
                WHERE user_id = ? AND tip_id = ? AND tip_type = 'consumption'
            ");
            $stmt->execute([$userId, $tipId]);
        }
        
        $pdo->commit();
        
        // Return to avoid rendering the page
        header("Location: consumer.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Handle error
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsible Consumption Tips | RESPONSABILI</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .tip-likes {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .tip-likes img {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .tip-item {
            transition: background-color 0.3s;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tip-item:hover {
            background-color: #f5f5f5;
        }
        .tip-content {
            flex-grow: 1;
            cursor: pointer;
        }
        .tips-intro {
            margin-bottom: 20px;
            font-size: 16px;
            color: #555;
        }
        form.like-form {
            display: inline;
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
            <div class="section-heading">
                <img src="assets/images/bulb.png" alt="Bulb Icon">
                <h2>Tips for Responsible Consumption</h2>
            </div>
            <div class="tips-section">
                <p class="tips-intro">Practical ways to make your daily consumption more sustainable:</p>

                <ul id="tipsList">
                    <?php foreach ($tips as $tip): ?>
                    <li class="tip-item">
                        <div class="tip-content" onclick="window.location.href='tip-detail.php?id=<?= $tip['id'] ?>&type=consumption'">
                            <h3><?= htmlspecialchars($tip['tip_text']) ?></h3>
                        </div>
                        <div class="tip-likes">
                            <form method="post" class="like-form">
                                <input type="hidden" name="tip_id" value="<?= $tip['id'] ?>">
                                <button type="submit" name="action" value="<?= $tip['is_liked'] ? 'unlike' : 'like' ?>">
                                    <img src="assets/images/<?= $tip['is_liked'] ? 'full-heart' : 'heart' ?>.png" alt="Like">
                                </button>
                                <span><?= $tip['like_count'] ?></span>
                            </form>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <p id="dynamicContent"><?= htmlspecialchars($randomContent) ?></p>
            </div>

            <button class="randomize-button" id="randomizeButton">
                <img src="assets/images/load.png" alt="Shuffle Tips">
            </button>
        </div>
    </div>
    
    <script>
        // Shuffle functionality
        document.getElementById('randomizeButton').addEventListener('click', function() {
            const tipsList = document.getElementById('tipsList');
            const tips = Array.from(tipsList.children);
            
            for (let i = tips.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [tips[i], tips[j]] = [tips[j], tips[i]];
            }
            
            tips.forEach(tip => tipsList.appendChild(tip));
        });
    </script>
</body>
</html>