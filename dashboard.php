<!-- debugging -->
<?php
session_start();
require_once 'db_connect.php';
// if(isset($_SESSION['loggedin'])) {
//     echo "Logged in as: " . $_SESSION['username'] . "<br>";
//     echo "User ID: " . $_SESSION['user_id'] . "<br>";
// }

// Function to fetch dashboard data for the current user
function getDashboardData($userId, $conn) {
    $data = [
        'tasks' => [],
        'activeGoals' => [], // Initialize activeGoals to avoid undefined key
        'productivityStats' => [],
        'productivity' => [ // For the productivity chart
            'mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0,
            'fri' => 0, 'sat' => 0, 'sun' => 0
        ],
    ];
    
    // Get recent tasks
    $query = "SELECT task_id, task_name, due_date, status FROM task 
             WHERE creator_id = ? ORDER BY due_date ASC LIMIT 5";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $data['tasks'][] = $row;
            $dueDate = new DateTime($row['due_date']);
            $today = new DateTime();
            $diff = $today->diff($dueDate);
            
            if ($diff->days == 0 && $diff->invert == 0) {
                $dueDateText = "Due Today";
            } elseif ($diff->days == 1 && $diff->invert == 0) {
                $dueDateText = "Due Tomorrow";
            } elseif ($diff->invert == 1) {
                $dueDateText = "Overdue";
            } else {
                $dueDateText = "Due in " . $diff->days . " days";
            }
            
            $data['tasks'][count($data['tasks']) - 1]['dueDateText'] = $dueDateText;
        }
        
        $stmt->close();
    } else {
        error_log("Task query failed: " . $conn->error);
    }
    
    // Get active goals
    $query = "SELECT goal_id, goal_title, progress, streak 
              FROM goal WHERE user_id = ? AND status = 'active' 
              ORDER BY progress DESC LIMIT 4";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $data['activeGoals'][] = [
                    'id' => $row['goal_id'],
                    'title' => $row['goal_title'],
                    'progress' => $row['progress'],
                    'streak' => $row['streak']
                ];
            }
        } else {
            error_log("Goal query execution failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Goal query preparation failed: " . $conn->error);
        $data['activeGoals'] = [
            ['id' => 1, 'title' => 'Improve coding skills', 'progress' => 75, 'streak' => 5],
            ['id' => 2, 'title' => 'Read 10 books this year', 'progress' => 40, 'streak' => 2],
            ['id' => 3, 'title' => 'Exercise 3 times per week', 'progress' => 60, 'streak' => 3],
            ['id' => 4, 'title' => 'Learn a new language', 'progress' => 25, 'streak' => 1]
        ];
    }
    
    // Get productivity stats
    $query = "SELECT COUNT(*) as total_tasks FROM task WHERE creator_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $data['productivityStats']['activeTasks'] = $row['total_tasks'];
        
        $stmt->close();
    } else {
        $data['productivityStats']['activeTasks'] = 8;
    }
    
    // Get total hours tracked today
    $today = date('Y-m-d');
    $query = "SELECT SUM(duration) as total_duration FROM tracker 
              WHERE user_id = ? AND DATE(start_time) = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $userId, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $data['productivityStats']['hoursTrackedToday'] = $row['total_duration'] ? round($row['total_duration'] / 60, 1) : 0;
        
        $stmt->close();
    } else {
        $data['productivityStats']['hoursTrackedToday'] = 3.5;
    }
    
    // Get productivity by day
    $query = "SELECT 
                LOWER(DAYNAME(created_at)) as day_name,
                COUNT(*) as task_count
              FROM task
              WHERE creator_id = ?
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              GROUP BY DAYNAME(created_at)";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $data['productivity'][substr($row['day_name'], 0, 3)] = $row['task_count'];
        }
        $stmt->close();
    } else {
        error_log("Productivity query failed: " . $conn->error);
    }
    
    // Set productivity stats
    $data['productivityStats']['goalsInProgress'] = count($data['activeGoals']);
    $data['productivityStats']['productivityScore'] = 82;
    
    return $data;
}

// Function to count active tasks
function countActiveTasks($userId, $conn) {
    $query = "SELECT COUNT(*) as count FROM task WHERE creator_id = ? AND status != 'completed'";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_assoc()['count'];
        }
    }
    return 0; // Return 0 if query fails
}

// Function to count active goals - FIXED
function countActiveGoals($userId, $conn) {
    $query = "SELECT COUNT(*) as count FROM goal WHERE user_id = ? AND status = 'active'";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_assoc()['count'];
        }
    }
    return 0; // Return 0 if query fails
}

