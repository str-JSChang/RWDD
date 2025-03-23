// Global variables
let currentCollabId = null;

// Initialize collaboration functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load collaborations from server
    loadCollaborations();
    
    // Event listener for collaboration title
    const collabTitle = document.getElementById('current-collab-title');
    if (collabTitle) {
        collabTitle.addEventListener('click', openCollabModal);
    }
    
    // Close modal when clicking on X
    const closeBtn = document.querySelector('.close');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCollabModal);
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('collabModal');
        if (event.target === modal) {
            closeCollabModal();
        }
    });
    
    // Add new collaboration click
    const addCollabBtn = document.querySelector('.add-collab');
    if (addCollabBtn) {
        addCollabBtn.addEventListener('click', createNewCollab);
    }
    
    // Set up message sending
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    }
    
    const sendBtn = document.querySelector('.send-icon');
    if (sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
    }
});

// Load collaborations from server
function loadCollaborations() {
    fetch('get_collabs.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading collaborations:', data.error);
                return;
            }
            
            // Clear existing collaboration items
            const collabList = document.querySelector('.collab-list');
            // Keep only the "Add collaboration" button
            const addCollabBtn = document.querySelector('.add-collab');
            if (collabList && addCollabBtn) {
                collabList.innerHTML = '';
                collabList.appendChild(addCollabBtn);
                
                // Add collaborations from server
                data.collabs.forEach(collab => {
                    const collabItem = document.createElement('div');
                    collabItem.className = 'collab-item';
                    collabItem.dataset.id = collab.collab_id;
                    collabItem.innerHTML = `
                        <span class="collab-icon">#</span>
                        <span class="collab-name">${collab.collab_name}</span>
                    `;
                    
                    // Add click event
                    collabItem.addEventListener('click', function() {
                        switchCollab(this);
                    });
                    
                    // Insert before "Add collaboration" button
                    collabList.insertBefore(collabItem, addCollabBtn);
                });
                
                // Select first collaboration if available
                if (data.collabs.length > 0) {
                    const firstCollab = document.querySelector('.collab-item:not(.add-collab)');
                    if (firstCollab) {
                        switchCollab(firstCollab);
                    }
                } else {
                    // If no collaborations, show prompt to create one
                    openCollabModal();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Open collaboration selection modal
function openCollabModal() {
    const modal = document.getElementById('collabModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

// Close collaboration selection modal
function closeCollabModal() {
    const modal = document.getElementById('collabModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Switch to a different collaboration
function switchCollab(collabElement) {
    // Remove active class from all collaborations
    document.querySelectorAll('.collab-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to selected collaboration
    collabElement.classList.add('active');
    
    // Get collaboration ID and name
    const collabId = collabElement.dataset.id;
    const collabName = collabElement.querySelector('.collab-name').textContent;
    
    // Update current collaboration ID
    currentCollabId = collabId;
    
    // Update collaboration title
    document.getElementById('current-collab-title').textContent = '# ' + collabName;
    
    // Load messages for this collaboration
    loadMessages(collabId);
    
    closeCollabModal();
}

// Create a new collaboration
function createNewCollab() {
    const collabName = prompt('Enter new collaboration name:');
    if (!collabName || collabName.trim() === '') {
        return;
    }
    
    // Create form data
    const formData = new FormData();
    formData.append('collab_name', collabName.trim());
    
    // Send to server
    fetch('create_collab.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add to collaboration list
            const collabList = document.querySelector('.collab-list');
            const addCollabBtn = document.querySelector('.add-collab');
            
            if (collabList && addCollabBtn) {
                const newCollab = document.createElement('div');
                newCollab.className = 'collab-item';
                newCollab.dataset.id = data.collab_id;
                newCollab.innerHTML = `
                    <span class="collab-icon">#</span>
                    <span class="collab-name">${data.collab_name}</span>
                `;
                
                // Add click event
                newCollab.addEventListener('click', function() {
                    switchCollab(this);
                });
                
                // Insert before "Add collaboration" button
                collabList.insertBefore(newCollab, addCollabBtn);
                
                // Switch to the new collaboration
                switchCollab(newCollab);
            }
        } else {
            alert('Error creating collaboration: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create collaboration');
    });
}

// Load messages for a collaboration
function loadMessages(collabId) {
    const chatMessages = document.getElementById('chatMessages');
    if (!chatMessages) return;
    
    // Clear existing messages
    chatMessages.innerHTML = '';
    
    // Show loading indicator
    chatMessages.innerHTML = '<div class="loading">Loading messages...</div>';
    
    // Fetch messages from server
    fetch(`get_messages.php?collab_id=${collabId}`)
        .then(response => response.json())
        .then(data => {
            // Clear loading indicator
            chatMessages.innerHTML = '';
            
            if (data.error) {
                chatMessages.innerHTML = `<div class="error-message">${data.error}</div>`;
                return;
            }
            
            if (data.messages.length === 0) {
                chatMessages.innerHTML = `<div class="empty-messages">No messages yet. Be the first to post!</div>`;
                return;
            }
            
            // Add messages to the chat
            data.messages.forEach(message => {
                const messageEl = createMessageElement({
                    id: message.message_id,
                    user: message.username,
                    user_id: message.user_id,
                    avatar: message.username.substring(0, 1).toUpperCase(),
                    message: message.message_text,
                    time: formatDateTime(message.created_at),
                    attachment: message.attachment_path ? {
                        path: message.attachment_path,
                        name: message.attachment_name
                    } : null
                });
                
                chatMessages.appendChild(messageEl);
            });
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
            chatMessages.innerHTML = `<div class="error-message">Failed to load messages</div>`;
        });
}

// Format date and time
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

// Create a message element
function createMessageElement(data) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';
    messageDiv.dataset.messageId = data.id;
    
    // Check if this is the current user's message
    const userId = document.querySelector('[data-user-id]')?.dataset.userId;
    if (userId && data.user_id === parseInt(userId)) {
        messageDiv.classList.add('own-message');
    }
    
    let attachmentHtml = '';
    if (data.attachment) {
        attachmentHtml = `
            <div class="message-attachment">
                <div class="attachment-preview">${data.attachment.name}</div>
                <div class="attachment-actions">
                    <a href="${data.attachment.path}" class="attachment-btn" download>Download</a>
                </div>
            </div>
        `;
    }
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${data.avatar}</div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-username">${data.user}</span>
                <span class="message-time">${data.time}</span>
            </div>
            <div class="message-text">${data.message}</div>
            ${attachmentHtml}
        </div>
    `;
    
    return messageDiv;
}

// Send a new message
function sendMessage() {
    if (!currentCollabId) {
        alert('Please select a collaboration first');
        return;
    }
    
    const chatInput = document.getElementById('chatInput');
    if (!chatInput) return;
    
    const message = chatInput.value.trim();
    if (!message) return;
    
    // Create form data
    const formData = new FormData();
    formData.append('collab_id', currentCollabId);
    formData.append('message_text', message);
    
    // Add attachment if selected
    const fileInput = document.getElementById('fileUpload');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('attachment', fileInput.files[0]);
    }
    
    // Send to server
    fetch('post_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear input
            chatInput.value = '';
            fileInput.value = '';
            
            // Add message to chat
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                const messageEl = createMessageElement({
                    id: data.message.message_id,
                    user: data.message.username,
                    user_id: data.message.user_id,
                    avatar: data.message.username.substring(0, 1).toUpperCase(),
                    message: data.message.message_text,
                    time: formatDateTime(data.message.created_at),
                    attachment: data.message.attachment_path ? {
                        path: data.message.attachment_path,
                        name: data.message.attachment_name
                    } : null
                });
                
                chatMessages.appendChild(messageEl);
                
                // Scroll to bottom
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        } else {
            alert('Error sending message: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message');
    });
}

// Handle file upload
function handleFileUpload() {
    const fileInput = document.getElementById('fileUpload');
    if (!fileInput || !fileInput.files.length) return;
    
    const file = fileInput.files[0];
    const attachmentsContainer = document.getElementById('attachmentsContainer');
    
    if (attachmentsContainer) {
        // Clear existing attachments
        attachmentsContainer.innerHTML = '';
        
        // Add attachment preview
        const attachmentItem = document.createElement('div');
        attachmentItem.className = 'attachment-item';
        attachmentItem.innerHTML = `
            <span class="attachment-name">${file.name}</span>
            <button class="attachment-remove" onclick="removeAttachment()">×</button>
        `;
        
        attachmentsContainer.appendChild(attachmentItem);
    }
}

// Remove attachment
function removeAttachment() {
    const fileInput = document.getElementById('fileUpload');
    const attachmentsContainer = document.getElementById('attachmentsContainer');
    
    if (fileInput) {
        fileInput.value = '';
    }
    
    if (attachmentsContainer) {
        attachmentsContainer.innerHTML = '';
    }
}