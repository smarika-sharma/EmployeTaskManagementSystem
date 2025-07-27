<?php
session_start();
require_once '../database.php';

header('Content-Type: application/json');

$db = new DatabaseConnection();

try {
    // Get admin-level statistics (no user_id filtering - shows all system data)
    $total_employees = $db->select("SELECT COUNT(*) as count FROM users");
    $pending_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Pending'");
    $in_progress_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'In Progress'");
    $completed_tasks = $db->select("SELECT COUNT(*) as count FROM task WHERE status = 'Completed'");

    $data = [
        'total_employees' => $total_employees[0]['count'],
        'pending_tasks' => $pending_tasks[0]['count'],
        'in_progress_tasks' => $in_progress_tasks[0]['count'],
        'completed_tasks' => $completed_tasks[0]['count']
    ];

    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 