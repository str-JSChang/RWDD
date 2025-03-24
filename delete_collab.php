<?php
// delete_collab.php
// Add this file to your project directory

session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if it's a POST request with collab_id
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['collab_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing collaboration ID']);
    exit();
}

$collabId = intval($_POST['collab_id']);
$userId = $_SESSION['user_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Check if current user is an admin of the collaboration
    $adminCheck = "SELECT 1 FROM collabmember 
                  WHERE collab_id = ? AND user_id = ? AND role_id = 1";
    $adminStmt = $conn->prepare($adminCheck);
    $adminStmt->bind_param("ii", $collabId, $userId);
    $adminStmt->execute();
    $adminResult = $adminStmt->get_result();
    
    if ($adminResult->num_rows === 0) {
        throw new Exception('You must be an admin to delete a collaboration');
    }
    
    // Get collaboration name for confirmation message
    $nameQuery = "SELECT collab_name FROM collab WHERE collab_id = ?";
    $nameStmt = $conn->prepare($nameQuery);
    $nameStmt->bind_param("i", $collabId);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();
    
    if ($nameResult->num_rows === 0) {
        throw new Exception('Collaboration not found');
    }
    
    $collabName = $nameResult->fetch_assoc()['collab_name'];
    
    // Delete all messages in the collaboration
    $deleteMessages = "DELETE FROM collab_messages WHERE collab_id = ?";
    $msgStmt = $conn->prepare($deleteMessages);
    $msgStmt->bind_param("i", $collabId);
    $msgStmt->execute();
    
    // Delete all members from the collaboration
    $deleteMembers = "DELETE FROM collabmember WHERE collab_id = ?";
    $memberStmt = $conn->prepare($deleteMembers);
    $memberStmt->bind_param("i", $collabId);
    $memberStmt->execute();
    
    // Delete the collaboration itself
    $deleteCollab = "DELETE FROM collab WHERE collab_id = ?";
    $collabStmt = $conn->prepare($deleteCollab);
    $collabStmt->bind_param("i", $collabId);
    $collabStmt->execute();
    
    if ($collabStmt->affected_rows === 0) {
        throw new Exception('Failed to delete collaboration');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Collaboration '$collabName' has been deleted"
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