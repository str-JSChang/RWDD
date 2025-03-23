<?php
// Check if session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$username = $isLoggedIn ? $_SESSION['username'] : '';
$userId = $isLoggedIn ? $_SESSION['user_id'] : '';
$userInitials = $isLoggedIn ? substr($username, 0, 2) : '';
?>

<!-- Sidebar navigation -->
<div class="sidebar" id="sidebar">
    <button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="sidebar-logo-icon">🚀</span>
            <span class="sidebar-logo-text">Efficio</span>
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
    <div class="sidebar-user" id="userLoggedIn" style="display: <?php echo $isLoggedIn ? 'flex' : 'none'; ?>;">
        <div class="user-avatar"><?php echo htmlspecialchars($userInitials); ?></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
            <a href="logout.php" class="user-logout">Logout</a>
        </div>
    </div>
    
    <!-- Login button (for guests) -->
    <div class="sidebar-user" id="userLoggedOut" style="display: <?php echo $isLoggedIn ? 'none' : 'flex'; ?>;">
        <a href="Login.php" class="login-btn">
            <span class="login-icon">🔑</span>
            <span class="login-text">Login / Sign Up</span>
        </a>
    </div>
</div>