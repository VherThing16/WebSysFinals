<?php
require_once 'config.php';

/**
 * Format name with proper capitalization and trim whitespace
 */
function formatName($name) {
    $name = trim($name);
    $name = preg_replace('/\s+/', ' ', $name);
    return ucwords(strtolower($name));
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

function registerUser($firstName, $lastName, $email, $password) {
    global $pdo;
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        return "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    
    if (!validatePassword($password)) {
        return "Password must be at least 8 characters with uppercase, lowercase, and number";
    }
    
    // Format and validate names
    $firstName = formatName($firstName);
    $lastName = formatName($lastName);
    
    if (!preg_match('/^[a-zA-Z\s\-]+$/', $firstName)) {
        return "First name contains invalid characters";
    }
    
    if (!preg_match('/^[a-zA-Z\s\-]+$/', $lastName)) {
        return "Last name contains invalid characters";
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        return "Email already exists";
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Insert user
    try {
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
        
        // Log user in automatically
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        return true;
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return "Registration failed. Please try again.";
    }
}

function loginUser($email, $password) {
    global $pdo;
    
    // Validate input
    if (empty($email) || empty($password)) {
        return "Email and password are required";
    }
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        return "Invalid email or password";
    }
    
    // Check if password needs rehashing
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $user['id']]);
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    
    return true;
}

function logoutUser() {
    session_unset();
    session_destroy();
}

function getUserProfile($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUserProfile($userId, $firstName, $lastName) {
    global $pdo;
    
    // Format and validate names
    $firstName = formatName($firstName);
    $lastName = formatName($lastName);
    
    if (!preg_match('/^[a-zA-Z\s\-]+$/', $firstName)) {
        return "First name contains invalid characters";
    }
    
    if (!preg_match('/^[a-zA-Z\s\-]+$/', $lastName)) {
        return "Last name contains invalid characters";
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $userId]);
        
        // Update session name if updating current user
        if ($userId == ($_SESSION['user_id'] ?? null)) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        return "Profile update failed. Please try again.";
    }
}
?>