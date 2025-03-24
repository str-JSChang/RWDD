<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Placeholder for fetching goals from database
$goals = [];
if ($isLoggedIn) {
    // This would be replaced with actual database query
    // SELECT * FROM goal WHERE user_id = ? AND status = 'active'
    $goals = [
        [
            'goal_id' => 1, 
            'goal_title' => 'Daily Exercise',
            'description' => 'Exercise for at least 30 minutes',
            'category' => 'health',
            'progress' => 75,
            'streak' => 5,
            'last_checkin' => '2025-03-23',
            'created_at' => '2025-03-01'
        ],
        [
            'goal_id' => 2, 
            'goal_title' => 'Read Books',
            'description' => 'Read for 20 minutes daily',
            'category' => 'personal',
            'progress' => 40,
            'streak' => 3,
            'last_checkin' => '2025-03-22',
            'created_at' => '2025-03-10'
        ],
        [
            'goal_id' => 3, 
            'goal_title' => 'Learn Spanish',
            'description' => 'Practice vocabulary for 15 minutes',
            'category' => 'education',
            'progress' => 25,
            'streak' => 0,
            'last_checkin' => '2025-03-20',
            'created_at' => '2025-03-15'
        ]
    ];
}

// Categories with colors for goals
$categories = [
    'health' => '#4CAF50',
    'personal' => '#2196F3',
    'education' => '#FF9800',
    'finance' => '#9C27B0',
    'career' => '#F44336',
    'other' => '#607D8B'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal Tracker | Efficio</title>
    <link rel="stylesheet" href="GoalTracking.css">
    <link rel="stylesheet" href="sidebar.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Include sidebar navigation -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="goal-header">
            <h1>Goal Tracker</h1>
            <p>Track your daily habits and long-term goals</p>
            
            <div class="add-goal-container">
                <button id="addGoalBtn" class="add-goal-btn">
                    <i class="fas fa-plus"></i> Add New Goal
                </button>
            </div>
        </div>

        <?php if (!$isLoggedIn): ?>
            <div class="login-prompt">
                <p>Please <a href="Login.php">log in</a> to track your goals.</p>
            </div>
        <?php else: ?>

            <div class="goal-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
                    <div class="stat-value"><?php echo count($goals); ?></div>
                    <div class="stat-label">Active Goals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-fire"></i></div>
                    <div class="stat-value" id="totalStreaks">8</div>
                    <div class="stat-label">Total Streaks</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-value" id="todayCheckins">1</div>
                    <div class="stat-label">Today's Check-ins</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                    <div class="stat-value" id="bestStreak">5</div>
                    <div class="stat-label">Best Streak</div>
                </div>
            </div>

            <div class="goals-container">
                <div class="filter-bar">
                    <div class="search-box">
                        <input type="text" id="searchGoal" placeholder="Search goals...">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="filter-dropdown">
                        <select id="categoryFilter">
                            <option value="all">All Categories</option>
                            <?php foreach ($categories as $cat => $color): ?>
                                <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="sort-dropdown">
                        <select id="sortGoals">
                            <option value="streak">Streak (High to Low)</option>
                            <option value="recent">Recent Check-in</option>
                            <option value="progress">Progress</option>
                            <option value="alphabetical">Alphabetical</option>
                        </select>
                    </div>
                </div>

                <div class="goals-grid" id="goalsGrid">
                    <?php foreach ($goals as $goal): ?>
                        <div class="goal-card" data-goal-id="<?php echo $goal['goal_id']; ?>" data-category="<?php echo $goal['category']; ?>">
                            <div class="goal-category" style="background-color: <?php echo $categories[$goal['category']]; ?>">
                                <?php echo ucfirst($goal['category']); ?>
                            </div>
                            <div class="goal-content">
                                <h3 class="goal-title"><?php echo $goal['goal_title']; ?></h3>
                                <p class="goal-description"><?php echo $goal['description']; ?></p>
                                
                                <div class="goal-progress-container">
                                    <div class="progress-label">
                                        <span>Progress</span>
                                        <span><?php echo $goal['progress']; ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php echo $goal['progress']; ?>%; background-color: <?php echo $categories[$goal['category']]; ?>"></div>
                                    </div>
                                </div>
                                
                                <div class="goal-stats-row">
                                    <div class="streak-counter">
                                        <i class="fas fa-fire"></i>
                                        <span><?php echo $goal['streak']; ?> day streak</span>
                                    </div>
                                    <div class="last-checkin">
                                        Last: <?php echo date('M d', strtotime($goal['last_checkin'])); ?>
                                    </div>
                                </div>
                                
                                <div class="goal-actions">
                                    <?php
                                    $today = date('Y-m-d');
                                    $canCheckIn = $goal['last_checkin'] !== $today;
                                    ?>
                                    <button class="checkin-btn <?php echo $canCheckIn ? '' : 'disabled'; ?>" 
                                            onclick="checkInGoal(<?php echo $goal['goal_id']; ?>)" 
                                            <?php echo $canCheckIn ? '' : 'disabled'; ?>>
                                        <?php echo $canCheckIn ? 'Check In Today' : 'Already Checked In'; ?>
                                    </button>
                                    <button class="details-btn" onclick="viewGoalDetails(<?php echo $goal['goal_id']; ?>)">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button class="edit-btn" onclick="editGoal(<?php echo $goal['goal_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>

        <!-- Add Goal Modal -->
        <div id="addGoalModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2>Create New Goal</h2>
                <form id="newGoalForm">
                    <div class="form-group">
                        <label for="goalTitle">Goal Title</label>
                        <input type="text" id="goalTitle" name="goalTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="goalDescription">Description</label>
                        <textarea id="goalDescription" name="goalDescription" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="goalCategory">Category</label>
                        <select id="goalCategory" name="goalCategory">
                            <?php foreach ($categories as $cat => $color): ?>
                                <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reminderTime">Daily Reminder (Optional)</label>
                        <input type="time" id="reminderTime" name="reminderTime">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="submit-btn">Create Goal</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Goal Details Modal -->
        <div id="goalDetailsModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <h2 id="detailsGoalTitle">Goal Details</h2>
                <div class="goal-details-content">
                    <div class="detail-section">
                        <h3>Goal Progress</h3>
                        <div class="progress-container">
                            <div class="progress-circle" id="detailsProgressCircle">
                                <span class="progress-text" id="detailsProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Check-in Calendar</h3>
                        <div class="calendar-view" id="detailsCalendar">
                            <!-- Calendar will be generated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Streak History</h3>
                        <div class="streak-chart" id="streakChart">
                            <!-- Streak chart will be generated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h3>Goal Stats</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-number" id="detailsTotalCheckins">0</span>
                                <span class="stat-label">Total Check-ins</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="detailsCurrentStreak">0</span>
                                <span class="stat-label">Current Streak</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="detailsLongestStreak">0</span>
                                <span class="stat-label">Longest Streak</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="detailsCompletionRate">0%</span>
                                <span class="stat-label">Completion Rate</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="GoalTracking.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>