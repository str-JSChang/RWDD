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
    <style>
    /* Add Member Modal Styles */
    .search-container {
        margin: 15px 0;
    }

    .search-container input {
        width: 75%;
        padding: 10px;
        margin-right: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .search-container button {
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .search-results {
        margin-top: 15px;
        max-height: 300px;
        overflow-y: auto;
    }

    .user-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .user-item:hover {
        background-color: #f5f5f5;
    }

    .user-info {
        align-items: center;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #007bff;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 10px;
    }

    .user-name {
        font-weight: bold;
    }

    .user-email {
        font-size: 14px;
        color: #666;
    }

    .add-user-btn {
        padding: 6px 12px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .add-user-btn:hover {
        background-color: #218838;
    }

    .user-added {
        color: #28a745;
        font-size: 14px;
    }

    .empty-results, .loading, .error-message {
        padding: 15px;
        text-align: center;
        color: #666;
    }

    .error-message {
        color: #dc3545;
    }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
<!-- The code is ugly, but i don't gives a shit. -->

    <!-- Main content -->
    <div class="main-content" id="mainContent" data-user-id="<?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : ''; ?>">
        <!-- Collaboration header -->
        <div class="collab-header">
            <h1 id="current-collab-title"># Python Project</h1>
            
            <div class="collab-tabs">
                <button class="tablinks active" onclick="openTab(event, 'Post')">
                    <span class="tab-icon">💬</span> Post
                </button>
                <button class="tablinks" onclick="openTab(event, 'Members')">
                    <span class="tab-icon">👥</span> Members
                </button>
            </div>
            
            <div class="collab-actions">
                <button class="action-btn" title="Add member">👤</button>
                <button class="action-btn" title="Delete collaboration" onclick="confirmDeleteCollab()">🗑️</button>
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

        <div id="Members" class="tabcontent">
            <div class="members-container">
                <h2>Collaboration Members</h2>
                <div class="members-list" id="membersList">
                    <!-- Members will be loaded dynamically -->
                    <div class="loading">Loading members...</div>
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

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Add Member to Collaboration</h2>
            <div class="search-container">
                <input type="text" id="memberSearchInput" placeholder="Search for a user...">
                <button id="searchMemberBtn">Search</button>
            </div>
            <div id="memberSearchResults" class="search-results">
                <!-- Search results will be displayed here -->
            </div>
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div id="deleteCollabModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Delete Collaboration</h2>
            <p>Are you sure you want to delete this collaboration? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                <button class="delete-btn" onclick="deleteCollaboration()">Delete</button>
            </div>
        </div>
    </div>

    <script src="C_Manager.js"></script>
    <script src="team.js"></script>
    <script src="sidebar.js"></script>
    <script>
    // Add Member functionality

    // Add this to your existing DOMContentLoaded event listener
    document.addEventListener('DOMContentLoaded', function() {
        // Add member button click handler
        const addMemberBtn = document.querySelector('.action-btn[title="Add member"]');
        if (addMemberBtn) {
            addMemberBtn.addEventListener('click', openAddMemberModal);
        }
        
        // Close modal when clicking on X
        const closeModalBtn = document.querySelector('#addMemberModal .close-modal');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', closeAddMemberModal);
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('addMemberModal');
            if (event.target === modal) {
                closeAddMemberModal();
            }
        });
        
        // Search button click
        const searchBtn = document.getElementById('searchMemberBtn');
        if (searchBtn) {
            searchBtn.addEventListener('click', searchMembers);
        }
        
        // Search input enter key press
        const searchInput = document.getElementById('memberSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchMembers();
                }
            });
        }
    });

    // Open add member modal
    function openAddMemberModal() {
        if (!currentCollabId) {
            alert('Please select a collaboration first');
            return;
        }
        
        const modal = document.getElementById('addMemberModal');
        if (modal) {
            // Clear previous search results and input
            document.getElementById('memberSearchResults').innerHTML = '';
            document.getElementById('memberSearchInput').value = '';
            
            modal.style.display = 'block';
        }
    }

    // Close add member modal
    function closeAddMemberModal() {
        const modal = document.getElementById('addMemberModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Search for members not in the collaboration
    function searchMembers() {
        const searchInput = document.getElementById('memberSearchInput');
        const searchText = searchInput.value.trim();
        
        if (!searchText) {
            return;
        }
        
        const searchResults = document.getElementById('memberSearchResults');
        searchResults.innerHTML = '<div class="loading">Searching...</div>';
        
        // Fetch users matching the search term
        fetch(`search_users.php?search=${encodeURIComponent(searchText)}&collab_id=${currentCollabId}`)
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                
                if (!data.success) {
                    searchResults.innerHTML = `<div class="error-message">${data.error}</div>`;
                    return;
                }
                
                if (data.users.length === 0) {
                    searchResults.innerHTML = '<div class="empty-results">No users found. Try a different search term.</div>';
                    return;
                }
                
                // Display each user with an add button
                data.users.forEach(user => {
                    const userEl = document.createElement('div');
                    userEl.className = 'user-item';
                    userEl.dataset.userId = user.user_id;
                    
                    // Get initials from username
                    const initials = user.username.substring(0, 1).toUpperCase();
                    
                    userEl.innerHTML = `
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div>
                                <div class="user-name">${user.username}</div>
                                <div class="user-email">${user.email || ''}</div>
                            </div>
                        </div>
                        <button class="add-user-btn" onclick="addMemberToCollab(${user.user_id})">Add</button>
                    `;
                    
                    searchResults.appendChild(userEl);
                });
            })
            .catch(error => {
                console.error('Error searching users:', error);
                searchResults.innerHTML = `<div class="error-message">Failed to search users: ${error.message}</div>`;
            });
    }

    // Add a member to the collaboration
    function addMemberToCollab(userId) {
        // Create form data
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('collab_id', currentCollabId);
        formData.append('role_id', 2); // Default to regular member role
        
        // Find the button in the user item for UI feedback
        const userItem = document.querySelector(`.user-item[data-user-id="${userId}"]`);
        const addBtn = userItem ? userItem.querySelector('.add-user-btn') : null;
        
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.textContent = 'Adding...';
        }
        
        // Send to server
        fetch('add_users.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                if (userItem) {
                    const addBtn = userItem.querySelector('.add-user-btn');
                    if (addBtn) {
                        // Replace button with success message
                        addBtn.outerHTML = `<span class="user-added">Added ✓</span>`;
                    }
                }
                
                // Optional: Show a notification
                alert(data.message || 'User added successfully');
            } else {
                alert('Error adding member: ' + data.error);
                
                // Reset button state
                if (addBtn) {
                    addBtn.disabled = false;
                    addBtn.textContent = 'Add';
                }
            }
        })
        .catch(error => {
            console.error('Error adding member:', error);
            alert('Failed to add member');
            
            // Reset button state
            if (addBtn) {
                addBtn.disabled = false;
                addBtn.textContent = 'Add';
            }
        });
    }
    </script>
</body>
</html>