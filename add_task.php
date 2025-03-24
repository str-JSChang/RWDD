<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate required inputs
if (!isset($_POST['task_name']) || empty($_POST['task_name'])) {
    echo json_encode(['success' => false, 'error' => 'Task name is required']);
    exit();
}

// Get task data
$task_name = trim($_POST['task_name']);
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
$start_date = isset($_POST['start_date']) && !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;
$priority = isset($_POST['priority']) ? $_POST['priority'] : 'Medium';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$creator_id = $_SESSION['user_id'];

// Insert task
$query = "INSERT INTO task (task_name, category_id, start_date, due_date, priority, description, creator_id, status, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("sissssi", $task_name, $category_id, $start_date, $due_date, $priority, $description, $creator_id);

if ($stmt->execute()) {
    $task_id = $conn->insert_id;
    echo json_encode(['success' => true, 'task_id' => $task_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>