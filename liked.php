<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = getUserId();

// Get liked tips with their type and ID for proper linking
$stmt = $pdo->prepare("
    SELECT lt.id, lt.tip_id, lt.tip_text, lt.tip_type 
    FROM liked_tips lt
    WHERE lt.user_id = ?
    ORDER BY lt.created_at DESC
");
$stmt->execute([$userId]);
$likedTips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle tip removal if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $pdo->beginTransaction();
            
            if ($_POST['action'] === 'remove' && isset($_POST['tip_id'])) {
                $stmt = $pdo->prepare("
                    DELETE FROM liked_tips 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$_POST['tip_id'], $userId]);
            } elseif ($_POST['action'] === 'clear_all') {
                $stmt = $pdo->prepare("
                    DELETE FROM liked_tips 
                    WHERE user_id = ?
                ");
                $stmt->execute([$userId]);
            }
            
            $pdo->commit();
            
            // Return JSON response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Liked Tips | RESPONSABILI</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        #likedTipsList {
            list-style-type: none;
            padding: 0;
        }
        #likedTipsList li {
            position: relative;
            padding: 12px 15px;
            margin: 8px 0;
            background-color: #f9f9f9;
            border-radius: 4px;
            transition: all 0.3s;
        }
        #likedTipsList li:hover {
            background-color: #f1f1f1;
        }
        .tip-link {
            display: block;
            color: #333;
            text-decoration: none;
            padding-right: 30px;
            cursor: pointer;
        }
        .tip-link:hover {
            color: #4CAF50;
        }
        .remove-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 20px;
            color: #ff5252;
            cursor: pointer;
            padding: 0 5px;
        }
        .clear-all-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #ff5252;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .clear-all-btn:hover {
            background-color: #ff0000;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        .empty-state a {
            color: #4CAF50;
            text-decoration: none;
        }
        .empty-state a:hover {
            text-decoration: underline;
        }
        .tip-type {
            display: inline-block;
            font-size: 12px;
            background-color: #4CAF50;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 8px;
            text-transform: capitalize;
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
            
            <div class="containers">
                <div class="section-heading">
                    <img src="assets/images/full-heart.png" alt="Full-Heart Icon">
                    <h2>Your Liked Tips</h2>
                </div>
                
                <?php if (!empty($likedTips)): ?>
                    <ul id="likedTipsList">
                        <?php foreach ($likedTips as $tip): ?>
                            <li data-id="<?= $tip['id'] ?>">
                                <a href="tip-detail.php?id=<?= $tip['tip_id'] ?>&type=<?= $tip['tip_type'] ?>" class="tip-link">
                                    <?= htmlspecialchars($tip['tip_text']) ?>
                                    <span class="tip-type"><?= $tip['tip_type'] ?></span>
                                </a>
                                <button class="remove-btn" data-tip-id="<?= $tip['id'] ?>">×</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <button class="clear-all-btn" id="clearAllBtn">Clear All Liked Tips</button>
                <?php else: ?>
                    <div id="emptyState" class="empty-state">
                        <p>You haven't liked any tips yet.</p>
                        <p>Go back to <a href="consumer.php">consumption tips</a> or <a href="production.php">production tips</a> to start saving your favorites!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likedTipsList = document.getElementById('likedTipsList');
            const clearAllBtn = document.getElementById('clearAllBtn');
            const emptyState = document.getElementById('emptyState');
            
            // Remove button functionality
            if (likedTipsList) {
                likedTipsList.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-btn')) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const tipId = e.target.getAttribute('data-tip-id');
                        
                        if (confirm('Are you sure you want to remove this tip from your favorites?')) {
                            fetch('liked.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `action=remove&tip_id=${tipId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    e.target.closest('li').remove();
                                    
                                    if (likedTipsList.children.length === 0) {
                                        emptyState.style.display = 'block';
                                        if (clearAllBtn) clearAllBtn.style.display = 'none';
                                    }
                                }
                            })
                            .catch(error => console.error('Error:', error));
                        }
                    }
                });
            }
            
            // Clear all button functionality
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove all your liked tips? This cannot be undone.')) {
                        fetch('liked.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=clear_all'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (likedTipsList) likedTipsList.innerHTML = '';
                                if (emptyState) emptyState.style.display = 'block';
                                if (clearAllBtn) clearAllBtn.style.display = 'none';
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                });
            }
        });
    </script>
</body>
</html>