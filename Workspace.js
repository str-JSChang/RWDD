// Global variable to store the current task being edited
let currentEditTask = null;

// Display Current Date
document.getElementById("current-date").innerText = new Date().toLocaleDateString();

// Add Task
function addTask(categoryId) {
    let taskList = document.querySelector(`#${categoryId} .task-list`);
    let taskItem = document.createElement("div");
    taskItem.classList.add("task-item");
    taskItem.innerHTML = `
        <input type="checkbox" onclick="archiveTask(this)">
        <span class="task-text">New Task</span>
        <div class="icons">
            <button class="edit" onclick="openEditPanel(this)">✏</button>
            <button class="delete" onclick="deleteTask(this)">🗑</button>
        </div>
    `;
    taskList.appendChild(taskItem);
}

// Archive Task
function archiveTask(checkbox) {
    checkbox.parentElement.style.opacity = checkbox.checked ? "0.5" : "1";
}

// Delete Task
function deleteTask(button) {
    button.parentElement.parentElement.remove();
}

// Open Edit Panel
function openEditPanel(editButton) {
    currentEditTask = editButton.closest('.task-item');
    document.getElementById("task-name").value = currentEditTask.querySelector('.task-text').textContent;
    document.getElementById("edit-panel").classList.add("active");
}

// Close Edit Panel
function closeEditPanel(save) {
    if (save && currentEditTask) {
        currentEditTask.querySelector('.task-text').textContent = document.getElementById("task-name").value;
    }
    document.getElementById("edit-panel").classList.remove("active");
    currentEditTask = null;
}

// Add Category
function addCategory() {
    let categoryName = prompt("Enter category name:");
    if (categoryName) {
        let categoryId = categoryName.toLowerCase().replace(/[^a-z0-9]/g, '-');
        let taskContainer = document.getElementById("task-container");
        let newCategory = document.createElement("div");
        newCategory.classList.add("task-category");
        newCategory.id = categoryId;
        newCategory.innerHTML = `<h3>${categoryName} <button onclick="addTask('${categoryId}')">+</button></h3><div class="task-list"></div>`;
        taskContainer.appendChild(newCategory);
    }
}
