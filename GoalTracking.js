// Global variables
let goals = []; // Will store goal data

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners
    setupEventListeners();
    
    // Load goals - In a real implementation, this would fetch from the server
    loadGoals();
    
    // Initialize filter functionality
    initializeFilters();
});

// Set up all event listeners
function setupEventListeners() {
    // Add goal button
    const addGoalBtn = document.getElementById('addGoalBtn');
    if (addGoalBtn) {
        addGoalBtn.addEventListener('click', () => openModal('addGoalModal'));
    }
    
    // Form submission
    const newGoalForm = document.getElementById('newGoalForm');
    if (newGoalForm) {
        newGoalForm.addEventListener('submit', handleNewGoalSubmit);
    }
    
    // Close buttons for modals
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => closeModal());
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal')) {
            closeModal();
        }
    });
}

// Initialize filter and search functionality
function initializeFilters() {
    const searchInput = document.getElementById('searchGoal');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortSelect = document.getElementById('sortGoals');
    
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', applyFilters);
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', applyFilters);
    }
}

// Apply filters to goals list
function applyFilters() {
    const searchInput = document.getElementById('searchGoal');
    const categoryFilter = document.getElementById('categoryFilter');
    const sortSelect = document.getElementById('sortGoals');
    const goalsGrid = document.getElementById('goalsGrid');
    
    if (!goalsGrid) return;
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const selectedCategory = categoryFilter ? categoryFilter.value : 'all';
    const sortBy = sortSelect ? sortSelect.value : 'streak';
    
    // Get all goal cards
    const goalCards = goalsGrid.querySelectorAll('.goal-card');
    
    // Filter and sort
    goalCards.forEach(card => {
        // Search filter
        const title = card.querySelector('.goal-title').textContent.toLowerCase();
        const description = card.querySelector('.goal-description').textContent.toLowerCase();
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        
        // Category filter
        const category = card.dataset.category;
        const matchesCategory = selectedCategory === 'all' || category === selectedCategory;
        
        // Show/hide based on filters
        if (matchesSearch && matchesCategory) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Sort visible goals
    const visibleCards = Array.from(goalCards).filter(card => card.style.display !== 'none');
    
    sortGoalCards(visibleCards, sortBy);
    
    // Re-append sorted cards
    visibleCards.forEach(card => goalsGrid.appendChild(card));
}

// Sort goal cards based on selected sort option
function sortGoalCards(cards, sortBy) {
    cards.sort((a, b) => {
        switch (sortBy) {
            case 'streak':
                const streakA = parseInt(a.querySelector('.streak-counter span').textContent.split(' ')[0]);
                const streakB = parseInt(b.querySelector('.streak-counter span').textContent.split(' ')[0]);
                return streakB - streakA; // Descending
                
            case 'recent':
                const lastA = a.querySelector('.last-checkin').textContent.substring(6);
                const lastB = b.querySelector('.last-checkin').textContent.substring(6);
                return new Date(lastB) - new Date(lastA); // Descending
                
            case 'progress':
                const progressA = parseInt(a.querySelector('.progress-label span:last-child').textContent);
                const progressB = parseInt(b.querySelector('.progress-label span:last-child').textContent);
                return progressB - progressA; // Descending
                
            case 'alphabetical':
                const titleA = a.querySelector('.goal-title').textContent.toLowerCase();
                const titleB = b.querySelector('.goal-title').textContent.toLowerCase();
                return titleA.localeCompare(titleB); // Ascending
                
            default:
                return 0;
        }
    });
}

// Load goals from the server
function loadGoals() {
    // Show loading indicator
    const goalsGrid = document.getElementById('goalsGrid');
    if (goalsGrid) {
        goalsGrid.innerHTML = '<div class="loading-spinner">Loading your goals...</div>';
    }
    
    // Fetch goals from the server with current filters
    const categoryFilter = document.getElementById('categoryFilter');
    const sortSelect = document.getElementById('sortGoals');
    
    const category = categoryFilter ? categoryFilter.value : 'all';
    const sort = sortSelect ? sortSelect.value : 'streak';
    
    let url = `get_goals.php?sort=${sort}`;
    if (category !== 'all') {
        url += `&category=${category}`;
    }
    
    fetch(url)
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                goals = data.goals;
                
                // Update stats
                document.getElementById('totalStreaks').textContent = data.stats.totalStreaks;
                document.getElementById('bestStreak').textContent = data.stats.bestStreak;
                document.getElementById('todayCheckins').textContent = data.stats.todayCheckins;
                
                // Render goals
                renderGoals();
            } else {
                // Log the error message
                console.error('Server error:', data.message);
                
                if (goalsGrid) {
                    goalsGrid.innerHTML = `<div class="error-message">Error: ${data.message || 'Unknown server error'}</div>`;
                }
            }
        })
        .catch(error => {
            // Log the full error
            console.error('Fetch error:', error);
            
            if (goalsGrid) {
                goalsGrid.innerHTML = `<div class="error-message">Failed to load goals: ${error.message}</div>`;
            }
        });
}

