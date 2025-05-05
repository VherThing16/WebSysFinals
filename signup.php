<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        // Check reCAPTCHA response
        if (empty($_POST['g-recaptcha-response'])) {
            $error = "Please complete the reCAPTCHA verification.";
        } else {
            $recaptchaSecret = '6LfY7iorAAAAAI7_abmFn9DocOf6SJTrEqtlQsBC'; // Your new secret key
            $recaptchaResponse = $_POST['g-recaptcha-response'];

            // Prepare data for POST request
            $data = [
                'secret' => $recaptchaSecret,
                'response' => $recaptchaResponse,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];
            $context  = stream_context_create($options);
            $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
            $captchaSuccess = json_decode($verify);

            if ($captchaSuccess->success) {
                // Proceed with user registration if reCAPTCHA is successful
                $result = registerUser($firstName, $lastName, $email, $password);

                if ($result === true) {
                    $_SESSION['message'] = "Registration successful!";
                    header("Location: landingpage.php");
                    exit();
                } else {
                    $error = $result; // Handle any registration errors
                }
            } else {
                $error = "Please complete the reCAPTCHA verification.";
                error_log("reCAPTCHA failed: " . print_r($captchaSuccess, true)); // Log error for debugging
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Responsabili - Sign Up</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=2" />
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <!-- Inline alert style -->
  <style>
    .alert-error {
      font-size: 8px;
      color: #a94442;
      background-color: #f2dede;
      border: 1px solid #ebccd1;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>

  <script>
    function validatePassword() {
        const password = document.querySelector('input[name="password"]').value;
        const requirements = [
            { regex: /.{8,}/, message: "At least 8 characters" },
            { regex: /[A-Z]/, message: "At least one uppercase letter" },
            { regex: /[a-z]/, message: "At least one lowercase letter" },
            { regex: /\d/, message: "At least one number" }
        ];
        
        let isValid = true;
        const requirementsList = document.getElementById('passwordRequirements');
        
        if (requirementsList) {
            requirementsList.innerHTML = '';
            requirements.forEach(req => {
                const li = document.createElement('li');
                li.textContent = req.message;
                li.style.color = req.regex.test(password) ? 'green' : 'red';
                requirementsList.appendChild(li);
                if (!req.regex.test(password)) isValid = false;
            });
        }
        
        return isValid;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput) {
            passwordInput.addEventListener('input', validatePassword);
        }
        
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validatePassword()) {
                    e.preventDefault();
                    alert('Please ensure your password meets all requirements');
                }
            });
        }
    });
  </script>
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
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="signup.php">
          <input type="text" name="first_name" placeholder="First Name" required 
                 value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
          <input type="text" name="last_name" placeholder="Last Name" required 
                 value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
          <input type="email" name="email" placeholder="Email" required 
                 value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
          <input type="password" name="password" placeholder="Password" required>
          <input type="password" name="confirm_password" placeholder="Confirm Password" required>
          
          <div class="login-options">
            <label>
              <input type="checkbox" name="remember"> Remember me
            </label>
          </div>
          
          <!-- reCAPTCHA -->
          <div class="g-recaptcha" data-sitekey='6LfY7iorAAAAAKDov-JKoREG7Aut5mVpEVZyAzgl'></div>

          <button type="submit">Sign Up</button>
        </form>

        <p class="signup-text">
          Already have an account? <a href="login.php">Login.</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
