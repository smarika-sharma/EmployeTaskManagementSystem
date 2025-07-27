<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-employee.php");
    exit();
}

require_once '../database.php';

try {
    $db = new DatabaseConnection();
    
    // Get user data
    $user_data = $db->select("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    
    if (empty($user_data)) {
        header("Location: login-employee.php");
        exit();
    }
    
    $user = $user_data[0];
    
    // Handle form submission for updating department and role
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $department = trim($_POST['department']);
        $role = trim($_POST['role']);
        
        if (!empty($department) && !empty($role)) {
            $db->update("UPDATE users SET department = ?, role = ? WHERE id = ?", 
                       [$department, $role, $_SESSION['user_id']]);
            
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: employee-profile.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Department and role cannot be empty.";
        }
    }
    
    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $current_user = $db->select("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        if (password_verify($current_password, $current_user[0]['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->update("UPDATE users SET password = ? WHERE id = ?", 
                               [$hashed_password, $_SESSION['user_id']]);
                    
                    $_SESSION['success_message'] = "Password changed successfully!";
                    header("Location: employee-profile.php");
                    exit();
                } else {
                    $_SESSION['error_message'] = "New password must be at least 6 characters long.";
                }
            } else {
                $_SESSION['error_message'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['error_message'] = "Current password is incorrect.";
        }
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link rel="stylesheet" href="employee-profile.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo-circle">
          <span class="logo-icon">◉</span>
        </div>
        <div>
          <div class="sidebar-title">ETMS</div>
          <div class="sidebar-subtitle">Employee Dashboard</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="employee-dashboard.php" class="custom-link"><span class="nav-icon">▣</span> Dashboard</a></li>
          <li><a href="employee-task.php" class="custom-link"><span class="nav-icon">●</span> My Tasks</a></li>
          <li class="active"><a href="employee-profile.php" class="custom-link"><span class="nav-icon">👤</span> Profile</a></li>
        </ul>
      </nav>
      <div class="sidebar-logout" id="logoutBtn">
        <span class="logout-icon">⎋</span> Logout
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <h1>Profile</h1>
      </header>
      
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-popup" id="successPopup">
          <span class="popup-icon">✅</span>
          <?php echo $_SESSION['success_message']; ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-popup" id="errorPopup">
          <span class="popup-icon">❌</span>
          <?php echo $_SESSION['error_message']; ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>
      
      <section class="profile-section">
        <div class="profile-info">
          <div class="profile-card">
            <div class="profile-card-header">
              <span>Personal Information</span>
              <button type="button" id="editProfileBtn" class="btn btn-outline"><span class="edit-icon">✏️</span> Edit Profile</button>
            </div>
            <form class="profile-form" method="POST">
              <label>Full Name
                <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>
              </label>
              <label>Email Address
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
              </label>
              <label>Employee ID
                <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" disabled>
              </label>
              <label>Department
                <input type="text" name="department" value="<?php echo htmlspecialchars($user['department'] ?? 'Engineering'); ?>" disabled style="color: #6b7280; background: #f4f6fa;">
              </label>
              <label>Role
                <input type="text" name="role" value="<?php echo htmlspecialchars($user['role'] ?? 'Developer'); ?>" disabled style="color: #6b7280; background: #f4f6fa;">
              </label>
              <div class="profile-form-actions" style="display: none; gap: 24px; margin-top: 20px;">
                <button type="submit" name="update_profile" class="btn btn-primary" style="background: #2563eb !important; color: #fff !important; border: 2px solid #2563eb !important; padding: 12px 24px; font-size: 14px; font-weight: 600;">Save Changes</button>
                <button type="button" id="cancelEditBtn" class="btn btn-outline" style="padding: 12px 24px; font-size: 14px; font-weight: 600;">Cancel</button>
              </div>
            </form>
          </div>
          <div class="profile-card profile-summary">
            <div class="profile-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 2)); ?></div>
            <div class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
            <div class="profile-role"><?php echo htmlspecialchars($user['role'] ?? 'Developer'); ?> • <?php echo htmlspecialchars($user['department'] ?? 'Engineering'); ?></div>
            <div class="profile-status"><span class="status-dot"></span></div>
          </div>
        </div>
        <div class="profile-card password-card">
          <div class="password-header">Password Management</div>
          <div class="password-desc">Change your password to keep your account secure.</div>
          <button type="button" id="changePasswordBtn" class="btn btn-outline btn-password"><span class="lock-icon">🔒</span> Change Password</button>
          <form class="password-form" method="POST" style="display:none; margin-top: 16px;">
            <input type="password" name="current_password" placeholder="Current Password" required style="margin-bottom: 8px; width: 100%; padding: 10px; border-radius: 6px; border: 1.5px solid #e5e7eb; font-size: 15px;">
            <input type="password" name="new_password" placeholder="New Password" required style="margin-bottom: 8px; width: 100%; padding: 10px; border-radius: 6px; border: 1.5px solid #e5e7eb; font-size: 15px;">
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required style="margin-bottom: 8px; width: 100%; padding: 10px; border-radius: 6px; border: 1.5px solid #e5e7eb; font-size: 15px;">
            <div class="password-form-actions" style="display: flex; gap: 24px; margin-top: 20px;">
              <button type="submit" name="change_password" class="btn btn-primary" style="background: #2563eb !important; color: #fff !important; border: 2px solid #2563eb !important; padding: 12px 24px; font-size: 14px; font-weight: 600;">Save Password</button>
              <button type="button" id="cancelPasswordBtn" class="btn btn-outline" style="padding: 12px 24px; font-size: 14px; font-weight: 600;">Cancel</button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
<script>
  // Edit Profile functionality
  const editBtn = document.getElementById('editProfileBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const form = document.querySelector('.profile-form');
  const departmentInput = form.querySelector('input[name="department"]');
  const roleInput = form.querySelector('input[name="role"]');
  const actionsDiv = form.querySelector('.profile-form-actions');
  let editing = false;

  editBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (!editing) {
      departmentInput.disabled = false;
      roleInput.disabled = false;
      // Apply darker text styling when fields become editable
      departmentInput.style.color = '#374151';
      departmentInput.style.background = '#fff';
      roleInput.style.color = '#374151';
      roleInput.style.background = '#fff';
      actionsDiv.style.display = 'flex';
      editBtn.style.display = 'none';
      editing = true;
    }
  });

  cancelEditBtn.addEventListener('click', function(e) {
    e.preventDefault();
    departmentInput.disabled = true;
    roleInput.disabled = true;
    // Reset to disabled styling
    departmentInput.style.color = '#6b7280';
    departmentInput.style.background = '#f4f6fa';
    roleInput.style.color = '#6b7280';
    roleInput.style.background = '#f4f6fa';
    actionsDiv.style.display = 'none';
    editBtn.style.display = 'inline-block';
    editing = false;
  });

  // Change Password functionality
  const changePasswordBtn = document.getElementById('changePasswordBtn');
  const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
  const passwordForm = document.querySelector('.password-form');
  let changingPassword = false;

  changePasswordBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (!changingPassword) {
      passwordForm.style.display = 'block';
      changePasswordBtn.style.display = 'none';
      // Ensure the form actions are properly styled
      const passwordActions = passwordForm.querySelector('.password-form-actions');
      passwordActions.style.display = 'flex';
      passwordActions.style.gap = '24px';
      passwordActions.style.marginTop = '20px';
      changingPassword = true;
    }
  });

  cancelPasswordBtn.addEventListener('click', function(e) {
    e.preventDefault();
    passwordForm.style.display = 'none';
    changePasswordBtn.style.display = 'inline-block';
    changingPassword = false;
    // Clear form
    passwordForm.reset();
  });

  // Logout functionality
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'index.html';
    }
  });

  // Auto-hide popup messages
  setTimeout(function() {
    const successPopup = document.getElementById('successPopup');
    const errorPopup = document.getElementById('errorPopup');
    
    if (successPopup) {
      successPopup.style.display = 'none';
    }
    if (errorPopup) {
      errorPopup.style.display = 'none';
    }
  }, 5000);

  // Make nav li clickable
  document.querySelectorAll('.sidebar-nav ul li').forEach(function(li) {
    const link = li.querySelector('a');
    if (link) {
      li.style.cursor = 'pointer';
      li.addEventListener('click', function(e) {
        // Prevent double navigation if clicking the link directly
        if (e.target.tagName.toLowerCase() !== 'a') {
          window.location.href = link.getAttribute('href');
        }
      });
    }
  });
</script>
</html>
