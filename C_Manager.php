<?php
session_start();
// Debug information - remove in production
echo "<!--";
var_dump($_SESSION);
echo "-->";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collaboration Page - Manager</title>
    <link rel="stylesheet" href="C_Manager.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="team.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <!-- Main content -->
    <div class="main-content" id="mainContent" data-user-id="<?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : ''; ?>">
        <!-- Collaboration header -->
        <div class="collab-header">
            <h1 id="current-collab-title"># Python Project</h1>
            
            <div class="collab-tabs">
                <button class="tablinks active" onclick="openTab(event, 'Post')">
                    <span class="tab-icon">💬</span> Post
                </button>
                <button class="tablinks" onclick="openTab(event, 'TaskManagement')">
                    <span class="tab-icon">📋</span> Team Management
                </button>
            </div>
            
            <div class="collab-actions">
                <button class="action-btn" title="Share document">📄</button>
                <button class="action-btn" title="Start meeting">👥</button>
                <button class="action-btn" title="Add member">👤</button>
            </div>
        </div>

        <!-- Post Section with Chatbox -->
        <div id="Post" class="tabcontent" style="display: block;">
            <div class="chat-collab">
                <!-- Chat/Comment area with messages -->
                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be loaded dynamically -->
                </div>
                
                <!-- Compose new message area -->
                <div class="compose-area">
                    <div class="message-input-container">
                        <input type="text" class="message-input" id="chatInput" placeholder="Comment...">
                        <div class="message-actions">
                            <button class="attachment-icon" onclick="document.getElementById('fileUpload').click()">📎</button>
                            <button class="send-icon" onclick="sendMessage()">➤</button>
                        </div>
                    </div>
                    <!-- Hidden file input -->
                    <input type="file" id="fileUpload" style="display: none;" onchange="handleFileUpload()">
                    <!-- Attachment preview container -->
                    <div id="attachmentsContainer" class="attachments-container"></div>
                </div>
            </div>
        </div>

        <!-- Task Management Section -->
        <div id="TaskManagement" class="tabcontent" style="display: none;">
            <div class="wrapper">
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
    </div>

    <!-- Collaboration Selector Modal -->
    <div id="collabModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Select Collaboration</h2>
            <div class="collab-list">
                <!-- Collaborations will be loaded dynamically -->
                <div class="collab-item add-collab">
                    <span class="collab-icon">+</span>
                    <span class="collab-name">Create New Collaboration</span>
                </div>
            </div>
        </div>
    </div>

    <script src="C_Manager.js"></script>
    <script src="team.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>