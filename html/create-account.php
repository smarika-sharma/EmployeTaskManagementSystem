<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php'; // Adjust the path as needed

// Start session to store messages (optional)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and trim whitespace
    $full_name = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Simple validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Create database connection
        $db = new DatabaseConnection();

        // Check if email already exists
        $existing = $db->select("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $error = "Email already registered.";
        } else {
            // Insert user into database
            $query = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $params = [$full_name, $email, $hashed_password];
            $user_id = $db->create($query, $params);

            if ($user_id) {
                // Success! Redirect or show a message
                $_SESSION['success'] = "Account created successfully! Please log in.";
                header("Location: employee-login.html");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Employee Account</title>
  <link rel="stylesheet" href="create-account.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="account-bg">
    <div class="account-card">
      <div class="account-logo-row">
        <div class="account-logo"><span class="logo-icon">🔒</span></div>
        <div>
          <div class="account-title-main">ETMS</div>
          <div class="account-title-sub">Task Management</div>
        </div>
      </div>
      <h1 class="account-title">Create Employee Account</h1>
      <div class="account-subtitle">Join the team and start managing your tasks</div>
      
      <?php if (isset($error)): ?>
        <div class="error-message" style="background-color: #fee; color: #c33; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #fcc;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      
      <form class="account-form" method="POST" action="">
        <label>Full Name
          <div class="input-icon-row">
            <input type="text" name="full_name" placeholder="Enter your full name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
            <span class="input-icon">👤</span>
          </div>
        </label>
        <label>Email Address
          <input type="email" name="email" placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </label>
        <label>Password
          <div class="input-icon-row">
            <input type="password" name="password" placeholder="Enter your password" required>
            <span class="input-icon">👁️</span>
          </div>
          <div class="input-note">Password must be at least 8 characters with uppercase, lowercase, and number</div>
        </label>
        <label>Confirm Password
          <div class="input-icon-row">
            <input type="password" name="confirm_password" placeholder="Confirm your password" required>
            <span class="input-icon">👁️</span>
          </div>
        </label>
        <button type="submit" class="btn btn-primary"><span class="btn-check">✔️</span> Create Employee Account</button>
      </form>
      <div class="account-links">
        <a href="employee-login.html" class="forgot-link custom-link">Forgot your password?</a>
        <div class="login-link">Already have an account? <a href="employee-login.html" class="custom-link">Login here</a></div>
      </div>
    </div>
    <div class="account-footer">
      <span class="footer-icon">🔒</span> Secure employee registration portal
    </div>
    <div class="account-back">
      <a href="index.html" class="custom-link">← Back to Home</a>
    </div>
  </div>
</body>
</html> 