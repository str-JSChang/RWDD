function handleFetchError(response) {
    if (response.status === 401 || response.status === 403) {
        // Unauthorized or Forbidden - redirect to login page
        alert('Please log in to continue using the application.');
        window.location.href = 'Login.php';
        return false;
    }
    return true;
}

// Global variable to store the current task being edited
let currentEditTask = null;

// Display Current Date
document.getElementById("current-date").innerText = new Date().toLocaleDateString();

// Add Task
function addTask(categoryId) {
    let taskList = document.querySelector(`[data-category-id="${categoryId}"] .task-list`);
    let taskItem = document.createElement("div");
    taskItem.classList.add("task-item");
    taskItem.dataset.taskId = "new"; // Temporary ID for new tasks
    taskItem.innerHTML = `
        <input type="checkbox" onclick="archiveTask(this)">
        <span class="task-text">New Task</span>
        <div class="icons">
            <button class="edit" onclick="openEditPanel(this, ${categoryId})">✏</button>
            <button class="delete" onclick="deleteTask(this)">🗑</button>
        </div>
    `;
    taskList.appendChild(taskItem);
    
    // Immediately open the edit panel for the new task
    openEditPanel(taskItem.querySelector('.edit'), categoryId);
}

// Archive Task
function archiveTask(checkbox) {
    const taskItem = checkbox.closest('.task-item');
    taskItem.style.opacity = checkbox.checked ? "0.5" : "1";
    
    // If this is an existing task (has ID), update its status in the database
    const taskId = taskItem.dataset.taskId;
    if (taskId && taskId !== 'new') {
        const status = checkbox.checked ? 'completed' : 'active';
        updateTaskStatus(taskId, status);
    }
}

