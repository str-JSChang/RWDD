<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get collaborations the user is a member of
$query = "SELECT c.collab_id, c.collab_name, c.description, c.purpose
          FROM collab c
          INNER JOIN collabmember cm ON c.collab_id = cm.collab_id
          WHERE cm.user_id = ? AND c.status = 'active'
          ORDER BY c.collab_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$collabs = [];
while ($row = $result->fetch_assoc()) {
    $collabs[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['collabs' => $collabs]);

$stmt->close();
$conn->close();
?>