<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if required data is provided
if (!isset($_POST['collab_id']) || !isset($_POST['message_text'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit();
}

$collab_id = $_POST['collab_id'];
$message_text = trim($_POST['message_text']);
$user_id = $_SESSION['user_id'];

// Check if the message is empty
if (empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

// Check if user is a member of the collaboration
$memberCheck = "SELECT 1 FROM collabmember WHERE collab_id = ? AND user_id = ?";
$stmtMember = $conn->prepare($memberCheck);
$stmtMember->bind_param("ii", $collab_id, $user_id);
$stmtMember->execute();
$memberResult = $stmtMember->get_result();

if ($memberResult->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Not authorized to post in this collaboration']);
    exit();
}

// Handle file upload if present
$attachment_path = null;
$attachment_name = null;
$attachment_type = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $attachment_name = $_FILES['attachment']['name'];
    $attachment_type = $_FILES['attachment']['type'];
    
    // Generate unique filename
    $unique_name = time() . '_' . $attachment_name;
    $attachment_path = $upload_dir . $unique_name;
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        exit();
    }
}

// Insert message
$query = "INSERT INTO collab_messages (collab_id, user_id, message_text, attachment_path, attachment_name, attachment_type) 
          VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iissss", $collab_id, $user_id, $message_text, $attachment_path, $attachment_name, $attachment_type);

if ($stmt->execute()) {
    $message_id = $conn->insert_id;
    
    // Get message with user info for response
    $messageQuery = "SELECT m.message_id, m.message_text, m.attachment_path, m.attachment_name, 
                    m.created_at, u.user_id, u.username
                    FROM collab_messages m
                    INNER JOIN user u ON m.user_id = u.user_id
                    WHERE m.message_id = ?";
    $msgStmt = $conn->prepare($messageQuery);
    $msgStmt->bind_param("i", $message_id);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result();
    $message = $msgResult->fetch_assoc();
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to post message']);
}

$conn->close();
?>