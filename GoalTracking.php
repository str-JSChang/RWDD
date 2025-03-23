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