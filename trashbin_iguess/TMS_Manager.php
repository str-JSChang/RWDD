<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - Manager</title>
    <link rel="stylesheet" href="TMS_Manager.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="container">
            <h2>Task Management - Manager</h2>

            <input type="text" id="taskName" placeholder="Task Name">
            <input type="text" id="ownerName" placeholder="Owner Name">

            <select id="taskPriority">
                <option value="Critical">Critical</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>

            <input type="datetime-local" id="dueDate">
            <button id="assignTask">Assign Task</button>

            <table id="taskTable">
                <tr>
                    <th>Task</th>
                    <th>Owner</th>
                    <th>Priority</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                </tr>
            </table>
        </div>
    </div>

    <script src="TMS_Manager.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>
