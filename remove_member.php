
<?php
// remove_member.php
// Add this file to your project directory

session_start();
require_once 'db_connect.php';

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

$userId = intval($_POST['user_id']);
$collabId = intval($_POST['collab_id']);
$currentUserId = $_SESSION['user_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Check if the user exists in the collaboration
    $memberCheck = "SELECT role_id FROM collabmember 
                   WHERE collab_id = ? AND user_id = ?";
    $memberStmt = $conn->prepare($memberCheck);
    $memberStmt->bind_param("ii", $collabId, $userId);
    $memberStmt->execute();
    $memberResult = $memberStmt->get_result();
    
    if ($memberResult->num_rows === 0) {
        throw new Exception('User is not a member of this collaboration');
    }
    
    $userRole = $memberResult->fetch_assoc()['role_id'];
    
    // If removing someone else, check if current user is an admin
    if ($userId !== $currentUserId) {
        $adminCheck = "SELECT 1 FROM collabmember 
                      WHERE collab_id = ? AND user_id = ? AND role_id = 1";
        $adminStmt = $conn->prepare($adminCheck);
        $adminStmt->bind_param("ii", $collabId, $currentUserId);
        $adminStmt->execute();
        $adminResult = $adminStmt->get_result();
        
        if ($adminResult->num_rows === 0) {
            throw new Exception('You must be an admin to remove other members');
        }
        
        // Don't allow removing the last admin
        if ($userRole === 1) {
            $adminCountCheck = "SELECT COUNT(*) as admin_count FROM collabmember 
                              WHERE collab_id = ? AND role_id = 1";
            $countStmt = $conn->prepare($adminCountCheck);
            $countStmt->bind_param("i", $collabId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            
            $adminCount = $countResult->fetch_assoc()['admin_count'];
            if ($adminCount <= 1) {
                throw new Exception('Cannot remove the last admin of a collaboration');
            }
        }
    }
    
    // Remove the member
    $removeMember = "DELETE FROM collabmember 
                    WHERE collab_id = ? AND user_id = ?";
    $removeStmt = $conn->prepare($removeMember);
    $removeStmt->bind_param("ii", $collabId, $userId);
    $removeStmt->execute();
    
    if ($removeStmt->affected_rows === 0) {
        throw new Exception('Failed to remove member');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Member removed successfully'
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