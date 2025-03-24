<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate input
$requiredFields = ['title', 'category'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: ' . implode(', ', $missingFields)
    ]);
    exit();
}

// Sanitize input
$title = trim($_POST['title']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$category = trim($_POST['category']);
$reminderTime = isset($_POST['reminderTime']) && !empty($_POST['reminderTime']) ? 
                trim($_POST['reminderTime']) : null;

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // Insert new goal
    $stmt = $conn->prepare("INSERT INTO goal (user_id, goal_title, description, category, 
                          progress, streak, status, created_at, last_checkin) 
                          VALUES (?, ?, ?, ?, 0, 0, 'active', NOW(), NULL)");
    
    $stmt->bind_param("isss", $userId, $title, $description, $category);
    $stmt->execute();
    
    $goalId = $conn->insert_id;
    
    // If reminder time is set, add a reminder record
    if ($reminderTime !== null) {
        $stmt = $conn->prepare("INSERT INTO goal_reminder (goal_id, reminder_time) 
                              VALUES (?, ?)");
        $stmt->bind_param("is", $goalId, $reminderTime);
        $stmt->execute();
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'goal_id' => $goalId,
        'message' => 'Goal created successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>