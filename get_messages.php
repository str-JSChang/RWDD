<?php
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

$collab_id = $_GET['collab_id'];
$user_id = $_SESSION['user_id'];

// Verify user is a member of the collaboration
$memberCheck = "SELECT 1 FROM collabmember WHERE collab_id = ? AND user_id = ?";
$stmtMember = $conn->prepare($memberCheck);
$stmtMember->bind_param("ii", $collab_id, $user_id);
$stmtMember->execute();
$memberResult = $stmtMember->get_result();

if ($memberResult->num_rows === 0) {
    echo json_encode(['error' => 'Not authorized to access this collaboration']);
    exit();
}

// Get messages for the collaboration
$query = "SELECT m.message_id, m.message_text, m.attachment_path, m.attachment_name, 
          m.created_at, u.user_id, u.username
          FROM collab_messages m
          INNER JOIN user u ON m.user_id = u.user_id
          WHERE m.collab_id = ?
          ORDER BY m.created_at ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $collab_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);

$stmt->close();
$conn->close();
?>