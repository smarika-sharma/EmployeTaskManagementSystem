<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

$db = new DatabaseConnection();

// Get all employees for the dropdown
$employees = $db->select("SELECT id, full_name FROM users ORDER BY full_name");

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_title = trim($_POST['task_title']);
    $task_detail = trim($_POST['task_detail']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $assigned_to = $_POST['assigned_to']; // This will be the user_id
    
    if (empty($task_title) || empty($due_date) || empty($assigned_to)) {
        $error_message = "Task title, due date, and assigned employee are required.";
    } else {
        // Get the next available ID
        $max_id_result = $db->select("SELECT MAX(id) as max_id FROM task");
        $next_id = ($max_id_result[0]['max_id'] ?? 0) + 1;
        
        $result = $db->create("INSERT INTO task (id, task_title, task_detail, due_date, status, priority, user_id, created_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", 
                             [$next_id, $task_title, $task_detail, $due_date, $status, $priority, $assigned_to]);
        
        if ($result > 0) {
            // Store success message in session and redirect
            $_SESSION['success_message'] = "Task created successfully!";
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $error_message = "Failed to create task. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Task</title>
  <link rel="stylesheet" href="new-task.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    .message {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: slideIn 0.3s ease-out;
      max-width: 300px;
    }
    .message.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(100%); opacity: 0; }
    }
  </style>
</head>
<body>
  <div class="task-bg">
    <form class="task-card" action="new-task.php" method="POST">
      <div style="text-align:left; margin-bottom: 8px;">
        <a href="admin-dashboard.php" class="custom-link">← Back to Dashboard</a>
      </div>
      <h1 class="task-title">Add New Task</h1>
      
      <?php if (isset($error_message)): ?>
        <div class="message error" id="errorPopup"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      
      <div class="task-form-grid">
        <div class="form-group">
          <label>Task Title *</label>
          <input type="text" name="task_title" placeholder="Enter task title" value="<?php echo isset($_POST['task_title']) ? htmlspecialchars($_POST['task_title']) : ''; ?>" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="Pending" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="In Progress" <?php echo (isset($_POST['status']) && $_POST['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
            <option value="Completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
          </select>
        </div>
        <div class="form-group form-group-full">
          <label>Description</label>
          <textarea name="task_detail" placeholder="Enter task description"><?php echo isset($_POST['task_detail']) ? htmlspecialchars($_POST['task_detail']) : ''; ?></textarea>
        </div>
        <div class="form-group">
          <label>Priority</label>
          <select name="priority">
            <option value="Medium" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
            <option value="Low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
          </select>
        </div>
        <div class="form-group">
          <label>Deadline *</label>
          <input type="date" name="due_date" value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>" required>
        </div>
        <div class="form-group form-group-full">
          <label>Assigned To *</label>
          <select name="assigned_to" required>
            <option value="">Select employee</option>
            <?php foreach ($employees as $employee): ?>
              <option value="<?php echo $employee['id']; ?>" <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $employee['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($employee['full_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="task-actions-row">
        <button type="button" class="btn btn-cancel" id="cancelBtn">Cancel</button>
        <button type="submit" class="btn btn-primary">+ Create Task</button>
      </div>
      <div class="task-required-note">* Required fields</div>
    </form>
  </div>
</body>
<script>
  document.getElementById('cancelBtn').addEventListener('click', function() {
    window.location.href = 'admin-dashboard.php';
  });
  
  // Auto-hide error messages
  document.addEventListener('DOMContentLoaded', function() {
    const errorPopup = document.getElementById('errorPopup');
    if (errorPopup) {
      setTimeout(() => {
        errorPopup.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
          errorPopup.remove();
        }, 300);
      }, 5000);
    }
  });
</script>
</html> 