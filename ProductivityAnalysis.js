// Initialize analytics when the DOM content is loaded
document.addEventListener('DOMContentLoaded', function() {
    // No joking, the code below here, i spend almost 1 hour, just to identify, why is my page, loaded as Logged in? while logged out, bro, do i need more monitor? or should i just fix my eye.... i comment it because i think it is funny, i looked for sidebar.php, i look for the debugger under firefox inspect element, look at those codes, just realizing why i never check this JS here, there's too many codes, i'm freaking out too much. -Jason

    // For demo: set user as logged in
    // document.getElementById('userLoggedIn').style.display = 'flex';
    // document.getElementById('userLoggedOut').style.display = 'none';
    
    // Load and display analytics data
    updateAnalytics();
    
    // Add event listener for refresh button
    document.getElementById('refreshAnalytics').addEventListener('click', updateAnalytics);
});

// Main function to update all analytics
function updateAnalytics() {
    updateProjectTimeDistribution();
    updateProductivityChart();
}

// Update the project time distribution chart
function updateProjectTimeDistribution() {
    // In a real app, this data would come from localStorage
    // Here we'll simulate retrieving task data
    const tasks = getTasksFromStorage();
    
    // Calculate time spent on each category
    let personalTime = 0;
    let workTime = 0;
    let pythonTime = 0;
    let uilxTime = 0;
    let totalTime = 0;
    
    // Process each task to calculate time distribution
    tasks.forEach(task => {
        const timeSpent = task.timeSpent || 0;
        totalTime += timeSpent;
        
        // Categorize time based on task category
        switch(task.category) {
            case 'Personal':
                personalTime += timeSpent;
                break;
            case 'Work':
                workTime += timeSpent;
                break;
            case 'Python Project':
                pythonTime += timeSpent;
                break;
            case 'UILX Project':
                uilxTime += timeSpent;
                break;
        }
    });
    
    // Calculate percentages
    const personalPercent = totalTime > 0 ? Math.round((personalTime / totalTime) * 100) : 25;
    const workPercent = totalTime > 0 ? Math.round((workTime / totalTime) * 100) : 25;
    const pythonPercent = totalTime > 0 ? Math.round((pythonTime / totalTime) * 100) : 25;
    const uilxPercent = totalTime > 0 ? Math.round((uilxTime / totalTime) * 100) : 25;
    
    // Calculate end positions for conic gradient
    const personalEnd = personalPercent;
    const workEnd = personalEnd + workPercent;
    const pythonEnd = workEnd + pythonPercent;
    
    // Update CSS variables for the donut chart
    document.documentElement.style.setProperty('--personal-end', personalEnd + '%');
    document.documentElement.style.setProperty('--work-end', workEnd + '%');
    document.documentElement.style.setProperty('--python-end', pythonEnd + '%');
    
    // Update legend percentages
    document.getElementById('personalPercent').textContent = personalPercent + '%';
    document.getElementById('workPercent').textContent = workPercent + '%';
    document.getElementById('pythonPercent').textContent = pythonPercent + '%';
    document.getElementById('uilxPercent').textContent = uilxPercent + '%';
    
    // Update total hours
    document.getElementById('totalHours').textContent = (totalTime / 60).toFixed(1); // Convert minutes to hours
}

// Update the productivity chart
function updateProductivityChart() {
    // In a real app, this data would come from localStorage
    // Here we'll simulate retrieving completed tasks by day
    const completedTasksByDay = getCompletedTasksByDay();
    
    // Find the maximum value to scale the bars properly
    const maxTasks = Math.max(...Object.values(completedTasksByDay));
    
    // Update bar heights as percentages of the maximum
    document.getElementById('monBar').style.height = calculateBarHeight(completedTasksByDay.monday, maxTasks);
    document.getElementById('tueBar').style.height = calculateBarHeight(completedTasksByDay.tuesday, maxTasks);
    document.getElementById('wedBar').style.height = calculateBarHeight(completedTasksByDay.wednesday, maxTasks);
    document.getElementById('thuBar').style.height = calculateBarHeight(completedTasksByDay.thursday, maxTasks);
    document.getElementById('friBar').style.height = calculateBarHeight(completedTasksByDay.friday, maxTasks);
    document.getElementById('satBar').style.height = calculateBarHeight(completedTasksByDay.saturday, maxTasks);
    document.getElementById('sunBar').style.height = calculateBarHeight(completedTasksByDay.sunday, maxTasks);
    
    // Calculate total completed tasks
    const totalCompleted = Object.values(completedTasksByDay).reduce((total, count) => total + count, 0);
    document.getElementById('totalCompleted').textContent = totalCompleted;
    
    // Find most productive day
    const mostProductiveDay = findMostProductiveDay(completedTasksByDay);
    document.getElementById('mostProductiveDay').textContent = mostProductiveDay;
}

// Helper function to calculate bar height as percentage
function calculateBarHeight(value, maxValue) {
    if (maxValue === 0) return '0%';
    return Math.max(5, (value / maxValue) * 100) + '%';  // Minimum 5% height for visibility
}

// Helper function to find the most productive day
function findMostProductiveDay(completedTasksByDay) {
    let maxTasks = 0;
    let mostProductiveDay = '-';
    
    for (const [day, count] of Object.entries(completedTasksByDay)) {
        if (count > maxTasks) {
            maxTasks = count;
            // Capitalize first letter
            mostProductiveDay = day.charAt(0).toUpperCase() + day.slice(1);
        }
    }
    
    return mostProductiveDay;
}

// Function to get tasks from localStorage
function getTasksFromStorage() {
    // In a real app, this would retrieve actual tasks from localStorage
    // For demonstration, we'll return sample data
    
    // Try to get tasks from localStorage first
    const storedTasks = localStorage.getItem('tasks');
    if (storedTasks) {
        try {
            return JSON.parse(storedTasks);
        } catch (e) {
            console.error('Error parsing tasks from localStorage:', e);
        }
    }
    
    // If no tasks in localStorage or error parsing, return sample data
    return [
        { id: 1, name: 'Research content', category: 'Work', status: 'completed', timeSpent: 120 },
        { id: 2, name: 'Grocery shopping', category: 'Personal', status: 'completed', timeSpent: 60 },
        { id: 3, name: 'Create database', category: 'Python Project', status: 'completed', timeSpent: 180 },
        { id: 4, name: 'Design prototype', category: 'UILX Project', status: 'in-progress', timeSpent: 90 },
        { id: 5, name: 'Team meeting', category: 'Work', status: 'completed', timeSpent: 45 },
        { id: 6, name: 'Exercise', category: 'Personal', status: 'completed', timeSpent: 30 },
        { id: 7, name: 'Code review', category: 'Python Project', status: 'completed', timeSpent: 60 },
        { id: 8, name: 'User testing', category: 'UILX Project', status: 'pending', timeSpent: 0 }
    ];
}

// Function to get completed tasks by day
function getCompletedTasksByDay() {
    // In a real app, this would calculate based on actual task completion dates
    // For demonstration, we'll return sample data
    
    // If you have actual task data with completion dates, you would:
    // 1. Get all tasks from localStorage
    // 2. Filter for completed tasks
    // 3. Group them by day of week
    // 4. Count tasks per day
    
    return {
        monday: 5,
        tuesday: 7,
        wednesday: 3,
        thursday: 6,
        friday: 8,
        saturday: 2,
        sunday: 1
    };
}