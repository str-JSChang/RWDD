<?php
session_start();
require_once 'db_connect.php';

// Enable error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'file_upload_log.txt');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Check if required data is provided
if (!isset($_POST['collab_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing collaboration ID']);
    exit();
}

$collab_id = $_POST['collab_id'];
$message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
$user_id = $_SESSION['user_id'];

// Allow empty message if there's an attachment
$hasAttachment = isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK;
if (empty($message_text) && !$hasAttachment) {
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty if there is no attachment']);
    exit();
}

// Check if user is a member of the collaboration
$memberCheck = "SELECT 1 FROM collabmember WHERE collab_id = ? AND user_id = ?";
$stmtMember = $conn->prepare($memberCheck);
if (!$stmtMember) {
    error_log("Failed to prepare member check: " . $conn->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

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

if ($hasAttachment) {
    error_log("File upload detected: " . print_r($_FILES['attachment'], true));
    
    // Define the upload directory with absolute path
    $upload_dir = __DIR__ . '/uploads/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("Failed to create directory: $upload_dir");
            echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
            exit();
        }
        error_log("Created directory: $upload_dir");
    }
    
    // Verify directory is writable
    if (!is_writable($upload_dir)) {
        error_log("Directory not writable: $upload_dir");
        echo json_encode(['success' => false, 'error' => 'Upload directory is not writable']);
        exit();
    }
    
    $attachment_name = $_FILES['attachment']['name'];
    $attachment_type = $_FILES['attachment']['type'];
    
    // Generate unique filename to prevent overwriting
    $unique_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $attachment_name);
    $upload_path = $upload_dir . $unique_name;
    
    // Web-accessible path for storage in database
    $attachment_path = 'uploads/' . $unique_name;
    
    error_log("Attempting to move uploaded file to: $upload_path");
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
        error_log("Failed to move uploaded file. Upload error code: " . $_FILES['attachment']['error']);
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        exit();
    }
    
    error_log("File uploaded successfully to: $upload_path");
}

// Insert message with prepared statement
try {
    $query = "INSERT INTO collab_messages (collab_id, user_id, message_text, attachment_path, attachment_name, attachment_type, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("iissss", $collab_id, $user_id, $message_text, $attachment_path, $attachment_name, $attachment_type);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $message_id = $conn->insert_id;
    
    // Get message with user info for response
    $messageQuery = "SELECT m.message_id, m.message_text, m.attachment_path, m.attachment_name, 
                    m.created_at, u.user_id, u.username
                    FROM collab_messages m
                    INNER JOIN individual u ON m.user_id = u.user_id
                    WHERE m.message_id = ?";
    $msgStmt = $conn->prepare($messageQuery);
    
    if (!$msgStmt) {
        throw new Exception("Failed to prepare select statement: " . $conn->error);
    }
    
    $msgStmt->bind_param("i", $message_id);
    $msgStmt->execute();
    $msgResult = $msgStmt->get_result();
    
    if (!$msgResult) {
        throw new Exception("Failed to get message result: " . $msgStmt->error);
    }
    
    $message = $msgResult->fetch_assoc();
    
    if (!$message) {
        throw new Exception("Failed to retrieve inserted message");
    }
    
    // Log success
    error_log("Message inserted successfully with ID: $message_id");
    if ($attachment_path) {
        error_log("Attachment saved at: $attachment_path");
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    error_log("Error in post_messages.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>