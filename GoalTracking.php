<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal Tracker</title>
    <link rel="stylesheet" href="GoalTracking.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
            <header>

                <div class="add-goal-container">
                    <div class="add-goal-background"></div>
                    <div class="add-goal" onclick="addGoal()">ADD GOAL</div>
                </div>
            </header>

            <div class="main-content">
                <!-- Goals will be dynamically added here -->
            </div>
            <div class="calendar-overlay">
                <div class="calendar">
                    <div class="calendar-header">
                        <span class="close" onclick="closeCalendar()">&times;</span>
                        <h2>Calendar</h2>
                    </div>
                    <div class="calendar-body"></div>
                </div>
            </div>
    </div>

    <script src="GoalTracking.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>