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
    
    // Delete associated checkins and other related data first
    $stmt = $conn->prepare("DELETE FROM goal_checkin WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    
    // Delete any associated reminders
    $stmt = $conn->prepare("DELETE FROM goal_reminder WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    
    // Delete goal
    $stmt = $conn->prepare("DELETE FROM goal WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Goal deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>