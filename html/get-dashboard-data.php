<?php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new DatabaseConnection();

try {
    // Get task counts from database filtered by user_id
    $total_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE user_id = ?", [$user_id]);
    $pending_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Pending' AND user_id = ?", [$user_id]);
    $in_progress_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'In Progress' AND user_id = ?", [$user_id]);
    $completed_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Completed' AND user_id = ?", [$user_id]);

    $data = [
        'total_tasks' => $total_tasks[0]['count'],
        'pending_tasks' => $pending_tasks[0]['count'],
        'in_progress_tasks' => $in_progress_tasks[0]['count'],
        'completed_tasks' => $completed_tasks[0]['count']
    ];

    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 