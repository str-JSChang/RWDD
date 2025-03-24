<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Initialize data arrays
$timeDistribution = [];
$productivityData = [];

if ($userId) {
    // Fetch time distribution data
    $query = "SELECT 
                c.category_name, 
                SUM(t.duration) as total_duration 
              FROM tracker t
              JOIN task task ON t.task_id = task.task_id
              JOIN task_categories c ON task.category_id = c.category_id
              WHERE t.user_id = ?
              GROUP BY c.category_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $timeDistribution[] = $row;
    }
    
    // Fetch productivity data by day
    $query = "SELECT 
                day_of_week as day_name,
                tasks_completed as task_count,
                hours_tracked * 60 as total_duration
              FROM stat
              WHERE user_id = ?
              ORDER BY 
                CASE day_name 
                    WHEN 'Mon' THEN 1 
                    WHEN 'Tue' THEN 2 
                    WHEN 'Wed' THEN 3 
                    WHEN 'Thu' THEN 4 
                    WHEN 'Fri' THEN 5 
                    WHEN 'Sat' THEN 6 
                    WHEN 'Sun' THEN 7 
                END";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $productivityData[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Analysis | Efficio</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="ProductivityAnalysis.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="productivity-analysis-container">
            <h1>Productivity Analysis</h1>

            <?php if (!$isLoggedIn): ?>
                <div class="login-prompt">
                    <p>Please <a href="Login.php">log in</a> to view your productivity insights.</p>
                </div>
            <?php else: ?>
                <div class="analysis-grid">
                    <div class="analysis-card time-distribution">
                        <h2>Project Time Distribution</h2>
                        <div class="chart-container">
                            <canvas id="timeDistributionChart"></canvas>
                        </div>
                    </div>

                    <div class="analysis-card productivity-chart">
                        <h2>Weekly Productivity - (Future enhancement, will be implemented soon)</h2>
                        <div class="chart-container">
                            <canvas id="productivityChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="detailed-insights">
                    <div class="insights-card">
                        <h2>Detailed Insights</h2>
                        <div class="insights-grid">
                            <div class="insight-item">
                                <h3>Total Tracked Time</h3>
                                <p id="totalTrackedTime">0 hrs</p>
                            </div>
                            <div class="insight-item">
                                <h3>Most Productive Day</h3>
                                <p id="mostProductiveDay">-</p>
                            </div>
                            <div class="insight-item">
                                <h3>Avg. Daily Productivity</h3>
                                <p id="avgDailyProductivity">0%</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const timeDistributionData = <?php echo json_encode($timeDistribution); ?>;
        const productivityData = <?php echo json_encode($productivityData); ?>;
    </script>
    <script src="ProductivityAnalysis.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>