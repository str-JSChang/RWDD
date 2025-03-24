<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate task ID and status
if (!isset($_POST['task_id']) || !is_numeric($_POST['task_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid task ID']);
    exit();
}

if (!isset($_POST['status']) || !in_array($_POST['status'], ['active', 'completed'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit();
}

$task_id = intval($_POST['task_id']);
$status = $_POST['status'];
$user_id = $_SESSION['user_id'];

// Update task status
$query = "UPDATE task SET status = ?, updated_at = NOW() WHERE task_id = ? AND creator_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $status, $task_id, $user_id);

if ($stmt->execute()) {
    // Check if any rows were actually updated
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