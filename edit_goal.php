<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate input
if (!isset($_POST['goal_id']) || !is_numeric($_POST['goal_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
    exit();
}

$goalId = intval($_POST['goal_id']);
$userId = $_SESSION['user_id'];

// Required fields
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
$status = isset($_POST['status']) && in_array($_POST['status'], ['active', 'completed', 'archived']) 
          ? $_POST['status'] : 'active';
$reminderTime = isset($_POST['reminderTime']) && !empty($_POST['reminderTime']) 
               ? trim($_POST['reminderTime']) : null;

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // First, check if the goal belongs to the user
    $stmt = $conn->prepare("SELECT goal_id FROM goal WHERE goal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goalId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Goal not found or unauthorized");
    }
    
    // Update the goal
    $stmt = $conn->prepare("UPDATE goal SET goal_title = ?, description = ?, category = ?, status = ? 
                          WHERE goal_id = ? AND user_id = ?");
    
    $stmt->bind_param("ssssis", $title, $description, $category, $status, $goalId, $userId);
    $stmt->execute();
    
    // Handle reminder settings
    if ($reminderTime !== null) {
        // Check if a reminder already exists
        $stmt = $conn->prepare("SELECT reminder_id FROM goal_reminder WHERE goal_id = ?");
        $stmt->bind_param("i", $goalId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing reminder
            $reminderId = $result->fetch_assoc()['reminder_id'];
            $stmt = $conn->prepare("UPDATE goal_reminder SET reminder_time = ?, is_active = 1 
                                  WHERE reminder_id = ?");
            $stmt->bind_param("si", $reminderTime, $reminderId);
        } else {
            // Insert new reminder
            $stmt = $conn->prepare("INSERT INTO goal_reminder (goal_id, reminder_time) VALUES (?, ?)");
            $stmt->bind_param("is", $goalId, $reminderTime);
        }
        
        $stmt->execute();
    } else {
        // Deactivate any existing reminders
        $stmt = $conn->prepare("UPDATE goal_reminder SET is_active = 0 WHERE goal_id = ?");
        $stmt->bind_param("i", $goalId);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Goal updated successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>