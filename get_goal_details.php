<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Validate input
if (!isset($_GET['goal_id']) || !is_numeric($_GET['goal_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid goal ID']);
    exit();
}

$goalId = intval($_GET['goal_id']);
$userId = $_SESSION['user_id'];

try {
    // Get goal information
    $stmt = $conn->prepare("SELECT * FROM goal WHERE goal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $goalId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Goal not found or unauthorized");
    }
    
    $goal = $result->fetch_assoc();
    
    // Get check-in history (last 30 days)
    $stmt = $conn->prepare("SELECT checkin_date FROM goal_checkin 
                           WHERE goal_id = ? 
                           AND checkin_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                           ORDER BY checkin_date DESC");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $checkinResult = $stmt->get_result();
    
    $checkins = [];
    while ($row = $checkinResult->fetch_assoc()) {
        $checkins[] = $row['checkin_date'];
    }
    
    // Calculate total check-ins
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM goal_checkin WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $totalCheckins = $stmt->get_result()->fetch_assoc()['total'];
    
    // Calculate longest streak
    $stmt = $conn->prepare("SELECT MAX(streak) as max_streak FROM goal_streak_history WHERE goal_id = ?");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $maxStreakResult = $stmt->get_result();
    
    $longestStreak = 0;
    if ($maxStreakResult->num_rows > 0) {
        $longestStreak = $maxStreakResult->fetch_assoc()['max_streak'];
    }
    
    // If current streak is longer, use that
    $longestStreak = max($longestStreak, $goal['streak']);
    
    // Calculate completion rate (check-ins / days since created)
    $createdDate = new DateTime($goal['created_at']);
    $today = new DateTime();
    $daysSinceCreated = $createdDate->diff($today)->days + 1; // +1 to include today
    
    $completionRate = $daysSinceCreated > 0 ? 
                     round(($totalCheckins / $daysSinceCreated) * 100) : 0;
    
    // Get weekly streak history (last 10 weeks)
    $weeklyStreaks = [];
    $stmt = $conn->prepare("SELECT week_start, max_streak FROM (
                           SELECT DATE_SUB(checkin_date, INTERVAL WEEKDAY(checkin_date) DAY) as week_start,
                           COUNT(*) as check_count,
                           MAX(streak) as max_streak
                           FROM goal_checkin
                           JOIN goal ON goal_checkin.goal_id = goal.goal_id
                           WHERE goal_checkin.goal_id = ?
                           GROUP BY week_start
                           ORDER BY week_start DESC
                           LIMIT 10
                           ) as weekly_stats");
    $stmt->bind_param("i", $goalId);
    $stmt->execute();
    $weeklyResult = $stmt->get_result();
    
    while ($row = $weeklyResult->fetch_assoc()) {
        $weeklyStreaks[$row['week_start']] = $row['max_streak'];
    }
    
    // Prepare calendar data
    $calendarData = [];
    $endDate = new DateTime();
    $startDate = clone $endDate;
    $startDate->modify('-30 days');
    
    $currentDate = clone $startDate;
    
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $calendarData[] = [
            'date' => $dateStr,
            'checked' => in_array($dateStr, $checkins)
        ];
        $currentDate->modify('+1 day');
    }
    
    // Prepare streak history data
    $streakHistory = [];
    for ($i = 0; $i < 10; $i++) {
        $weekStart = date('Y-m-d', strtotime("-$i weeks", strtotime('monday this week')));
        $streakHistory[] = isset($weeklyStreaks[$weekStart]) ? $weeklyStreaks[$weekStart] : 0;
    }
    
    // Return the data
    echo json_encode([
        'success' => true,
        'goalDetails' => [
            'id' => $goal['goal_id'],
            'title' => $goal['goal_title'],
            'description' => $goal['description'],
            'category' => $goal['category'],
            'progress' => $goal['progress'],
            'currentStreak' => $goal['streak'],
            'longestStreak' => $longestStreak,
            'totalCheckins' => $totalCheckins,
            'completionRate' => $completionRate,
            'calendar' => $calendarData,
            'streakHistory' => $streakHistory
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>