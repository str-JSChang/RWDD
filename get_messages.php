<?php
// Turn off PHP error display in the output
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// Instead, log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();
require_once 'db_connect.php';

// Ensure no output before our JSON
ob_clean();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Check if collab_id is provided
if (!isset($_GET['collab_id']) || empty($_GET['collab_id'])) {
    echo json_encode(['error' => 'Collaboration ID is required']);
    exit();
}

$collab_id = $_GET['collab_id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify user is a member of the collaboration
    $memberCheck = "SELECT 1 FROM collabmember WHERE collab_id = ? AND user_id = ?";
    $stmtMember = $conn->prepare($memberCheck);
    if (!$stmtMember) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmtMember->bind_param("ii", $collab_id, $user_id);
    $stmtMember->execute();
    $memberResult = $stmtMember->get_result();

    if ($memberResult->num_rows === 0) {
        echo json_encode(['error' => 'Not authorized to access this collaboration']);
        exit();
    }

    // Get messages for the collaboration
    $query = "SELECT m.message_id, m.message_text, m.attachment_path, m.attachment_name, 
            m.created_at, i.user_id, i.username
            FROM collab_messages m
            INNER JOIN individual i ON m.user_id = i.user_id
            WHERE m.collab_id = ?
            ORDER BY m.created_at ASC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $collab_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['messages' => $messages]);
} 
catch (Exception $e) {
    error_log("Error in get_messages.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 
finally {
    if (isset($stmt)) $stmt->close();
    if (isset($stmtMember)) $stmtMember->close();
    if (isset($conn)) $conn->close();
}
?>