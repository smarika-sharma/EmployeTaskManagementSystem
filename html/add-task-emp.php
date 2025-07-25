<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

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
        $result = $db->create("INSERT INTO task (task_title, task_detail, due_date, status, priority) VALUES (?, ?, ?, ?, ?)", 
                             [$task_title, $task_detail, $due_date, $status, $priority]);
        
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

// If there's an error, redirect back to the form
if (isset($error_message)) {
    header("Location: add-task-emp.php?error=" . urlencode($error_message));
    exit();
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
</head>
<body>
  <div class="task-bg">
    <form class="task-card" action="add-task-emp.php" method="POST">
      <div style="text-align:left; margin-bottom: 8px;">
        <a href="employee-task.php" class="custom-link">← Back to My Tasks</a>
      </div>
      <h1 class="task-title">Add New Task</h1>
      <div class="task-form-grid">
        <div class="form-group">
          <label>Task Title *</label>
          <input type="text" name="task_title" placeholder="Enter task title" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
          </select>
        </div>
        <div class="form-group form-group-full">
          <label>Description</label>
          <textarea name="task_detail" placeholder="Enter task description"></textarea>
        </div>
        <div class="form-group">
          <label>Priority</label>
          <select name="priority">
            <option value="Medium">Medium</option>
            <option value="High">High</option>
            <option value="Low">Low</option>
          </select>
        </div>
        <div class="form-group">
          <label>Deadline *</label>
          <input type="date" name="due_date" required>
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