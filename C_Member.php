<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaboration Page - Member</title>
    <link rel="stylesheet" href="C_Member.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    
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
            <div class="container">
                <h2>Collaboration Page - Member</h2>

                <div class="section">
                    <h3>Discussion & File Sharing</h3>
                    <textarea id="commentInput" placeholder="Write a comment..."></textarea>
                    <button onclick="addComment()">Post Comment</button>
                    <ul class="list" id="commentList"></ul>

                    <input type="file" id="fileUpload">
                    <button onclick="uploadFile()">Upload File</button>
                    <ul class="list" id="fileList"></ul>
                </div>

                <div class="section">
                    <h3>Task Management (View Only)</h3>
                    <ul class="list readonly" id="taskList">
                        <li>Task 1 - In Progress</li>
                        <li>Task 2 - Completed</li>
                    </ul>
                </div>

                <div class="section">
                    <h3>Team Members (View Only)</h3>
                    <ul class="list readonly" id="memberList">
                        <li>Member A</li>
                        <li>Member B</li>
                    </ul>
                </div>

                <div class="section">
                    <h3>Scheduled Meeting (View Only)</h3>
                    <p id="meetingScheduled">Meeting scheduled for: 20/03/2025 10:00 AM</p>
                </div>

            </div>
        </div>

    <script src="C_Member.js"></script>
    <script src="sidebar.js"></script>

</body>
</html>