// Render goals to the page
function renderGoals() {
    const goalsGrid = document.getElementById('goalsGrid');
    if (!goalsGrid) return;
    
    if (goals.length === 0) {
        goalsGrid.innerHTML = '<div class="no-goals">No goals found. Click "Add New Goal" to get started!</div>';
        return;
    }
    
    goalsGrid.innerHTML = '';
    
    // Get category colors from PHP (fallback to defaults if not available)
    const categoryColors = {
        'health': '#4CAF50',
        'personal': '#2196F3',
        'education': '#FF9800',
        'finance': '#9C27B0',
        'career': '#F44336',
        'other': '#607D8B'
    };
    
    // Render each goal card
    goals.forEach(goal => {
        const color = categoryColors[goal.category] || categoryColors.other;
        
        const goalCard = document.createElement('div');
        goalCard.className = 'goal-card';
        goalCard.dataset.goalId = goal.id;
        goalCard.dataset.category = goal.category;
        
        goalCard.innerHTML = `
            <div class="goal-category" style="background-color: ${color}">
                ${goal.category.charAt(0).toUpperCase() + goal.category.slice(1)}
            </div>
            <div class="goal-content">
                <h3 class="goal-title">${goal.title}</h3>
                <p class="goal-description">${goal.description}</p>
                
                <div class="goal-progress-container">
                    <div class="progress-label">
                        <span>Progress</span>
                        <span>${goal.progress}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress" style="width: ${goal.progress}%; background-color: ${color}"></div>
                    </div>
                </div>
                
                <div class="goal-stats-row">
                    <div class="streak-counter">
                        <i class="fas fa-fire"></i>
                        <span>${goal.streak} day streak</span>
                    </div>
                    <div class="last-checkin">
                        Last: ${goal.lastCheckin}
                    </div>
                </div>
                
                <div class="goal-actions">
                    <button class="checkin-btn ${goal.canCheckIn ? '' : 'disabled'}" 
                            onclick="checkInGoal(${goal.id})" 
                            ${goal.canCheckIn ? '' : 'disabled'}>
                        ${goal.canCheckIn ? 'Check In Today' : 'Already Checked In'}
                    </button>
                    <button class="details-btn" onclick="viewGoalDetails(${goal.id})">
                        <i class="fas fa-chart-line"></i>
                    </button>
                    <button class="edit-btn" onclick="editGoal(${goal.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="delete-btn" onclick="deleteGoal(${goal.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        goalsGrid.appendChild(goalCard);
    });
    
    // Apply any active filters
    applyFilters();
}

// Update goal statistics display
function updateGoalStats() {
    // Calculate totals
    const totalStreaks = goals.reduce((sum, goal) => sum + goal.streak, 0);
    const bestStreak = goals.length > 0 ? Math.max(...goals.map(goal => goal.streak)) : 0;
    
    // Count today's check-ins
    const today = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const todayCheckins = goals.filter(goal => {
        return goal.lastCheckin === today;
    }).length;
    
    // Update DOM
    document.getElementById('totalStreaks').textContent = totalStreaks;
    document.getElementById('bestStreak').textContent = bestStreak;
    document.getElementById('todayCheckins').textContent = todayCheckins;
}

// Open a modal dialog
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

// Close any open modal
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.style.display = 'none';
    });
}

// Handle new goal form submission
function handleNewGoalSubmit(event) {
    event.preventDefault();
    
    // Get form values
    const title = document.getElementById('goalTitle').value;
    const description = document.getElementById('goalDescription').value;
    const category = document.getElementById('goalCategory').value;
    const reminderTime = document.getElementById('reminderTime').value;
    
    // Validate form
    if (!title.trim()) {
        showNotification('Please enter a goal title', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('.submit-btn');
    const cancelBtn = event.target.querySelector('.cancel-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';
    }
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('category', category);
    if (reminderTime) {
        formData.append('reminderTime', reminderTime);
    }
    
    // Send to server
    fetch('add_goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            closeModal();
            
            // Show success message
            showNotification('Goal created successfully!', 'success');
            
            // Reload goals to show the new one
            loadGoals();
            
            // Reset form
            document.getElementById('newGoalForm').reset();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error creating goal:', error);
        showNotification('Failed to create goal. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button states
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Goal';
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
    });
}

// Check in for a goal
function checkInGoal(goalId) {
    // Disable the check-in button during the request to prevent multiple check-ins
    const goalCard = document.querySelector(`.goal-card[data-goal-id="${goalId}"]`);
    if (!goalCard) return;
    
    const checkinBtn = goalCard.querySelector('.checkin-btn');
    checkinBtn.disabled = true;
    checkinBtn.textContent = 'Checking in...';
    
    // Send check-in request to the server
    fetch(`check_in_goal.php?goal_id=${goalId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI with the response data
            updateGoalUI(goalId, data);
            
            // Show success message
            showNotification('Check-in successful!', 'success');
            
            // Update goal stats
            updateGoalStats();
        } else {
            // Show error message
            showNotification('Error: ' + data.message, 'error');
            
            // Re-enable button
            checkinBtn.disabled = false;
            checkinBtn.textContent = 'Check In Today';
        }
    })
    .catch(error => {
        console.error('Error checking in:', error);
        showNotification('Failed to check in. Please try again.', 'error');
        
        // Re-enable button
        checkinBtn.disabled = false;
        checkinBtn.textContent = 'Check In Today';
    });
}

