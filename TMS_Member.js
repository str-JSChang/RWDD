// Simulated backend data (JSON format)
let tasks = [
    { task: "Design UI", owner: "Alice", progress: 75, dueDate: "20/03/2025 14:30" },
    { task: "Write Docs", owner: "Bob", progress: 100, dueDate: "18/03/2025 10:00" },
    { task: "Fix Bugs", owner: "Charlie", progress: 50, dueDate: "19/03/2025 16:00" }
];

function loadTasks() {
    let table = document.getElementById("taskTable");
    tasks.forEach(task => {
        let row = table.insertRow();
        row.innerHTML = `
            <td>${task.task}</td>
            <td>${task.owner}</td>
            <td>
                <span class="progress-value">${task.progress}%</span>
                <progress value="${task.progress}" max="100"></progress>
            </td>
            <td>
                ${task.dueDate}
                <div class="due-reminder"></div>
            </td>
        `;
        updateDueDateReminder(row, task.dueDate, task.progress);
    });
}

function updateDueDateReminder(row, dueDateStr, progress) {
    let dueReminder = row.querySelector(".due-reminder");
    let dueDate = parseDateTime(dueDateStr);
    let now = new Date();
    let timeDiff = dueDate - now;

    let days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    let hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));

    if (progress === 100) {
        dueReminder.textContent = "Completed";
        dueReminder.classList.add("completed");
    } else if (timeDiff > 0) {
        dueReminder.textContent = `Remaining: ${days} days, ${hours} hours, ${minutes} minutes`;
        dueReminder.classList.remove("overdue");
    } else {
        dueReminder.textContent = `Overdue by ${Math.abs(days)} days, ${Math.abs(hours)} hours, ${Math.abs(minutes)} minutes`;
        dueReminder.classList.add("overdue");
    }
}

function parseDateTime(dateTimeStr) {
    let parts = dateTimeStr.split(" ");
    let dateParts = parts[0].split("/");
    let timeParts = parts[1].split(":");

    let day = parseInt(dateParts[0], 10);
    let month = parseInt(dateParts[1], 10) - 1;
    let year = parseInt(dateParts[2], 10);
    let hours = parseInt(timeParts[0], 10);
    let minutes = parseInt(timeParts[1], 10);

    return new Date(year, month, day, hours, minutes);
}

// Auto-update due date reminders every 1 minute
setInterval(() => {
    document.querySelectorAll("#taskTable tr").forEach((row, index) => {
        if (index > 0) {
            updateDueDateReminder(row, tasks[index - 1].dueDate, tasks[index - 1].progress);
        }
    });
}, 60000);

// Load tasks on page load
window.onload = loadTasks;
