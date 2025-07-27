<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

$db = new DatabaseConnection();

// Handle Delete
if (isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $result = $db->delete("DELETE FROM task WHERE id = ?", [$task_id]);
    if ($result > 0) {
        $_SESSION['success_message'] = "Task deleted successfully!";
        header("Location: employee-task.php");
        exit();
    } else {
        $error_message = "Failed to delete task.";
    }
}

// Handle Edit
if (isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $task_title = trim($_POST['task_title']);
    $task_detail = trim($_POST['task_detail']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    
    if (empty($task_title) || empty($task_detail) || empty($due_date) || empty($status) || empty($priority)) {
        $error_message = "All fields are required.";
    } else {
        $result = $db->update("UPDATE task SET task_title = ?, task_detail = ?, due_date = ?, status = ?, priority = ? WHERE id = ?", 
                             [$task_title, $task_detail, $due_date, $status, $priority, $task_id]);
        if ($result > 0) {
            $_SESSION['success_message'] = "Task updated successfully!";
            header("Location: employee-task.php");
            exit();
        } else {
            $error_message = "Failed to update task.";
        }
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login-employee.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all tasks for this user
$tasks = $db->select("SELECT * FROM task WHERE user_id = ? ORDER BY created_date DESC", [$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tasks</title>
  <link rel="stylesheet" href="employee-task.css">
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
          <li class="active"><a href="employee-task.php" class="custom-link"><span class="nav-icon">●</span> My Tasks</a></li>
          <li><a href="employee-profile.html" class="custom-link"><span class="nav-icon">👤</span> Profile</a></li>
        </ul>
      </nav>
      <div class="sidebar-logout" id="logoutBtn">
        <span class="logout-icon">⎋</span> Logout
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <h1>My Tasks</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
          <a href="add-task-emp.php" class="btn-add-task" style="display: inline-block; background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; transition: background-color 0.3s;">+ Add Task</a>
        </div>
      </header>
      
      <?php if (isset($success_message)): ?>
        <div class="message success" id="successPopup"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      
      <?php if (isset($error_message)): ?>
        <div class="message error" id="errorPopup"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="message success" id="successPopup"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      
      <section class="task-table-section">
        <?php if (empty($tasks)): ?>
          <div style="text-align: center; padding: 40px; color: #6c757d;">
            <h3>No tasks found</h3>
            <p>You don't have any tasks assigned yet.</p>
            <div style="margin-top: 20px;">
              <a href="add-task-emp.php" class="btn-add-task" style="display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.3s;">+ Add New Task</a>
            </div>
          </div>
        <?php else: ?>
          <!-- Edit Forms for each task -->
          <?php foreach ($tasks as $task): ?>
            <div class="edit-form" id="editForm<?php echo $task['id']; ?>">
              <h3>Edit Task</h3>
              <form method="POST" action="">
                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                <div class="form-group">
                  <label>Task Title</label>
                  <input type="text" name="task_title" value="<?php echo htmlspecialchars($task['task_title']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Task Description</label>
                  <textarea name="task_detail" required><?php echo htmlspecialchars($task['task_detail']); ?></textarea>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                      <option value="Pending" <?php echo $task['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                      <option value="In Progress" <?php echo $task['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                      <option value="Completed" <?php echo $task['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" required>
                      <option value="Low" <?php echo $task['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                      <option value="Medium" <?php echo $task['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                      <option value="High" <?php echo $task['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                  </div>
                </div>
                <div class="edit-buttons">
                  <button type="button" class="btn-cancel" onclick="cancelEdit(<?php echo $task['id']; ?>)">Cancel</button>
                  <button type="submit" name="edit_task" class="btn-save">Save Changes</button>
                </div>
              </form>
            </div>
          <?php endforeach; ?>
          
          <!-- Single Task Table -->
          <table class="task-table">
            <thead>
              <tr>
                <th>Task Details</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tasks as $task): ?>
                <tr>
                  <td>
                    <div class="task-title-main"><?php echo htmlspecialchars($task['task_title']); ?></div>
                    <div class="task-title-desc"><?php echo htmlspecialchars($task['task_detail']); ?></div>
                    <div class="task-date">Created: <?php echo date('M j, Y', strtotime($task['created_date'])); ?></div>
                  </td>
                  <td>
                    <?php 
                    $due_date = new DateTime($task['due_date']);
                    $today = new DateTime();
                    $is_overdue = $due_date < $today && $task['status'] != 'Completed';
                    echo $due_date->format('M j, Y');
                    if ($is_overdue): ?>
                      <br><span class="overdue">Overdue</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="pill <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                  <td><span class="pill <?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
                                      <td>
                      <span class="action-icon" onclick="editTask(<?php echo $task['id']; ?>)">✏️</span>
                      <span class="action-icon" onclick="deleteTask(<?php echo $task['id']; ?>)">🗑️</span>
                    </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </main>
  </div>

<script>
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
  
  // Logout functionality
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'index.html';
    }
  });
  
  // Edit task functionality
  function editTask(taskId) {
    // Hide all edit forms first
    document.querySelectorAll('.edit-form').forEach(form => {
      form.classList.remove('active');
    });
    
    // Show the specific edit form
    const editForm = document.getElementById('editForm' + taskId);
    if (editForm) {
      editForm.classList.add('active');
      editForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
  
  // Cancel edit functionality
  function cancelEdit(taskId) {
    const editForm = document.getElementById('editForm' + taskId);
    if (editForm) {
      editForm.classList.remove('active');
    }
  }
  
  // Delete task functionality
  function deleteTask(taskId) {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '';
      
      const taskIdInput = document.createElement('input');
      taskIdInput.type = 'hidden';
      taskIdInput.name = 'task_id';
      taskIdInput.value = taskId;
      
      const deleteInput = document.createElement('input');
      deleteInput.type = 'hidden';
      deleteInput.name = 'delete_task';
      deleteInput.value = '1';
      
      form.appendChild(taskIdInput);
      form.appendChild(deleteInput);
      document.body.appendChild(form);
      form.submit();
    }
  }
  
  // Auto-hide popup messages
  function hidePopup(element) {
    element.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => {
      element.remove();
    }, 300);
  }
  
  // Auto-hide success and error popups after 3 seconds
  document.addEventListener('DOMContentLoaded', function() {
    const successPopup = document.getElementById('successPopup');
    const errorPopup = document.getElementById('errorPopup');
    
    if (successPopup) {
      setTimeout(() => {
        hidePopup(successPopup);
      }, 3000);
    }
    
    if (errorPopup) {
      setTimeout(() => {
        hidePopup(errorPopup);
      }, 5000); // Error messages stay longer
    }
  });
</script>
</body>
</html> 