// Update goal UI after check-in
function updateGoalUI(goalId, data) {
    const goalCard = document.querySelector(`.goal-card[data-goal-id="${goalId}"]`);
    if (!goalCard) return;
    
    // Update streak
    const streakCounter = goalCard.querySelector('.streak-counter span');
    streakCounter.textContent = `${data.streak} day streak`;
    
    // Update progress
    const progressLabel = goalCard.querySelector('.progress-label span:last-child');
    const progressBar = goalCard.querySelector('.progress');
    progressLabel.textContent = `${data.progress}%`;
    progressBar.style.width = `${data.progress}%`;
    
    // Update last check-in date
    const lastCheckin = goalCard.querySelector('.last-checkin');
    const formattedDate = new Date(data.last_checkin).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    lastCheckin.textContent = `Last: ${formattedDate}`;
    
    // Update check-in button
    const checkinBtn = goalCard.querySelector('.checkin-btn');
    checkinBtn.textContent = 'Already Checked In';
    checkinBtn.classList.add('disabled');
    checkinBtn.disabled = true;
    
    // Update the goal in our goals array
    const index = goals.findIndex(g => g.id === goalId);
    if (index !== -1) {
        goals[index].streak = data.streak;
        goals[index].progress = data.progress;
        goals[index].lastCheckin = formattedDate;
        goals[index].canCheckIn = false;
    }
}

// Show notification message
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        document.body.appendChild(notification);
        
        // Add styles to notification
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.padding = '12px 20px';
        notification.style.borderRadius = '4px';
        notification.style.color = 'white';
        notification.style.fontWeight = 'bold';
        notification.style.zIndex = '1000';
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
    }
    
    // Set notification style based on type
    switch (type) {
        case 'success':
            notification.style.backgroundColor = '#4CAF50';
            break;
        case 'error':
            notification.style.backgroundColor = '#F44336';
            break;
        case 'warning':
            notification.style.backgroundColor = '#FF9800';
            break;
        default:
            notification.style.backgroundColor = '#2196F3';
    }
    
    // Set notification message
    notification.textContent = message;
    
    // Show notification
    setTimeout(() => {
        notification.style.opacity = '1';
    }, 10);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
    }, 3000);
}

