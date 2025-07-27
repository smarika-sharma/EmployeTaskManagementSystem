<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

$db = new DatabaseConnection();

// Get admin-level statistics (no user_id filtering)
$total_employees = $db->select("SELECT COUNT(*) as count FROM users");
$pending_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Pending'");
$in_progress_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'In Progress'");
$completed_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Completed'");

// Get recent tasks with user information
$recent_tasks = $db->select("
    SELECT t.*, u.full_name 
    FROM task t 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_date DESC 
    LIMIT 4
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin-dashboard.css">
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
          <li class="active"><a href="#"><span class="nav-icon">▣</span> Dashboard</a></li>
          <li><a href="./admin-task.php"><span class="nav-icon">●</span> Tasks</a></li>
        </ul>
      </nav>
      <div class="sidebar-logout" id="logoutBtn">
        <span class="logout-icon">⎋</span> Logout
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <h1>Dashboard</h1>
        <div class="header-actions">
          <a href="new-task.php" class="btn btn-primary">+ Add Task</a>
          <a href="create-account.php" class="btn btn-outline">+ Add Employee</a>
          <div class="profile-circle">A</div>
        </div>
      </header>
      <section class="dashboard-cards">
        <div class="dashboard-card">
          <div class="card-title">Total Employees</div>
          <div class="card-value" id="total-employees"><?php echo $total_employees[0]['count']; ?></div>
          <div class="card-status active"><?php echo $total_employees[0]['count']; ?> Active</div>
          <div class="card-icon card-icon-purple">👥</div>
        </div>
        <div class="dashboard-card">
          <div class="card-title">Pending Tasks</div>
          <div class="card-value" id="pending-tasks"><?php echo $pending_tasks[0]['count']; ?></div>
          <div class="card-status pending">Awaiting Action</div>
          <div class="card-icon card-icon-orange">⏰</div>
        </div>
        <div class="dashboard-card">
          <div class="card-title">In Progress</div>
          <div class="card-value" id="in-progress-tasks"><?php echo $in_progress_tasks[0]['count']; ?></div>
          <div class="card-status inprogress">Active Work</div>
          <div class="card-icon card-icon-blue">▶️</div>
        </div>
        <div class="dashboard-card">
          <div class="card-title">Completed</div>
          <div class="card-value" id="completed-tasks"><?php echo $completed_tasks[0]['count']; ?></div>
          <div class="card-status completed">Finished</div>
          <div class="card-icon card-icon-green">✔️</div>
        </div>
      </section>
      <section class="dashboard-lists">
        <div class="recent-tasks">
          <div class="list-header">
            <span>Recent Tasks</span>
          </div>
          <div class="task-list">
            <?php if (empty($recent_tasks)): ?>
              <div class="task-item">
                <div class="task-title">No tasks found</div>
                <div class="task-assigned">Create tasks to see them here</div>
              </div>
            <?php else: ?>
              <?php foreach ($recent_tasks as $task): ?>
                <div class="task-item">
                  <div class="task-title"><?php echo htmlspecialchars($task['task_title']); ?></div>
                  <div class="task-assigned">Assigned to <?php echo htmlspecialchars($task['full_name'] ?? 'Unknown User'); ?></div>
                  <div class="task-labels">
                    <span class="label <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span>
                    <span class="label <?php echo strtolower($task['priority']); ?>"><?php echo htmlspecialchars($task['priority']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
        </div>
      </section>
    </main>
  </div>
<script>
  const logoutBtn = document.getElementById('logoutBtn');
  logoutBtn.addEventListener('click', function() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'index.html';
    }
  });
  
  // Admin dashboard refresh functionality
  function refreshAdminDashboard() {
    fetch('get-admin-dashboard-data.php')
    .then(response => response.json())
    .then(data => {
      document.getElementById('total-employees').textContent = data.total_employees;
      document.getElementById('pending-tasks').textContent = data.pending_tasks;
      document.getElementById('in-progress-tasks').textContent = data.in_progress_tasks;
      document.getElementById('completed-tasks').textContent = data.completed_tasks;
    })
    .catch(error => {
      console.error('Error refreshing admin dashboard:', error);
    });
  }
  
  // Refresh dashboard every 30 seconds
  setInterval(refreshAdminDashboard, 30000);
</script>
</body>
</html>