// Function to get hours tracked today - FIXED
function getHoursTrackedToday($userId, $conn) {
    // Try with tracker table first
    $query = "SELECT COALESCE(SUM(duration)/60, 0) as hours FROM tracker 
             WHERE user_id = ? AND DATE(start_time) = CURDATE()";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc()['hours'];
        }
    }
    
    // Fall back to timetracker table if tracker query fails
    $query = "SELECT COALESCE(SUM(duration_minutes)/60, 0) as hours FROM timetracker 
             WHERE user_id = ? AND DATE(start_time) = CURDATE()";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_assoc()['hours'];
        }
    }
    
    return 0; // Return 0 if both queries fail
}

// Function to calculate productivity score - FIXED
function getProductivityScore($userId, $conn) {
    // For this example, we'll base the score on completed tasks vs. total tasks
    $query = "SELECT 
             (SELECT COUNT(*) FROM task WHERE creator_id = ? AND status = 'completed') as completed,
             (SELECT COUNT(*) FROM task WHERE creator_id = ?) as total";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $data = $result->fetch_assoc();
            if ($data['total'] > 0) {
                return round(($data['completed'] / $data['total']) * 100);
            }
        }
    }
    return 0; // Return 0 if query fails or no tasks
}

// Get current user ID
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Initialize dashboard data
$dashboardData = [];
$activeTasks = 0;
$activeGoals = 0;
$hoursTracked = 0;
$productivityScore = 0;