// View details for a goal
function viewGoalDetails(goalId) {
    // Store the current goal ID
    currentGoalId = goalId;
    
    // Find the goal data
    const goal = goals.find(g => g.id === goalId);
    if (!goal) return;
    
    // Update modal title
    document.getElementById('detailsGoalTitle').textContent = goal.title;
    
    // Show loading indicator in modal
    const modalContent = document.querySelector('#goalDetailsModal .goal-details-content');
    if (modalContent) {
        modalContent.innerHTML = '<div class="loading-spinner">Loading goal details...</div>';
    }
    
    // Open the modal while data is loading
    openModal('goalDetailsModal');
    
    // Fetch goal details from the server
    fetch(`get_goal_details.php?goal_id=${goalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate details
                populateGoalDetails(data.goalDetails);
            } else {
                showNotification('Error: ' + data.message, 'error');
                if (modalContent) {
                    modalContent.innerHTML = `<div class="error-message">Failed to load goal details: ${data.message}</div>`;
                }
            }
        })
        .catch(error => {
            console.error('Error fetching goal details:', error);
            showNotification('Failed to load goal details', 'error');
            if (modalContent) {
                modalContent.innerHTML = '<div class="error-message">Failed to load goal details. Please try again.</div>';
            }
        });
}

// Populate goal details in the modal
function populateGoalDetails(details) {
    // Set progress circle
    const progressCircle = document.getElementById('detailsProgressCircle');
    progressCircle.style.setProperty('--progress', `${details.progress}%`);
    document.getElementById('detailsProgressText').textContent = `${details.progress}%`;
    
    // Set stats
    document.getElementById('detailsTotalCheckins').textContent = details.totalCheckins;
    document.getElementById('detailsCurrentStreak').textContent = details.currentStreak;
    document.getElementById('detailsLongestStreak').textContent = details.longestStreak;
    document.getElementById('detailsCompletionRate').textContent = `${details.completionRate}%`;
    
    // Generate calendar
    generateCalendarView(details.calendar);
    
    // Generate streak history chart
    generateStreakChart(details.streakHistory);
}

// Generate calendar view for check-ins
function generateCalendarView(calendarData) {
    const calendarEl = document.getElementById('detailsCalendar');
    if (!calendarEl) return;
    
    calendarEl.innerHTML = '';
    
    // Add day labels (Mon, Tue, etc.)
    const dayLabels = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
    for (let i = 0; i < 7; i++) {
        const dayLabel = document.createElement('div');
        dayLabel.className = 'calendar-day day-label';
        dayLabel.textContent = dayLabels[i];
        calendarEl.appendChild(dayLabel);
    }
    
    // Add placeholder days to align with correct weekday
    const firstDay = calendarData[calendarData.length - 1].date;
    const dayOfWeek = firstDay.getDay();
    
    for (let i = 0; i < dayOfWeek; i++) {
        const placeholder = document.createElement('div');
        placeholder.className = 'calendar-day placeholder';
        calendarEl.appendChild(placeholder);
    }
    
    // Add calendar days in reverse (most recent first)
    for (let i = calendarData.length - 1; i >= 0; i--) {
        const day = calendarData[i];
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';
        dayEl.textContent = day.date.getDate();
        
        // Add classes based on check-in status
        if (day.checked) {
            dayEl.classList.add('checked');
        } else {
            // Only mark as missed if it's in the past
            if (day.date < new Date()) {
                dayEl.classList.add('missed');
            }
        }
        
        // Mark today's date
        if (day.date.toDateString() === new Date().toDateString()) {
            dayEl.classList.add('today');
        }
        
        calendarEl.appendChild(dayEl);
    }
}

// Generate streak history chart
function generateStreakChart(streakHistory) {
    const chartEl = document.getElementById('streakChart');
    if (!chartEl) return;
    
    chartEl.innerHTML = '';
    
    // Find max streak for scaling
    const maxStreak = Math.max(...streakHistory, 1);
    
    // Create bars for each week
    streakHistory.forEach((streak, index) => {
        const bar = document.createElement('div');
        bar.className = 'streak-bar';
        // Calculate height percentage (minimum 5% for visibility)
        const heightPercentage = Math.max(5, (streak / maxStreak) * 100);
        bar.style.height = `${heightPercentage}%`;
        
        // Add week label on hover
        bar.title = `Week ${streakHistory.length - index}: ${streak} days`;
        
        chartEl.appendChild(bar);
    });
}

// Edit a goal
function editGoal(goalId) {
    // Check if the edit modal exists, create it if it doesn't
    createEditModalIfNeeded();
    
    // Find the goal data
    const goal = goals.find(g => g.id === goalId);
    if (!goal) {
        showNotification('Goal not found', 'error');
        return;
    }
    
    // Populate edit form with goal data
    const editForm = document.getElementById('editGoalForm');
    const titleInput = document.getElementById('editGoalTitle');
    const descriptionInput = document.getElementById('editGoalDescription');
    const categorySelect = document.getElementById('editGoalCategory');
    const statusSelect = document.getElementById('editGoalStatus');
    const reminderTimeInput = document.getElementById('editReminderTime');
    
    if (titleInput) titleInput.value = goal.title;
    if (descriptionInput) descriptionInput.value = goal.description;
    if (categorySelect) categorySelect.value = goal.category;
    if (statusSelect) statusSelect.value = goal.status || 'active';
    if (reminderTimeInput) reminderTimeInput.value = goal.reminderTime || '';
    
    // Set goal ID in the form for submission
    if (editForm) editForm.dataset.goalId = goalId;
    
    // Open the modal
    openModal('editGoalModal');
}

// Create edit modal if it doesn't exist
function createEditModalIfNeeded() {
    if (document.getElementById('editGoalModal')) {
        return; // Modal already exists
    }
    
    // Get category options from the add goal form
    const categoryOptions = Array.from(document.getElementById('goalCategory').options)
        .map(option => `<option value="${option.value}">${option.textContent}</option>`)
        .join('');
    
    // Create the modal
    const modal = document.createElement('div');
    modal.id = 'editGoalModal';
    modal.className = 'modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Edit Goal</h2>
            <form id="editGoalForm">
                <div class="form-group">
                    <label for="editGoalTitle">Goal Title</label>
                    <input type="text" id="editGoalTitle" name="editGoalTitle" required>
                </div>
                <div class="form-group">
                    <label for="editGoalDescription">Description</label>
                    <textarea id="editGoalDescription" name="editGoalDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="editGoalCategory">Category</label>
                    <select id="editGoalCategory" name="editGoalCategory">
                        ${categoryOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label for="editGoalStatus">Status</label>
                    <select id="editGoalStatus" name="editGoalStatus">
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editReminderTime">Daily Reminder (Optional)</label>
                    <input type="time" id="editReminderTime" name="editReminderTime">
                </div>
                <div class="form-actions">
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Changes</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Add event listeners
    const closeBtn = modal.querySelector('.close-modal');
    closeBtn.addEventListener('click', closeModal);
    
    const form = document.getElementById('editGoalForm');
    form.addEventListener('submit', handleEditGoalSubmit);
}

function deleteGoal(goalId) {
    // Show confirmation dialog
    const confirmDelete = confirm('Are you sure you want to delete this goal? This action cannot be undone.');
    
    if (!confirmDelete) return;
    
    // Show loading state
    const goalCard = document.querySelector(`.goal-card[data-goal-id="${goalId}"]`);
    if (goalCard) {
        goalCard.style.opacity = '0.5';
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('goal_id', goalId);
    
    // Send delete request
    fetch('delete_goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove goal from local array
            goals = goals.filter(goal => goal.id !== goalId);
            
            // Remove goal card from DOM
            if (goalCard) {
                goalCard.remove();
            }
            
            // Show success notification
            showNotification('Goal deleted successfully!', 'success');
            
            // Update goal stats
            updateGoalStats();
        } else {
            // Show error message
            showNotification(`Error: ${data.message}`, 'error');
            
            // Restore opacity
            if (goalCard) {
                goalCard.style.opacity = '1';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to delete goal', 'error');
        
        // Restore opacity
        if (goalCard) {
            goalCard.style.opacity = '1';
        }
    });
}

// Handle edit goal form submission
function handleEditGoalSubmit(event) {
    event.preventDefault();
    
    // Get goal ID
    const goalId = event.target.dataset.goalId;
    if (!goalId) {
        showNotification('Goal ID not found', 'error');
        return;
    }
    
    // Get form values
    const title = document.getElementById('editGoalTitle').value;
    const description = document.getElementById('editGoalDescription').value;
    const category = document.getElementById('editGoalCategory').value;
    const status = document.getElementById('editGoalStatus').value;
    const reminderTime = document.getElementById('editReminderTime').value;
    
    // Validate form
    if (!title.trim()) {
        showNotification('Please enter a goal title', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('.submit-btn');
    const cancelBtn = event.target.querySelector('.cancel-btn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
    }
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('goal_id', goalId);
    formData.append('title', title);
    formData.append('description', description);
    formData.append('category', category);
    formData.append('status', status);
    if (reminderTime) {
        formData.append('reminderTime', reminderTime);
    }
    
    // Send to server
    fetch('edit_goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            closeModal();
            
            // Show success message
            showNotification('Goal updated successfully!', 'success');
            
            // Reload goals
            loadGoals();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating goal:', error);
        showNotification('Failed to update goal. Please try again.', 'error');
    })
    .finally(() => {
        // Reset button states
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Changes';
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
    });
}