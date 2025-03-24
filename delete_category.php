<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate category ID
if (!isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid category ID']);
    exit();
}

$category_id = intval($_POST['category_id']);
$user_id = $_SESSION['user_id'];

// Start a transaction
$conn->begin_transaction();

try {
    // First, check if the category belongs to the user
    $check_query = "SELECT user_id FROM task_categories WHERE category_id = ? AND (user_id IS NULL OR user_id = ?)";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $category_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        throw new Exception('Category not found or you do not have permission to delete it.');
    }

    // Delete tasks in this category
    $delete_tasks_query = "DELETE FROM task WHERE category_id = ? AND creator_id = ?";
    $delete_tasks_stmt = $conn->prepare($delete_tasks_query);
    $delete_tasks_stmt->bind_param("ii", $category_id, $user_id);
    $delete_tasks_stmt->execute();

    // Delete the category
    $delete_category_query = "DELETE FROM task_categories WHERE category_id = ? AND (user_id IS NULL OR user_id = ?)";
    $delete_category_stmt = $conn->prepare($delete_category_query);
    $delete_category_stmt->bind_param("ii", $category_id, $user_id);
    $delete_category_stmt->execute();

    // Commit the transaction
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();

    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>