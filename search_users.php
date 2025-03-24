<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if GET request with search parameter
if (!isset($_GET['search']) || !isset($_GET['collab_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

$search = '%' . trim($_GET['search']) . '%';
$collabId = intval($_GET['collab_id']);
$userId = $_SESSION['user_id'];

try {
    // First check if the user has permission to add members (admin role)
    $permissionCheck = "SELECT cm.role_id FROM collabmember cm 
                       WHERE cm.user_id = ? AND cm.collab_id = ?";
    $permStmt = $conn->prepare($permissionCheck);
    $permStmt->bind_param("ii", $userId, $collabId);
    $permStmt->execute();
    $permResult = $permStmt->get_result();
    
    if ($permResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Not authorized to add members to this collaboration']);
        exit();
    }
    
    $userRole = $permResult->fetch_assoc()['role_id'];
    if ($userRole != 1) { // Assuming role_id 1 is for admin
        echo json_encode(['success' => false, 'error' => 'Only collaboration admins can add members']);
        exit();
    }
    
    // Search for users not already in the collaboration
    $query = "SELECT i.user_id, i.username, i.email 
              FROM individual i
              WHERE (i.username LIKE ? OR i.email LIKE ?)
              AND i.user_id NOT IN (
                  SELECT cm.user_id FROM collabmember cm WHERE cm.collab_id = ?
              )
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $search, $search, $collabId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
} 
catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 
finally {
    $conn->close();
}
?>