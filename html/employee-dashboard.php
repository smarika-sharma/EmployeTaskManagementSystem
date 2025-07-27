<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

session_start();

$db = new DatabaseConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login-employee.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "User"; // Get user name from session

// Get task counts from database filtered by user_id
$total_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE user_id = ?", [$user_id]);
$pending_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Pending' AND user_id = ?", [$user_id]);
$in_progress_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'In Progress' AND user_id = ?", [$user_id]);
$completed_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Completed' AND user_id = ?", [$user_id]);

// Get recent tasks for this user
$recent_tasks = $db->select("SELECT * FROM task WHERE user_id = ? ORDER BY created_date DESC LIMIT 5", [$user_id]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="employee-dashboard.css">
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
          <li class="active"><a href="employee-dashboard.php" class="custom-link"><span class="nav-icon">▣</span> Dashboard</a></li>
          <li><a href="employee-task.php" class="custom-link"><span class="nav-icon">●</span> My Tasks</a></li>
          <li><a href="employee-profile.html" class="custom-link"><span class="nav-icon">👤</span> Profile</a></li>
        </ul>
      </nav>
      <div class="sidebar-logout" id="logoutBtn">
        <span class="logout-icon">⎋</span> Logout
      </div>
    </aside>
    <main class="main-content">
      <header class="main-header">
        <h1>Dashboard</h1>
      </header>
      <section class="welcome-card">
        <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>Here's an overview of your tasks and progress.</p>
      </section>
      <section class="dashboard-cards">
        <div class="dashboard-card">
          <div class="card-title">Total Tasks</div>
          <div class="card-value" id="total-tasks"><?php echo $total_tasks[0]['count']; ?></div>
          <div class="card-status all">All assigned</div>
          <div class="card-icon card-icon-blue">▶️</div>
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
            <a href="employee-task.php" class="view-all custom-link">View All</a>
          </div>
          <div class="task-list">
            <?php if (empty($recent_tasks)): ?>
              <div class="task-item">
                <div class="task-title">No tasks found</div>
                <div class="task-due">Create your first task to get started</div>
              </div>
            <?php else: ?>
              <?php foreach ($recent_tasks as $task): ?>
                <div class="task-item">
                  <div class="task-title"><?php echo htmlspecialchars($task['task_title']); ?></div>
                  <div class="task-due">Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
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
  
  // Dashboard refresh functionality
  function refreshDashboard() {
    fetch('get-dashboard-data.php')
    .then(response => response.json())
    .then(data => {
      document.getElementById('total-tasks').textContent = data.total_tasks;
      document.getElementById('pending-tasks').textContent = data.pending_tasks;
      document.getElementById('in-progress-tasks').textContent = data.in_progress_tasks;
      document.getElementById('completed-tasks').textContent = data.completed_tasks;
    })
    .catch(error => {
      console.error('Error refreshing dashboard:', error);
    });
  }
  
  // Refresh dashboard every 30 seconds
  setInterval(refreshDashboard, 30000);
  
</script>
</body>

</html> 