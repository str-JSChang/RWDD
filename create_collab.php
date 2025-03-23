<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if collab name is provided
if (!isset($_POST['collab_name']) || empty($_POST['collab_name'])) {
    echo json_encode(['success' => false, 'error' => 'Collaboration name is required']);
    exit();
}

$collab_name = trim($_POST['collab_name']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$purpose = isset($_POST['purpose']) ? trim($_POST['purpose']) : 'General collaboration';
$user_id = $_SESSION['user_id'];

// Begin transaction
$conn->begin_transaction();

try {
    // Insert collab
    $insertCollab = "INSERT INTO collab (collab_name, description, purpose, created_at, status) 
                     VALUES (?, ?, ?, NOW(), 'active')";
    $stmtCollab = $conn->prepare($insertCollab);
    $stmtCollab->bind_param("sss", $collab_name, $description, $purpose);
    $stmtCollab->execute();
    
    $collab_id = $conn->insert_id;
    
    // Add creator as admin member
    $insertMember = "INSERT INTO collabmember (collab_id, user_id, role_id, joined_date) 
                     VALUES (?, ?, 1, NOW())"; // Assuming role_id 1 is for admin
    $stmtMember = $conn->prepare($insertMember);
    $stmtMember->bind_param("ii", $collab_id, $user_id);
    $stmtMember->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'collab_id' => $collab_id, 'collab_name' => $collab_name]);
    
} catch (Exception $e) {
    // Rollback if error
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>