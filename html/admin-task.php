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
        header("Location: admin-task.php");
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
    $user_id = $_POST['user_id'];
    
    if (empty($task_title) || empty($task_detail) || empty($due_date) || empty($status) || empty($priority) || empty($user_id)) {
        $error_message = "All fields are required.";
    } else {
        $result = $db->update("UPDATE task SET task_title = ?, task_detail = ?, due_date = ?, status = ?, priority = ?, user_id = ? WHERE id = ?", 
                             [$task_title, $task_detail, $due_date, $status, $priority, $user_id, $task_id]);
        if ($result > 0) {
            $_SESSION['success_message'] = "Task updated successfully!";
            header("Location: admin-task.php");
            exit();
        } else {
            $error_message = "Failed to update task.";
        }
    }
}

// Fetch all tasks with user information
$tasks = $db->select("SELECT t.*, u.full_name FROM task t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_date DESC");

// Fetch all users for the dropdown
$users = $db->select("SELECT id, full_name FROM users ORDER BY full_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management</title>
  <link rel="stylesheet" href="admin-task.css?v=2">
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
          <div class="sidebar-subtitle">Admin Dashboard</div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="admin-dashboard.php"><span class="nav-icon">▣</span> Dashboard</a></li>
          <li class="active"><a href="admin-task.php"><span class="nav-icon">●</span> Tasks</a></li>
        </ul>
      </nav>
      <div class="sidebar-logout" id="logoutBtn">
        <span class="logout-icon">⎋</span> Logout
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <h1>Task Management</h1>
        <div class="header-actions">
          <a href="new-task.php" class="btn btn-primary">+ Add Task</a>
          <div class="profile-circle profile-purple">A</div>
        </div>
      </header>
      
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
            <p>There are no tasks in the system yet.</p>
            <div style="margin-top: 20px;">
              <a href="new-task.php" class="btn btn-primary" style="display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background-color 0.3s;">+ Add New Task</a>
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
                    <label>Assigned To</label>
                    <select name="user_id" required>
                      <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $task['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($user['full_name']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
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
            
          <!-- Task Table -->
          <table class="task-table">
            <thead>
              <tr>
                <th>Task Title</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Deadline</th>
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
                  <td><?php echo htmlspecialchars($task['full_name'] ?? 'Unassigned'); ?></td>
                  <td><span class="pill <?php 
                    $status_class = strtolower(str_replace(' ', '', $task['status']));
                    echo $status_class;
                  ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                  <td><span class="pill <?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span></td>
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
  // Logout functionality
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'index.html';
    }
  });
  
  // Edit task functionality
  function editTask(taskId) {
    console.log('Edit task clicked for ID:', taskId);
    
    // Hide all edit forms first
    document.querySelectorAll('.edit-form').forEach(form => {
      form.classList.remove('active');
      console.log('Hiding form:', form.id);
    });
    
    // Show the specific edit form
    const editForm = document.getElementById('editForm' + taskId);
    console.log('Looking for edit form:', 'editForm' + taskId);
    console.log('Found edit form:', editForm);
    
    if (editForm) {
      editForm.classList.add('active');
      editForm.style.display = 'block';
      editForm.style.visibility = 'visible';
      editForm.style.opacity = '1';
      console.log('Showing edit form:', editForm.id);
      editForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
      console.error('Edit form not found for task ID:', taskId);
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
    console.log('Page loaded, initializing edit forms...');
    
    // Ensure all edit forms are hidden on page load
    document.querySelectorAll('.edit-form').forEach(form => {
      form.classList.remove('active');
      form.style.display = 'none';
      form.style.visibility = 'hidden';
      form.style.opacity = '0';
      console.log('Initialized edit form:', form.id, 'as hidden');
    });
    
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
