<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Analysis</title>
    <link rel="stylesheet" href="ProductivityAnalysis.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <!-- Mobile menu toggle button -->
    
    <!-- Sidebar navigation -->
    <div class="sidebar" id="sidebar">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span class="sidebar-logo-icon">🚀</span>
                <span class="sidebar-logo-text">Productivity Hub</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">◀</button>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="dashboard.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">📊</span>
                    <span class="sidebar-menu-text">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="GoalTracking.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">🎯</span>
                    <span class="sidebar-menu-text">Goal Tracking</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="Workspace.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">📝</span>
                    <span class="sidebar-menu-text">Task Workspace</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="Time Tracker.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">⏱️</span>
                    <span class="sidebar-menu-text">Time Tracker</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="ProductivityAnalysis.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">📈</span>
                    <span class="sidebar-menu-text">Analytics</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="C_Manager.php" class="sidebar-menu-link">
                    <span class="sidebar-menu-icon">👥</span>
                    <span class="sidebar-menu-text">Collaboration</span>
                </a>
            </li>
        </ul>
        
        <!-- User profile section (for logged in users) -->
        <div class="sidebar-user" id="userLoggedIn" style="display: none;">
            <div class="user-avatar">JS</div>
            <div class="user-info">
                <div class="user-name">John Smith</div>
                <div class="user-status">Premium Member</div>
                <a href="Login.php" class="user-logout">Logout</a>
            </div>
        </div>
        
        <!-- Login button (for guests) -->
        <div class="sidebar-user" id="userLoggedOut">
            <a href="Login.php" class="login-btn">
                <span class="login-icon">🔑</span>
                <span class="login-text">Login / Sign Up</span>
            </a>
        </div>
    </div>

    <!-- Main content -->
    <div class="main-content" id="mainContent">
        <div class="analysis-container">
            <!-- Refresh button to update analytics data -->
            <button id="refreshAnalytics" class="refresh-button">Refresh Analytics</button>
            
            <!-- Project Time Distribution using CSS-based donut chart -->
            <div class="analysis-card">
                <h2>Project Time Distribution</h2>
                <div class="chart-container">
                    <!-- CSS-based donut chart instead of canvas -->
                    <div class="css-donut-chart" id="projectTimeChart">
                        <!-- The donut chart is created using CSS, no segments needed in HTML -->
                        <div class="donut-center">
                            <!-- Center content can show total hours -->
                            <span id="totalHours">0</span>
                            <span class="unit">hrs</span>
                        </div>
                    </div>
                    
                    <!-- Legend with dynamic percentages -->
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color personal-color"></span>
                            <span class="legend-label">Personal Tasks</span>
                            <span class="legend-percent" id="personalPercent">0%</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color work-color"></span>
                            <span class="legend-label">Work Tasks</span>
                            <span class="legend-percent" id="workPercent">0%</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color python-color"></span>
                            <span class="legend-label">Python Project</span>
                            <span class="legend-percent" id="pythonPercent">0%</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color uilx-color"></span>
                            <span class="legend-label">UILX Project</span>
                            <span class="legend-percent" id="uilxPercent">0%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productivity Chart using CSS bars -->
            <div class="analysis-card">
                <h2>Productivity Chart</h2>
                <div class="css-bar-chart-container">
                    <!-- Days of the week with CSS bars -->
                    <div class="chart-column">
                        <div class="chart-bar" id="monBar"></div>
                        <div class="chart-label">Mon</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="tueBar"></div>
                        <div class="chart-label">Tue</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="wedBar"></div>
                        <div class="chart-label">Wed</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="thuBar"></div>
                        <div class="chart-label">Thu</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="friBar"></div>
                        <div class="chart-label">Fri</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="satBar"></div>
                        <div class="chart-label">Sat</div>
                    </div>
                    <div class="chart-column">
                        <div class="chart-bar" id="sunBar"></div>
                        <div class="chart-label">Sun</div>
                    </div>
                </div>
                
                <!-- Legend for bar chart -->
                <div class="bar-legend">
                    <div class="legend-item">
                        <span class="legend-color tasks-color"></span>
                        <span class="legend-label">Tasks Completed</span>
                    </div>
                </div>
                
                <!-- Summary stats -->
                <div class="productivity-summary">
                    <div class="summary-item">
                        <span class="summary-label">Total Completed This Week:</span>
                        <span class="summary-value" id="totalCompleted">0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Most Productive Day:</span>
                        <span class="summary-value" id="mostProductiveDay">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="ProductivityAnalysis.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>