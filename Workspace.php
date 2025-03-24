<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

// Fetch categories
$categories = [];
if ($isLoggedIn) {
    $query = "SELECT category_id, category_name, color_code FROM task_categories 
              WHERE user_id IS NULL OR user_id = ? 
              ORDER BY category_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch user's tasks
$tasks = [];
if ($isLoggedIn) {
    $query = "SELECT t.task_id, t.task_name, t.status, t.category_id, 
                    t.due_date, t.description, c.category_name, c.color_code 
              FROM task t
              JOIN task_categories c ON t.category_id = c.category_id 
              WHERE t.creator_id = ? 
              ORDER BY t.due_date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $categoryId = $row['category_id'];
        if (!isset($tasks[$categoryId])) {
            $tasks[$categoryId] = [
                'name' => $row['category_name'],
                'color' => $row['color_code'],
                'items' => []
            ];
        }
        
        $tasks[$categoryId]['items'][] = [
            'id' => $row['task_id'],
            'name' => $row['task_name'],
            'status' => $row['status'],
            'dueDate' => $row['due_date'],
            'description' => $row['description']
        ];
    }
}
?>

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
             <?php if (!$isLoggedIn): ?>
                <div class="login-prompt">
                <p>Please <a href="Login.php">log in</a></p>
                </div>
            <?php else: ?>
            <div id="task-container">
                <?php if (empty($categories)): ?>
                    <p>No categories found. Please create a category.</p>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="task-category" id="category-<?php echo $category['category_id']; ?>" 
                             data-category-id="<?php echo $category['category_id']; ?>">
                            <h3 style="border-left: 5px solid <?php echo $category['color_code']; ?>; padding-left: 10px;">
                                <?php echo htmlspecialchars($category['category_name']); ?> 
                                <button onclick="addTask(<?php echo $category['category_id']; ?>)">+</button>
                            </h3>
                            <div class="task-list">
                                <?php 
                                if (isset($tasks[$category['category_id']]) && !empty($tasks[$category['category_id']]['items'])):
                                    foreach ($tasks[$category['category_id']]['items'] as $task):
                                ?>
                                    <div class="task-item" data-task-id="<?php echo $task['id']; ?>">
                                        <input type="checkbox" <?php echo ($task['status'] === 'completed') ? 'checked' : ''; ?> 
                                               onclick="archiveTask(this)">
                                        <span class="task-text"><?php echo htmlspecialchars($task['name']); ?></span>
                                        <div class="icons">
                                            <button class="edit" onclick="openEditPanel(this)">✏</button>
                                            <button class="delete" onclick="deleteTask(this)">🗑</button>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach; 
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
            </div>

            <?php if (!$isLoggedIn): ?>
                <button class="add-task-btn" style="display: none;">+ ADD TASK CATEGORY</button>
            <?php else: ?>
                <button class="add-task-btn" onclick="addCategory()">+ ADD TASK CATEGORY</button>
            <?php endif; ?>
        </div>

        <!-- Edit Panel -->
        <div class="edit-panel" id="edit-panel">
        <h3>Edit Task</h3>
        <label>Task Name:</label>
        <input type="text" id="task-name">

        <label>Category:</label>
        <select id="task-category">
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

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

    <script src="Workspace.js"></script>
    <script src="sidebar.js"></script>
    
</body>
</html>