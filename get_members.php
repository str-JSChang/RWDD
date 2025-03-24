<?php
// get_members.php

session_start();
require_once 'db_connect.php';

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

$collab_id = intval($_GET['collab_id']);
$user_id = $_SESSION['user_id'];

// Verify user is a member of the collaboration
$memberCheck = "SELECT cm.role_id FROM collabmember cm 
               WHERE cm.collab_id = ? AND cm.user_id = ?";
$stmtMember = $conn->prepare($memberCheck);
$stmtMember->bind_param("ii", $collab_id, $user_id);
$stmtMember->execute();
$memberResult = $stmtMember->get_result();

if ($memberResult->num_rows === 0) {
    echo json_encode(['error' => 'Not authorized to access this collaboration']);
    exit();
}

// Store current user's role
$currentUserRole = $memberResult->fetch_assoc()['role_id'];

// Get all members of the collaboration
$query = "SELECT cm.user_id, cm.role_id, u.username 
          FROM collabmember cm
          INNER JOIN individual u ON cm.user_id = u.user_id
          WHERE cm.collab_id = ?
          ORDER BY cm.role_id ASC, u.username ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $collab_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'members' => $members,
    'currentUserRole' => $currentUserRole
]);

$stmt->close();
$conn->close();
?>