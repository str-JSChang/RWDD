<?php
// // Disable all output before JSON
// ob_start();

// // Explicitly set headers to prevent any output
// header('Content-Type: application/json');
// header('X-Content-Type-Options: nosniff');

// // Turn off all error reporting for production
// error_reporting(0);

session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];

// Get sorting parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'streak';
$category = isset($_GET['category']) && $_GET['category'] !== 'all' ? $_GET['category'] : null;

try {
    // Build query based on filters
    $query = "SELECT * FROM goal WHERE user_id = ? AND status = 'active'";
    $params = [$userId];
    
    // Add category filter if specified
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    
    // Add sorting
    switch ($sort) {
        case 'recent':
            $query .= " ORDER BY last_checkin DESC NULLS LAST";
            break;
        case 'progress':
            $query .= " ORDER BY progress DESC";
            break;
        case 'alphabetical':
            $query .= " ORDER BY goal_title ASC";
            break;
        case 'streak':
        default:
            $query .= " ORDER BY streak DESC";
            break;
    }
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    // Bind parameters using the correct types
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all goals
    $goals = [];
    $today = date('Y-m-d');
    
    while ($row = $result->fetch_assoc()) {
        // Use 'other' as default category if NULL
        $category = $row['category'] ?: 'other';
        
        $goals[] = [
            'id' => $row['goal_id'],
            'title' => $row['goal_title'],
            'description' => $row['description'] ?? '',
            'category' => $category,
            'progress' => $row['progress'] ?? 0,
            'streak' => $row['streak'] ?? 0,
            'status' => $row['status'] ?? 'active',
            'lastCheckin' => $row['last_checkin'] ? date('M d', strtotime($row['last_checkin'])) : 'Never',
            'canCheckIn' => $row['last_checkin'] !== $today
        ];
    }
    
    // Calculate stats
    $totalStreaks = array_sum(array_column($goals, 'streak'));
    $bestStreak = $goals ? max(array_column($goals, 'streak')) : 0;
    $todayCheckins = 0;
    
    foreach ($goals as $goal) {
        if (!$goal['canCheckIn']) {
            $todayCheckins++;
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'goals' => $goals,
        'stats' => [
            'totalStreaks' => $totalStreaks,
            'bestStreak' => $bestStreak,
            'todayCheckins' => $todayCheckins
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
// No closing PHP tag to prevent accidental whitespace