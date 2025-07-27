<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../database.php';

try {
    $db = new DatabaseConnection();
    
    echo "<h2>Setting up User-Task Relationship</h2>";
    
    // Check if user_id column exists in task table
    $columns = $db->select("SHOW COLUMNS FROM task LIKE 'user_id'");
    
    if (empty($columns)) {
        // Add user_id column to task table
        $result = $db->update("ALTER TABLE task ADD COLUMN user_id INT NOT NULL DEFAULT 1");
        echo "<p style='color: green;'>✅ Successfully added user_id column to task table</p>";
        
        // Update existing tasks to have user_id = 1 (default user)
        $update_result = $db->update("UPDATE task SET user_id = 1 WHERE user_id = 0 OR user_id IS NULL");
        echo "<p style='color: green;'>✅ Updated existing tasks to have user_id = 1</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ user_id column already exists in task table</p>";
    }
    
    // Show users table structure
    echo "<h3>Users Table Structure:</h3>";
    $users_structure = $db->select("DESCRIBE users");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($users_structure as $row) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show task table structure
    echo "<h3>Task Table Structure:</h3>";
    $task_structure = $db->select("DESCRIBE task");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($task_structure as $row) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show sample users
    echo "<h3>Sample Users:</h3>";
    $users = $db->select("SELECT id, name, email FROM users LIMIT 5");
    if (!empty($users)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show sample tasks with user_id
    echo "<h3>Sample Tasks with User IDs:</h3>";
    $tasks = $db->select("SELECT id, task_title, user_id, status FROM task LIMIT 5");
    if (!empty($tasks)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Task ID</th><th>Task Title</th><th>User ID</th><th>Status</th></tr>";
        foreach ($tasks as $task) {
            echo "<tr>";
            echo "<td>" . $task['id'] . "</td>";
            echo "<td>" . htmlspecialchars($task['task_title']) . "</td>";
            echo "<td>" . $task['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($task['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>How the Relationship Works:</h3>";
    echo "<ul>";
    echo "<li><strong>Users Table:</strong> Contains user information with unique 'id' as primary key</li>";
    echo "<li><strong>Task Table:</strong> Contains tasks with 'user_id' as foreign key</li>";
    echo "<li><strong>Login:</strong> User logs in → session stores user's 'id' as 'user_id'</li>";
    echo "<li><strong>Task Creation:</strong> New tasks get 'user_id' = logged-in user's 'id'</li>";
    echo "<li><strong>Task Filtering:</strong> Queries filter tasks WHERE user_id = session user_id</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 