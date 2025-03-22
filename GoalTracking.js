let goals = [];
let streakCount = 0;
let calendarData = {}; // Store check-in data for each goal


function editGoal(goal) {
    const goalName = goal.querySelector('.task-title');
    const newName = prompt("Enter new goal name:", goalName.textContent);
    if (newName) {
        goalName.textContent = newName;
    }
}

function checkIn(event, element) {
    event.stopPropagation();
    const goal = element.parentElement;
    const now = new Date();
    const today = now.toDateString();

    // Check if already checked in today
    if (goal.dataset.lastCheckIn === today) {
        alert("You have already checked in today.");
        return;
    }

    // Update last check-in date
    goal.dataset.lastCheckIn = today;

    // Update date and time
    const dateTime = goal.querySelector('.date-time');
    dateTime.textContent = `${now.getDate()}/${now.getMonth() + 1} ${now.getHours()}:${now.getMinutes()}`;

    // Update progress bar
    const progress = goal.querySelector('.progress');
    const progressPercentage = goal.querySelector('.progress-percentage');
    const currentWidth = parseFloat(progress.style.width) || 0;
    const newWidth = Math.min(100, currentWidth + 3.33); // Prevent exceeding 100%
    progress.style.width = `${newWidth}%`;
    progressPercentage.textContent = `${Math.round(newWidth)}%`;

    // Disable check-in button
    element.classList.add('checked');
    element.style.cursor = 'not-allowed';
    element.style.backgroundColor = '#ccc';

    // Update streak and calendar
    updateStreak(goal);
    updateCalendar(goal, now.getDate());
}

function updateStreak(goal) {
    const streak = goal.querySelector('.streak');
    streakCount++;
    streak.classList.toggle('active', streakCount >= 3);
}

function updateCalendar(goal, day) {
    const calendarBody = document.querySelector('.calendar-body');
    const cell = calendarBody.querySelector(`div[data-day="${day}"]`);
    if (cell) {
        cell.classList.add('checked');
        cell.classList.remove('missed');
    }
}

function closeCalendar() {
    document.querySelector('.calendar-overlay').style.display = 'none';
}

function addGoal() {
    const mainContent = document.querySelector('.main-content');
    const newGoal = document.createElement('div');
    newGoal.className = 'goal';
    newGoal.innerHTML = `
        <div class="task-header">
            <div class="task-title" onclick="editGoal(this.parentElement.parentElement)">Title</div>
            <div class="streak">Streak 🔥</div>
            <div class="pen-icon" onclick="openCalendar()">✏️</div>
            <div class="delete-goal" onclick="deleteGoal(this.parentElement.parentElement)">❌</div>
        </div>
        <div class="check-in" onclick="checkIn(event, this)">CHECK IN</div>
        <div class="date-time"></div>
        <div class="progress-bar-container">
            <div class="progress-bar-label">Progress Bar</div>
            <div class="progress-bar">
                <div class="progress" style="width: 0%;"></div>
                <div class="progress-percentage">0%</div>
            </div>
        </div>
    `;
    mainContent.appendChild(newGoal);
}

function initializeCalendar() {
    const calendarBody = document.querySelector('.calendar-body');
    for (let i = 1; i <= 31; i++) {
        const cell = document.createElement('div');
        cell.textContent = i;
        cell.setAttribute('data-day', i);
        cell.onclick = () => toggleCheckIn(cell, i);
        calendarBody.appendChild(cell);
    }
}

function toggleCheckIn(cell, day) {
    const now = new Date();
    const currentDay = now.getDate();
    if (day > currentDay) return; // Cannot check-in future dates

    const goal = document.querySelector('.goal');
    const progress = goal.querySelector('.progress');
    const progressPercentage = goal.querySelector('.progress-percentage');
    const currentWidth = parseFloat(progress.style.width) || 0;

    if (cell.classList.contains('checked')) {
        cell.classList.remove('checked');
        cell.classList.add('missed');
        progress.style.width = `${Math.max(0, currentWidth - 3.33)}%`; // Prevent negative progress
    } else {
        cell.classList.remove('missed');
        cell.classList.add('checked');
        progress.style.width = `${Math.min(100, currentWidth + 3.33)}%`;
    }

    progressPercentage.textContent = `${Math.round(parseFloat(progress.style.width))}%`;
}

function openCalendar() {
    const calendarBody = document.querySelector('.calendar-body');
    const now = new Date();
    const currentDay = now.getDate();

    calendarBody.querySelectorAll('div').forEach((cell) => {
        const day = parseInt(cell.getAttribute('data-day'), 10);
        if (day < currentDay && !cell.classList.contains('checked')) {
            cell.classList.add('missed');
        }
    });

    document.querySelector('.calendar-overlay').style.display = 'flex';
}

function deleteGoal(goal) {
    goal.remove();
}

initializeCalendar();
