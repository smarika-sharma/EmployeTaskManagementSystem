<?php
session_start();
require_once '../database.php'; // Adjust path if needed

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $db = new DatabaseConnection();
        $result = $db->select("SELECT * FROM users WHERE email = ?", [$email]);
        if ($result && password_verify($password, $result[0]['password'])) {
            // Login successful, set session variables
            $_SESSION['user_id'] = $result[0]['id']; // Store user's ID from users table
            $_SESSION['user_name'] = $result[0]['full_name'] ?? 'User'; 
            
            // Redirect to dashboard
            header("Location: employee-dashboard.php");
            exit();
        } else {
            $error = "Incorrect credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Login</title>
  <link rel="stylesheet" href="employee-login.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="login-bg">
    <div class="login-card">
      <div class="login-logo-row">
        <div class="login-logo"><span class="logo-icon">👤</span></div>
        <div>
          <div class="login-title-main">ETMS</div>
          <div class="login-title-sub">Task Management</div>
        </div>
      </div>
      <h1 class="login-title">Employee Login</h1>
      <div class="login-subtitle">Access your tasks and manage your workflow</div>
      <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
      <form class="login-form" action="" method="POST">
        <label>Email Address
          <div class="input-icon-row">
            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            <span class="input-icon">@</span>
          </div>
        </label>
        <label>Password
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </label>
        <button type="submit" class="btn btn-primary"><span class="btn-icon">🔄</span> Login to Dashboard</button>
      </form>
      <div class="login-links">
        <a href="employee-login.html" class="forgot-link custom-link">Forgot your password?</a>
        <div class="create-link">New here? <a href="create-account.php" class="custom-link">Create an Account</a></div>
      </div>
    </div>
    <div class="login-footer">
      <span class="footer-icon">🔒</span> Secure employee access portal
    </div>
    <div class="login-back">
      <a href="index.html" class="custom-link">← Back to Home</a>
    </div>
  </div>
</body>

</html>