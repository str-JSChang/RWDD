<?php
session_start();
require_once 'db_connect.php';

ini_set('display_errors', 0); // Don't show errors in output
ini_set('log_errors', 1);
ini_set('error_log', 'member_errors.log'); // Create this file with write permissions

// Log all variables at the beginning
error_log('POST data: ' . print_r($_POST, true));
error_log('SESSION data: ' . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if it's a POST request with required parameters
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['user_id']) || !isset($_POST['collab_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

$memberUserId = intval($_POST['user_id']);
$collabId = intval($_POST['collab_id']);
$adminUserId = $_SESSION['user_id'];
$roleId = isset($_POST['role_id']) ? intval($_POST['role_id']) : 2; // Default to regular member (role_id = 2)

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // First check if the current user has admin permission
    $permissionCheck = "SELECT cm.role_id FROM collabmember cm 
                       WHERE cm.user_id = ? AND cm.collab_id = ?";
    $permStmt = $conn->prepare($permissionCheck);
    $permStmt->bind_param("ii", $adminUserId, $collabId);
    $permStmt->execute();
    $permResult = $permStmt->get_result();
    
    if ($permResult->num_rows === 0) {
        throw new Exception('Not authorized to add members to this collaboration');
    }
    
    $userRole = $permResult->fetch_assoc()['role_id'];
    if ($userRole != 1) { // Assuming role_id 1 is for admin
        throw new Exception('Only collaboration admins can add members');
    }
    
    // Check if the user to be added exists
    $userCheck = "SELECT username FROM individual WHERE user_id = ?";
    $userStmt = $conn->prepare($userCheck);
    $userStmt->bind_param("i", $memberUserId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $username = $userResult->fetch_assoc()['username'];
    
    // Check if the user is already a member
    $memberCheck = "SELECT 1 FROM collabmember WHERE user_id = ? AND collab_id = ?";
    $memberStmt = $conn->prepare($memberCheck);
    $memberStmt->bind_param("ii", $memberUserId, $collabId);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();
    
    if ($memberResult->num_rows > 0) {
        throw new Exception('User is already a member of this collaboration');
    }
    
    // Add the user to the collaboration
    $addMember = "INSERT INTO collabmember (collab_id, user_id, role_id, joined_date) 
                 VALUES (?, ?, ?, NOW())";
    $addStmt = $conn->prepare($addMember);
    $addStmt->bind_param("iii", $collabId, $memberUserId, $roleId);
    $addStmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "$username has been added to the collaboration",
        'user' => [
            'user_id' => $memberUserId,
            'username' => $username,
            'role_id' => $roleId
        ]
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