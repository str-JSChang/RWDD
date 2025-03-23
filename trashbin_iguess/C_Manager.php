<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaboration Page - Manager</title>
    <link rel="stylesheet" href="C_Manager.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>

    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>
    <div class="main-content" id="mainContent">
        <div class="container">
            <h2>Collaboration Page - Manager</h2>

            <div class="section">
                <h3>Discussion & File Sharing</h3>
                <textarea id="commentInput" placeholder="Write a comment..."></textarea>
                <button onclick="addComment()">Post Comment</button>
                <ul class="comment-list" id="commentList"></ul>

                <input type="file" id="fileUpload">
                <button onclick="uploadFile()">Upload File</button>
                <ul class="file-list" id="fileList"></ul>
            </div>

            <div class="section">
                <h3>Task Management</h3>
                <input type="text" id="taskInput" placeholder="Enter a task">
                <button onclick="addTask()">Assign Task</button>
                <ul class="task-list" id="taskList"></ul>
            </div>

            <div class="section">
                <h3>Team Members</h3>
                <input type="text" id="memberInput" placeholder="Enter member name">
                <button onclick="addMember()">Add Member</button>
                <ul class="member-list" id="memberList"></ul>
            </div>

            <div class="section">
                <h3>Schedule Online Meeting</h3>
                <input type="datetime-local" id="meetingTime">
                <button onclick="scheduleMeeting()">Schedule Meeting</button>
                <p id="meetingScheduled"></p>
            </div>

        </div>
    </div>

    <script src="C_Manager.js"></script>
    <script src="sidebar.js"></script>

</body>
</html>
