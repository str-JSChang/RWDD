<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Validate input
if (!isset($_GET['goal_id']) || !is_numeric($_GET['goal_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
    exit();
}

$goalId = intval($_GET['goal_id']);
$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Check if the goal belongs to the user
    $stmt = $conn->prepare("SELECT goal_id, progress, streak FROM goal WHERE goal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goalId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Goal not found or unauthorized");
    }
    
    $goal = $result->fetch_assoc();
    
    // Check if already checked in today
    $stmt = $conn->prepare("SELECT checkin_id FROM goal_checkin WHERE goal_id = ? AND checkin_date = ?");
    $stmt->bind_param("is", $goalId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Already checked in today");
    }
    
    // Get the last check-in date
    $stmt = $conn->prepare("SELECT checkin_date FROM goal_checkin 
                           WHERE goal_id = ? ORDER BY checkin_date DESC LIMIT 1");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Calculate new streak
    $newStreak = $goal['streak'];
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    if ($result->num_rows > 0) {
        $lastCheckin = $result->fetch_assoc()['checkin_date'];
        
        if ($lastCheckin === $yesterday) {
            // Consecutive day, increase streak
            $newStreak++;
        } else if ($lastCheckin !== $today) {
            // Streak broken, reset to 1
            $newStreak = 1;
        }
    } else {
        // First check-in ever
        $newStreak = 1;
    }
    
    // Calculate new progress (incremented by a small percentage each check-in)
    $newProgress = min(100, $goal['progress'] + 3);
    
    // Insert check-in record
    $stmt = $conn->prepare("INSERT INTO goal_checkin (goal_id, checkin_date) VALUES (?, ?)");
    $stmt->bind_param("is", $goalId, $today);
    $stmt->execute();
    
    // Update goal with new streak and progress
    $stmt = $conn->prepare("UPDATE goal SET streak = ?, progress = ?, last_checkin = ? WHERE goal_id = ?");
    $stmt->bind_param("iisi", $newStreak, $newProgress, $today, $goalId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'streak' => $newStreak,
        'progress' => $newProgress,
        'last_checkin' => $today
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>