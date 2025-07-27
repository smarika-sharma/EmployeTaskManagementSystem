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
    // Check for tasks created in the last 10 seconds for this user
    $recent_tasks = $db->select("
        SELECT task_title, created_date 
        FROM task 
        WHERE user_id = ? 
        AND created_date >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
        ORDER BY created_date DESC 
        LIMIT 1
    ", [$user_id]);
    
    if (!empty($recent_tasks)) {
        echo json_encode([
            'has_new_tasks' => true,
            'task_title' => $recent_tasks[0]['task_title'],
            'created_date' => $recent_tasks[0]['created_date']
        ]);
    } else {
        echo json_encode([
            'has_new_tasks' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 