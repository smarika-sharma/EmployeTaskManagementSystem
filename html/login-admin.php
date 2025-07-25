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
        $result = $db->select("SELECT * FROM admin WHERE email = ?", [$email]);
        if ($result && password_verify($password, $result[0]['password'])) {
            // Login successful, redirect to dashboard
            header("Location: admin-dashboard.html");
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
  <title>Admin Login</title>
  <link rel="stylesheet" href="admin-login.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-bg">
    <div class="login-card">
      <div class="login-logo-row">
        <div class="login-logo"><span class="logo-icon">🗝️</span></div>
        <div>
          <div class="login-title-main">ETMS</div>
          <div class="login-title-sub">Task Management</div>
        </div>
      </div>
      <h1 class="login-title">Admin Login</h1>
      <div class="login-subtitle">Access administrative dashboard and manage system settings</div>
      <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <form class="login-form" action="" method="POST">
        <label>Email or Username
          <input type="text" id="email" name="email" placeholder="Enter your email or username" required>
        </label>
        <label>Password
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </label>
        <button type="submit" class="btn btn-primary" formaction="admin-dashboard.html">Login as Admin</button>
      </form>
      <div class="login-back">
        <a href="./index.html">← Back to Home</a>
      </div>
    </div>
    <div class="login-footer">
      <span class="footer-icon">🔒</span> Secure admin access only
    </div>
  </div>
</body>
</html>
