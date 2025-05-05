<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESPONSABILI - Responsible Consuming</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-image-container">
                    <a href="liked.php">
                        <img src="assets/images/pics3.jpg" alt="People liking products" class="feature-image">
                    </a> 
                </div>
                <div class="feature-text">
                    <h3 class="feature-title">Likes</h3>
                    <p class="feature-desc">Support ethical products and businesses with your social engagement and positive feedback.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-image-container">
                    <a href="consumer.php">
                        <img src="assets/images/pics2.webp" alt="Sustainable shopping" class="feature-image">
                    </a>
                </div>
                <div class="feature-text">
                    <h3 class="feature-title">Responsible Consuming</h3>
                    <p class="feature-desc">Make informed choices that align with your values and contribute to a healthier planet.</p>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-image-container">
                    <a href="production.php">
                        <img src="assets/images/pics1.jpg" alt="Eco-friendly production" class="feature-image">
                    </a>
                </div>
                <div class="feature-text">
                    <h3 class="feature-title">Production</h3>
                    <p class="feature-desc">Understand the environmental and social impact behind the products you use every day.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>