document.getElementById("assignTask").addEventListener("click", addTask);

function addTask() {
    let taskName = document.getElementById("taskName").value.trim();
    let ownerName = document.getElementById("ownerName").value.trim();
    let priority = document.getElementById("taskPriority").value;
    let dueDateInput = document.getElementById("dueDate").value;

    if (!taskName || !ownerName || !dueDateInput) {
        alert("Please fill in all fields before assigning a task.");
        return;
    }

    let dueDate = new Date(dueDateInput);
    dueDate.setSeconds(0);
    dueDate.setMilliseconds(0);

    let now = new Date();
    now.setSeconds(0);
    now.setMilliseconds(0);

    let timeDiff = dueDate.getTime() - now.getTime();
    
    let overdueText = "";
    if (timeDiff < 0) {
        overdueText = `<span class="overdue">Overdue by ${Math.abs(Math.floor(timeDiff / (1000 * 60 * 60 * 24)))} days</span>`;
    } else if (timeDiff === 0) {
        overdueText = `<span class="due-today">Due Today</span>`;
    } else {
        overdueText = `<span class="due-soon">Due in ${Math.ceil(timeDiff / (1000 * 60 * 60 * 24))} days</span>`;
    }

    // Ensure exact format: DD/MM/YYYY HH:MM
    let formattedDueDate = `${dueDate.getDate().toString().padStart(2, '0')}/${(dueDate.getMonth() + 1).toString().padStart(2, '0')}/${dueDate.getFullYear()} ${dueDate.getHours().toString().padStart(2, '0')}:${dueDate.getMinutes().toString().padStart(2, '0')}`;

    let newTask = {
        taskName: taskName,
        ownerName: ownerName,
        priority: priority,
        formattedDueDate: formattedDueDate,
        overdueText: overdueText,
        progress: 0,  // Default progress at 0%
        completed: false
    };

    let tasks = JSON.parse(localStorage.getItem("tasks")) || [];
    tasks.push(newTask);
    
    tasks.sort((a, b) => priorityValue(b.priority) - priorityValue(a.priority));

    localStorage.setItem("tasks", JSON.stringify(tasks));

    renderTasks();
}

function priorityValue(priority) {
    let priorityLevels = { "Critical": 4, "High": 3, "Medium": 2, "Low": 1 };
    return priorityLevels[priority] || 0;
}

function renderTasks() {
    let table = document.getElementById("taskTable").querySelector("tbody");
    table.innerHTML = "";

    let tasks = JSON.parse(localStorage.getItem("tasks")) || [];

    tasks.forEach((task, index) => {
        let row = table.insertRow();
        row.innerHTML = `
            <td>${task.taskName}</td>
            <td>${task.ownerName}</td>
            <td class="priority-${task.priority.toLowerCase()}">${task.priority}</td>
            <td>
                <progress value="${task.progress}" max="100"></progress>
                <input type="range" min="0" max="100" value="${task.progress}" class="progress-slider" data-index="${index}">
            </td>
            <td class="due-date">${task.completed ? `<span class="completed">Completed</span>` : `${task.formattedDueDate} <br> ${task.overdueText}`}</td>
        `;
    });

    document.querySelectorAll(".progress-slider").forEach(slider => {
        slider.addEventListener("input", function () {
            updateProgress(this);
        });
    });
}

function updateProgress(slider) {
    let progress = slider.value;
    let progressBar = slider.previousElementSibling;
    progressBar.value = progress;

    let index = slider.getAttribute("data-index");
    let tasks = JSON.parse(localStorage.getItem("tasks"));

    tasks[index].progress = progress;
    
    let dueDateCell = slider.closest("tr").querySelector(".due-date");

    if (progress == 100) {
        tasks[index].completed = true;
        dueDateCell.innerHTML = `<span class="completed">Completed</span>`;
    } else {
        tasks[index].completed = false;
        dueDateCell.innerHTML = `${tasks[index].formattedDueDate} <br> ${tasks[index].overdueText}`;
    }

    localStorage.setItem("tasks", JSON.stringify(tasks));
}

// Load tasks on page load
document.addEventListener("DOMContentLoaded", renderTasks);
