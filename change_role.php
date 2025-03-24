<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if it's a POST request with required parameters
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['user_id']) || !isset($_POST['collab_id']) || !isset($_POST['new_role_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

$userId = intval($_POST['user_id']);
$collabId = intval($_POST['collab_id']);
$newRoleId = intval($_POST['new_role_id']);
$currentUserId = $_SESSION['user_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Check if current user is an admin of the collaboration
    $adminCheck = "SELECT 1 FROM collabmember 
                  WHERE collab_id = ? AND user_id = ? AND role_id = 1";
    $adminStmt = $conn->prepare($adminCheck);
    $adminStmt->bind_param("ii", $collabId, $currentUserId);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    
    if ($adminResult->num_rows === 0) {
        throw new Exception('You must be an admin to change roles');
    }
    
    // Check if the user being modified exists in the collaboration
    $userCheck = "SELECT 1 FROM collabmember WHERE collab_id = ? AND user_id = ?";
    $userStmt = $conn->prepare($userCheck);
    $userStmt->bind_param("ii", $collabId, $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User is not a member of this collaboration');
    }
    
    // Update the user's role
    $updateRole = "UPDATE collabmember SET role_id = ? 
                  WHERE collab_id = ? AND user_id = ?";
    $updateStmt = $conn->prepare($updateRole);
    $updateStmt->bind_param("iii", $newRoleId, $collabId, $userId);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows === 0) {
        throw new Exception('No changes were made');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Role updated successfully'
    ]);
} 
catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 
finally {
    $conn->close();
}
?>