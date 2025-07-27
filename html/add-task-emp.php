<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-employee.php");
    exit();
}

$db = new DatabaseConnection();

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_title = trim($_POST['task_title']);
    $task_detail = trim($_POST['task_detail']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    
    if (empty($task_title) || empty($due_date)) {
        $error_message = "Task title and due date are required.";
    } else {
        $user_id = $_SESSION['user_id']; // Get logged-in user's ID from session
        
        // Get the next available ID
        $max_id_result = $db->select("SELECT MAX(id) as max_id FROM task");
        $next_id = ($max_id_result[0]['max_id'] ?? 0) + 1;
        
        $result = $db->create("INSERT INTO task (id, task_title, task_detail, due_date, status, priority, user_id, created_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", 
                             [$next_id, $task_title, $task_detail, $due_date, $status, $priority, $user_id]);
        
        if ($result > 0) {
            // Store success message in session and redirect
            $_SESSION['success_message'] = "Task created successfully!";
            header("Location: employee-task.php");
            exit();
        } else {
            $error_message = "Failed to create task. Please try again.";
        }
    }
}

// Error message will be displayed in the form below
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
</head>
<body>
  <div class="task-bg">
    <form class="task-card" action="add-task-emp.php" method="POST">
      <div style="text-align:left; margin-bottom: 8px;">
        <a href="employee-task.php" class="custom-link">← Back to My Tasks</a>
      </div>
      <h1 class="task-title">Add New Task</h1>
      
      <?php if (isset($error_message)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
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
    window.location.href = 'employee-task.php';
  });
</script>
</html> 