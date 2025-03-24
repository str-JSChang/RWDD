<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate task ID
if (!isset($_POST['task_id']) || !is_numeric($_POST['task_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit();
}

$task_id = intval($_POST['task_id']);
$user_id = $_SESSION['user_id'];

// Delete task
$query = "DELETE FROM task WHERE task_id = ? AND creator_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $task_id, $user_id);

if ($stmt->execute()) {
    // Check if any rows were actually deleted
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Task not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>