<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate task ID
if (!isset($_GET['task_id']) || !is_numeric($_GET['task_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit();
}

$task_id = intval($_GET['task_id']);
$user_id = $_SESSION['user_id'];

// Fetch task details
$query = "SELECT t.task_id, t.task_name, t.category_id, t.start_date, 
                 t.due_date, t.priority, t.description, t.status, 
                 c.category_name
          FROM task t
          LEFT JOIN task_categories c ON t.category_id = c.category_id
          WHERE t.task_id = ? AND t.creator_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Task not found']);
    exit();
}

$task = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'task' => $task
]);

$stmt->close();
$conn->close();
?>