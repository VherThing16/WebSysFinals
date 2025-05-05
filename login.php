<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // reCAPTCHA verification
    $recaptchaSecret = '6LfY7iorAAAAAI7_abmFn9DocOf6SJTrEqtlQsBC'; // Secret key you provided
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verifying the reCAPTCHA response with Google
    $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . urlencode($recaptchaSecret) . "&response=" . urlencode($recaptchaResponse));
    $responseData = json_decode($verifyResponse);

    if ($responseData->success) {
        $email = sanitizeInput($_POST['email']);
        $password = sanitizeInput($_POST['password']);
        
        // Proceed with login
        $result = loginUser($email, $password);
        
        if ($result === true) {
            header("Location: landingpage.php");
            exit();
        } else {
            $error = $result;
        }
    } else {
        $error = 'Please verify that you are not a robot.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsabili - Login</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
  <div class="container">
    <div class="overlay">
      <div class="login-box">
        <div class="icon">
          <img src="assets/images/store.png" alt="Logo">
        </div>
        <h1 class="title">RESPONSABILI</h1>

        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <input type="email" name="email" placeholder="Email" required value="<?php echo isset($email) ? $email : ''; ?>">
          <input type="password" name="password" placeholder="Password" required>

          <div class="login-options">
            <label><input type="checkbox" name="remember"> Remember me</label>
            <a href="forgot_password.php">Forgot Password?</a>
          </div>

          <!-- reCAPTCHA widget -->
          <div class="g-recaptcha" data-sitekey="6LfY7iorAAAAAKDov-JKoREG7Aut5mVpEVZyAzgl"></div>

          <button class="btn-2" type="submit">Login</button>

        </form>

        <p class="signup-text">Don't have an account? <a href="signup.php">Sign up.</a></p>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert success"><?php echo $_SESSION['message']; ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

</body>
</html>
