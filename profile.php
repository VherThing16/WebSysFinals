<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = getUserId();
$user = getUserProfile($userId);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    
    $result = updateUserProfile($userId, $firstName, $lastName);
    
    if ($result === true) {
        $success = "Profile updated successfully!";
        $user = getUserProfile($userId); 
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Responsabili</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=2">
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

        <div class="nav-button">
            <a href="landingpage.php">
                <img src="assets/images/home.png" alt="Home Icon">
            </a>
        </div>

        <div class="profile-content">
          <div class="profile-left">
            <h2 class="profile-title">Profile</h2>
            <div class="profile-icon">
              <img src="assets/images/acc.png" alt="User Icon">
            </div>
          </div>

          <div class="profile-right">
            <?php if ($error): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form id="profile-form" method="POST" action="profile.php">
              <div class="form-group">
                <label for="firstName">First Name</label>
                <input type="text" id="firstName" name="first_name" placeholder="First Name" 
                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
              </div>

              <div class="form-group">
                <label for="lastName">Last Name</label>
                <input type="text" id="lastName" name="last_name" placeholder="Last Name" 
                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
              </div>

              <div class="form-group">
                <label for="email">Email: <small>(Cannot be changed)</small></label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
              </div>

              <button type="submit" class="btn-1">Save</button>
            </form> 

            <form method="POST" action="logout.php">
              <button type="submit" class="btn-1 logout-btn">Logout</button>
            </form> 
          </div>
        </div>
      </div>
    </div>
</body>
</html>