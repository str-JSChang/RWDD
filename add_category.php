<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate input
if (!isset($_POST['category_name']) || empty($_POST['category_name'])) {
    echo json_encode(['success' => false, 'error' => 'Category name is required']);
    exit();
}

$category_name = trim($_POST['category_name']);
$color_code = isset($_POST['color_code']) ? trim($_POST['color_code']) : '#607D8B';
$user_id = $_SESSION['user_id'];

// Insert the new category
$query = "INSERT INTO task_categories (category_name, color_code, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $category_name, $color_code, $user_id);

if ($stmt->execute()) {
    $category_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'category_id' => $category_id,
        'category_name' => $category_name,
        'color_code' => $color_code
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>