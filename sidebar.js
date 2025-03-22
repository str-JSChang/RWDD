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
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.html';
    document.querySelectorAll('.sidebar-menu-link').forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
    
    // Check if user is logged in (you can replace this with your actual auth check)
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    document.getElementById('userLoggedIn').style.display = isLoggedIn ? 'flex' : 'none';
    document.getElementById('userLoggedOut').style.display = isLoggedIn ? 'none' : 'flex';
});