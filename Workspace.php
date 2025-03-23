<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="Workspace.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="container">
            <div class="header">
                <span>Today <span id="current-date"></span></span>
            </div>

            <!-- Task Categories -->
            <div id="task-container">
                <div class="task-category" id="personal">
                    <h3>Personal <button onclick="addTask('personal')">+</button></h3>
                    <div class="task-list">
                        <!-- Default 3 tasks for Personal -->
                        <div class="task-item">
                            <input type="checkbox" onclick="archiveTask(this)">
                            <span class="task-text">Personal Task 1</span>
                            <div class="icons">
                                <button class="edit" onclick="openEditPanel(this)">✏</button>
                                <button class="delete" onclick="deleteTask(this)">🗑</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="task-category" id="grocery">
                    <h3>Grocery <button onclick="addTask('grocery')">+</button></h3>
                    <div class="task-list">
                        <!-- Default 3 tasks for Grocery -->
                        <div class="task-item">
                            <input type="checkbox" onclick="archiveTask(this)">
                            <span class="task-text">Grocery Task 1</span>
                            <div class="icons">
                                <button class="edit" onclick="openEditPanel(this)">✏</button>
                                <button class="delete" onclick="deleteTask(this)">🗑</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="add-task-btn" onclick="addCategory()">+ ADD TASK CATEGORY</button>
        </div>

        <!-- Edit Panel -->
        <div class="edit-panel" id="edit-panel">
            <h3>Edit Task</h3>
            <label>Task Name:</label>
            <input type="text" id="task-name">

            <label>Start Date:</label>
            <input type="date" id="start-date">

            <label>Due Date:</label>
            <input type="date" id="due-date">

            <label>Priority:</label>
            <select id="priority">
                <option>High</option>
                <option>Medium</option>
                <option>Low</option>
            </select>

            <label>Description:</label>
            <textarea rows="3" id="description"></textarea>

            <div class="button-group">
                <button class="back-btn" onclick="closeEditPanel(false)">Back</button>
                <button class="done-btn" onclick="closeEditPanel(true)">Done</button>
            </div>
        </div>
    </div>

    <script src="Workspace.js"></script>
    <script src="sidebar.js"></script>
    
</body>
</html>