// Function to update task status via AJAX
function updateTaskStatus(taskId, status) {
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('status', status);
    
    fetch('update_task_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Error updating task:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Delete Task
function deleteTask(button) {
    const taskItem = button.closest('.task-item');
    const taskId = taskItem.dataset.taskId;
    
    if (confirm('Are you sure you want to delete this task?')) {
        if (taskId && taskId !== 'new') {
            // Delete from database
            const formData = new FormData();
            formData.append('task_id', taskId);
            
            fetch('delete_task.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    taskItem.remove();
                } else {
                    console.error('Error deleting task:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            // Just remove from DOM if it's a new task
            taskItem.remove();
        }
    }
}

// Open Edit Panel
function openEditPanel(editButton, categoryId) {
    currentEditTask = editButton.closest('.task-item');
    
    const taskId = currentEditTask.dataset.taskId;
    const isNew = taskId === 'new';
    
    // If it's an existing task, fetch all details
    if (!isNew) {
        fetch(`get_task_details.php?task_id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateEditPanel(data.task, categoryId);
                } else {
                    console.error('Error fetching task details:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        // For new tasks, just set the default category
        document.getElementById("task-category").value = categoryId;
        document.getElementById("task-name").value = currentEditTask.querySelector('.task-text').textContent;
        
        // Set default due date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        document.getElementById("due-date").valueAsDate = tomorrow;
    }
    
    document.getElementById("edit-panel").classList.add("active");
}

// Populate Edit Panel with task details
function populateEditPanel(task, categoryId) {
    document.getElementById("task-name").value = task.task_name;
    document.getElementById("task-category").value = task.category_id || categoryId;
    
    if (task.start_date) {
        document.getElementById("start-date").value = task.start_date;
    }
    
    if (task.due_date) {
        document.getElementById("due-date").value = task.due_date;
    }
    
    document.getElementById("priority").value = task.priority || 'Medium';
    document.getElementById("description").value = task.description || '';
}

// Close Edit Panel
function closeEditPanel(save) {
    if (save && currentEditTask) {
        saveTask();
    } else {
        document.getElementById("edit-panel").classList.remove("active");
        currentEditTask = null;
    }
}

// Save Task
function saveTask() {
    const taskName = document.getElementById("task-name").value.trim();
    if (!taskName) {
        alert('Task name is required');
        return;
    }
    
    const taskId = currentEditTask.dataset.taskId;
    const isNew = taskId === 'new';
    
    const formData = new FormData();
    if (!isNew) {
        formData.append('task_id', taskId);
    }
    
    formData.append('task_name', taskName);
    formData.append('category_id', document.getElementById("task-category").value);
    formData.append('start_date', document.getElementById("start-date").value);
    formData.append('due_date', document.getElementById("due-date").value);
    formData.append('priority', document.getElementById("priority").value);
    formData.append('description', document.getElementById("description").value);
    
    const url = isNew ? 'add_task.php' : 'update_task.php';
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentEditTask.querySelector('.task-text').textContent = taskName;
            
            if (isNew) {
                // Update the task item with the new ID
                currentEditTask.dataset.taskId = data.task_id;
                
                // If category has changed, move the task
                const newCategoryId = document.getElementById("task-category").value;
                const currentCategoryId = currentEditTask.closest('.task-category').dataset.categoryId;
                
                if (newCategoryId !== currentCategoryId) {
                    const newCategoryTaskList = document.querySelector(`[data-category-id="${newCategoryId}"] .task-list`);
                    newCategoryTaskList.appendChild(currentEditTask);
                }
            }
            
            document.getElementById("edit-panel").classList.remove("active");
            currentEditTask = null;
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the task.');
    });
}

// Add Category
function addCategory() {
    // First show an input prompt
    const categoryName = prompt("Enter category name:");
    if (!categoryName || categoryName.trim() === '') {
        return;
    }
    
    // Then show color picker
    const colorOptions = [
        '#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336', '#607D8B',
        '#009688', '#795548', '#9E9E9E', '#FFC107', '#8BC34A', '#673AB7'
    ];
    
    let colorPickerHTML = '<div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">';
    colorOptions.forEach(color => {
        colorPickerHTML += `<div onclick="selectColor('${color}', '${categoryName}')" style="width:30px;height:30px;background-color:${color};cursor:pointer;border-radius:50%;"></div>`;
    });
    colorPickerHTML += '</div>';
    
    // Create a modal for color selection
    const modal = document.createElement('div');
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.style.display = 'flex';
    modal.style.justifyContent = 'center';
    modal.style.alignItems = 'center';
    modal.style.zIndex = '1000';
    
    const modalContent = document.createElement('div');
    modalContent.style.backgroundColor = 'white';
    modalContent.style.padding = '20px';
    modalContent.style.borderRadius = '10px';
    modalContent.style.maxWidth = '400px';
    modalContent.style.width = '90%';
    
    modalContent.innerHTML = `
        <h3 style="margin-bottom:15px;">Choose a color for "${categoryName}"</h3>
        ${colorPickerHTML}
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Function to handle color selection and create category
function selectColor(color, categoryName) {
    // Remove the color picker modal
    document.querySelector('div[style*="position: fixed"]').remove();
    
    // Send request to create new category
    const formData = new FormData();
    formData.append('category_name', categoryName);
    formData.append('color_code', color);
    
    fetch('add_category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new category to the page
            const taskContainer = document.getElementById("task-container");
            const newCategory = document.createElement("div");
            newCategory.classList.add("task-category");
            newCategory.id = `category-${data.category_id}`;
            newCategory.dataset.categoryId = data.category_id;
            
            newCategory.innerHTML = `
                <h3 style="border-left: 5px solid ${color}; padding-left: 10px;">
                    ${categoryName} 
                    <button onclick="addTask(${data.category_id})">+</button>
                </h3>
                <div class="task-list"></div>
            `;
            
            taskContainer.appendChild(newCategory);
            
            // Also add to the category dropdown in the edit panel
            const categorySelect = document.getElementById("task-category");
            const option = document.createElement("option");
            option.value = data.category_id;
            option.textContent = categoryName;
            categorySelect.appendChild(option);
        } else {
            alert('Error creating category: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the category.');
    });
}

function deleteCategory(categoryId) {
    // Confirm deletion
    if (!confirm('Are you sure you want to delete this category? All tasks in this category will also be deleted.')) {
        return;
    }

    // Send delete request
    const formData = new FormData();
    formData.append('category_id', categoryId);

    fetch('delete_category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove category from UI
            const categoryElement = document.getElementById(`category-${categoryId}`);
            if (categoryElement) {
                categoryElement.remove();
            }

            // Remove category from edit panel dropdown
            const categorySelect = document.getElementById("task-category");
            const optionToRemove = Array.from(categorySelect.options).find(
                option => option.value == categoryId
            );
            if (optionToRemove) {
                categorySelect.remove(optionToRemove.index);
            }
        } else {
            alert('Error deleting category: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the category.');
    });
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Populate category dropdown in edit panel
    const categorySelect = document.getElementById("task-category");
    
    document.querySelectorAll('.task-category').forEach(category => {
        const categoryId = category.dataset.categoryId;
        const categoryName = category.querySelector('h3').textContent.trim().replace('+', '').trim();
        
        // // Add delete button to category header
        // const categoryHeader = category.querySelector('h3');
        // const deleteButton = document.createElement('button');
        // deleteButton.textContent = '🗑';
        // deleteButton.style.backgroundColor = 'red';
        // deleteButton.style.color = 'white';
        // deleteButton.style.border = 'none';
        // deleteButton.style.borderRadius = '50%';
        // deleteButton.style.width = '25px';
        // deleteButton.style.height = '25px';
        // deleteButton.style.display = 'flex';
        // deleteButton.style.justifyContent = 'center';
        // deleteButton.style.alignItems = 'center';
        // deleteButton.onclick = () => deleteCategory(categoryId);
        
        // // Adjust the header to accommodate the delete button
        // categoryHeader.style.display = 'flex';
        // categoryHeader.style.justifyContent = 'space-between';
        // categoryHeader.style.alignItems = 'center';
        
        // // Append delete button
        // categoryHeader.appendChild(deleteButton);
        
        // Add to category dropdown
        const option = document.createElement("option");
        option.value = categoryId;
        option.textContent = categoryName;
        categorySelect.appendChild(option);
    });
});