// Get data if user is logged in
if ($userId) {
    $dashboardData = getDashboardData($userId, $conn);
    $activeTasks = countActiveTasks($userId, $conn);
    $activeGoals = countActiveGoals($userId, $conn);
    $hoursTracked = getHoursTrackedToday($userId, $conn);
    $productivityScore = getProductivityScore($userId, $conn);
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Hub - Dashboard</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f7fc;
            color: #333;
        }
        
        /* Dashboard Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
        }
        
        .section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section h2 a {
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
        }
        
        /* Grid Layout */
        .welcome-section {
            grid-column: span 12;
            background-color: #007bff;
            color: white;
            padding: 40px 30px;
        }
        
        .welcome-section h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .welcome-section p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .quick-stats {
            grid-column: span 12;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 0;
            background: none;
            box-shadow: none;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: #555;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        
        .recent-tasks {
            grid-column: span 7;
        }
        
        .recent-goals {
            grid-column: span 5;
        }
        
        .productivity-chart {
            grid-column: span 7;
        }
        
        /* .upcoming-events {
            grid-column: span 5;
        } */
        
        /* Task List */
        .task-list {
            list-style-type: none;
        }
        
        .task-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .task-item:last-child {
            border-bottom: none;
        }
        
        .task-checkbox {
            margin-right: 15px;
            transform: scale(1.3);
        }
        
        .task-name {
            flex-grow: 1;
        }
        
        .task-date {
            color: #888;
            font-size: 14px;
        }
        
        .completed {
            text-decoration: line-through;
            color: #888;
        }
        
        /* Goal List */
        .goal-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .goal-item:last-child {
            border-bottom: none;
        }
        
        .goal-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .goal-progress {
            position: relative;
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            margin-top: 8px;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 4px;
            background-color: #4CAF50;
        }
        
        /* Productivity Chart Placeholder */
        .chart-placeholder {
            height: 250px;
            background-color: #f9f9f9;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        .bar-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            width: 100%;
            height: 100%;
            padding: 20px;
        }
        
        .bar {
            width: 40px;
            background-color: #007bff;
            border-radius: 3px 3px 0 0;
            position: relative;
        }
        
        .bar::after {
            content: attr(data-day);
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #555;
        }
        
        /* Events */
        /* .event-list {
            list-style-type: none;
        }
        
        .event-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .event-item:last-child {
            border-bottom: none;
        }
        
        .event-date {
            font-size: 14px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .event-title {
            font-weight: bold;
        }
        
        .event-description {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        } */

        .main-content {
            /* margin-left: var(--sidebar-width); */
            padding: 20px;
            transition: margin-left var(--transition-speed) ease;
            width: auto;
            box-sizing: border-box;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .recent-tasks, .recent-goals, .productivity-chart, .upcoming-events {
                grid-column: span 6;
            }
        }
        
        @media (max-width: 768px) {
            .recent-tasks, .recent-goals, .productivity-chart {
                grid-column: span 12;
            }
            
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .welcome-section {
                padding: 20px 15px;
            }
            
            .welcome-section h1 {
                font-size: 24px;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <div class="dashboard-grid">
            <div class="section welcome-section">
                <h1>Welcome to Efficio, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?>!</h1>
                <p>
                    <?php if ($userId): ?>
                        You have <?php echo $activeTasks; ?> active tasks and <?php echo $activeGoals; ?> goals in progress. Keep up the great work!
                    <?php else: ?>
                        Please log in to view your personalized dashboard.
                    <?php endif; ?>
                </p>
            </div>

            <div class="quick-stats">
                <div class="stat-card">
                    <h3>Active Tasks</h3>
                    <div class="stat-value"><?php echo $activeTasks; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Goals in Progress</h3>
                    <div class="stat-value"><?php echo $activeGoals; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Hours Tracked Today</h3>
                    <div class="stat-value"><?php echo number_format($hoursTracked, 1); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Productivity Score</h3>
                    <div class="stat-value"><?php echo $productivityScore; ?>%</div>
                </div>
            </div>

            <div class="section recent-tasks">
            <h2>Recent Tasks <a href="Workspace.php">View All</a></h2>
            <ul class="task-list">
                <?php if (empty($dashboardData['tasks'])): ?>
                    <li class="task-item">No tasks found</li>
                <?php else: ?>
                    <?php foreach ($dashboardData['tasks'] as $task): ?>
                        <li class="task-item" data-task-id="<?php echo $task['task_id']; ?>">
                            <input type="checkbox" class="task-checkbox" <?php echo ($task['status'] == 'completed') ? 'checked' : ''; ?>>
                            <span class="task-name <?php echo ($task['status'] == 'completed') ? 'completed' : ''; ?>">
                                <?php echo htmlspecialchars($task['task_name']); ?>
                            </span>
                            <span class="task-date">
                            <?php echo isset($task['dueDateText']) ? htmlspecialchars($task['dueDateText']) : ''; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="section recent-goals">
            <h2>Active Goals <a href="GoalTracking.php">View All</a></h2>
            <?php if (empty($dashboardData['activeGoals'])): ?>
                <div class="goal-item">No active goals</div>
            <?php else: ?>
                <?php foreach ($dashboardData['activeGoals'] as $goal): ?>
                    <div class="goal-item">
                        <div class="goal-title"><?php echo htmlspecialchars($goal['title']); ?></div>
                        <div class="goal-progress">
                            <div class="progress-bar" style="width: <?php echo $goal['progress']; ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

            <div class="section productivity-chart">
                <h2>Weekly Productivity -(FUTURE ENHANCEMENT, WILL BE IMPLEMENT SOON)<a href="ProductivityAnalysis.php">View Details</a></h2>
                <div class="chart-placeholder">
                    <div class="bar-container">
                        <?php
                        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        $dayData = [];

                        // Initialize with zeros
                        foreach ($days as $day) {
                            $dayData[$day] = isset($dashboardData['productivity'][$day]) ? 
                                         (int)$dashboardData['productivity'][$day] : 0;
                        }

                        // Find max value for scaling
                        $maxTasks = max($dayData) > 0 ? max($dayData) : 1;

                        // Generate bars
                        foreach ($days as $day) {
                            $height = ($dayData[$day] / $maxTasks) * 100;
                            $height = max(5, $height); // Minimum 5% height
                            echo "<div class='bar' style='height: {$height}%;' data-day='{$day}'></div>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- <div class="section upcoming-events">
                <h2>Upcoming Events <a href="C_Manager.php">View All</a></h2>
                <ul class="event-list">
                    <?php if (empty($dashboardData['events'])): ?>
                        <li class="event-item">No upcoming events</li>
                    <?php else: ?>
                        <?php foreach ($dashboardData['events'] as $event): ?>
                            <li class="event-item">
                                <div class="event-date">
                                    <?php 
                                    $eventDate = new DateTime($event['event_date']);
                                    echo $eventDate->format('F j, g:i A'); 
                                    ?>
                                </div>
                                <div class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></div>
                                <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div> -->

    <script>
        // Add interactivity for task checkboxes
        document.querySelectorAll('.task-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskName = this.nextElementSibling;
                if (this.checked) {
                    taskName.classList.add('completed');
                } else {
                    taskName.classList.remove('completed');
                }
            });
        });
        
        // Toggle sidebar expand/collapse
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
        
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Set active menu item based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
            document.querySelectorAll('.sidebar-menu-link').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>