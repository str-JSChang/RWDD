// Global variables
let currentCollabId = null;
let isSubmitting = false; // Flag to prevent duplicate submissions

// Initialize collaboration functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set up tab switching
    document.querySelectorAll('.tablinks').forEach(function(button) {
        button.addEventListener('click', function(event) {
            openTab(event, this.textContent.includes('Post') ? 'Post' : 'TaskManagement');
        });
    });
    
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
    
    // Set up message sending - Using a single event handler for Enter key
    const chatInput = document.getElementById('chatInput');
    if (chatInput) {
        chatInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });
    }
    
    // Set up message sending button
    const sendBtn = document.querySelector('.send-icon');
    if (sendBtn) {
        sendBtn.addEventListener('click', function(event) {
            event.preventDefault();
            sendMessage();
        });
    }
    
    // File upload button
    const attachmentBtn = document.querySelector('.attachment-icon');
    if (attachmentBtn) {
        attachmentBtn.addEventListener('click', function() {
            document.getElementById('fileUpload').click();
        });
    }
    
    // File input change
    const fileInput = document.getElementById('fileUpload');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileUpload);
    }

    // Action buttons in the collaboration header
    const actionButtons = document.querySelectorAll('.action-btn');
    if (actionButtons.length > 0) {
        // Share document button
        actionButtons[0].addEventListener('click', function() {
            alert('Share document functionality will be added in a future update.');
        });
        
        // Start meeting button
        actionButtons[1].addEventListener('click', function() {
            alert('Meeting functionality will be added in a future update.');
        });
        
        // Add member button
        actionButtons[2].addEventListener('click', function() {
            alert('Add member functionality will be added in a future update.');
        });
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
                if (data.collabs && data.collabs.length > 0) {
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
            console.error('Error fetching collaborations:', error);
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
    
    // Fetch messages from server with a timestamp to prevent caching
    fetch(`get_messages.php?collab_id=${collabId}&_=${new Date().getTime()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}`);
            }
            return response.text(); // Get as text first to debug
        })
        .then(text => {
            try {
                // Try to parse as JSON
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON:', text);
                throw new Error('Server returned invalid JSON');
            }
        })
        .then(data => {
            // Clear loading indicator
            chatMessages.innerHTML = '';
            
            if (data.error) {
                chatMessages.innerHTML = `<div class="error-message">${data.error}</div>`;
                return;
            }
            
            if (!data.messages || data.messages.length === 0) {
                chatMessages.innerHTML = `<div class="empty-messages">No messages yet. Be the first to post!</div>`;
                return;
            }
            
            // Debug data
            console.log('Received messages:', data.messages);
            
            // Add messages to the chat
            data.messages.forEach(message => {
                console.log('Processing message:', message);
                
                const messageEl = createMessageElement({
                    id: message.message_id,
                    user: message.username,
                    user_id: parseInt(message.user_id, 10),
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
            console.error('Error loading messages:', error);
            chatMessages.innerHTML = `<div class="error-message">Failed to load messages: ${error.message}</div>`;
        });
}

// Format date and time
function formatDateTime(dateString) {
    if (!dateString) return '';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return dateString; // Return original if not valid date
        }
        return date.toLocaleString();
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
}

// Create a message element
function createMessageElement(data) {
    console.log('Creating message element with data:', data);
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';
    messageDiv.dataset.messageId = data.id;
    
    // Check if this is the current user's message
    const mainContent = document.getElementById('mainContent');
    const userId = mainContent ? parseInt(mainContent.dataset.userId, 10) : null;
    
    console.log('Current user ID:', userId, 'Message user ID:', data.user_id);
    
    if (userId && data.user_id === userId) {
        messageDiv.classList.add('own-message');
    }
    
    let attachmentHtml = '';
    
    if (data.attachment && data.attachment.path) {
        console.log('Message has attachment:', data.attachment);
        
        attachmentHtml = `
            <div class="message-attachment">
                <div class="attachment-preview">${data.attachment.name || 'Attachment'}</div>
                <div class="attachment-actions">
                    <a href="${data.attachment.path}" class="attachment-btn" download>Download</a>
                </div>
            </div>
        `;
    }
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${data.avatar || '?'}</div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-username">${data.user || 'User'}</span>
                <span class="message-time">${data.time || 'Now'}</span>
            </div>
            <div class="message-text">${data.message || ''}</div>
            ${attachmentHtml}
        </div>
    `;
    
    return messageDiv;
}

// Send a new message
function sendMessage() {
    // Prevent duplicate submissions
    if (isSubmitting) {
        console.log('Already submitting a message, please wait...');
        return;
    }
    
    if (!currentCollabId) {
        alert('Please select a collaboration first');
        return;
    }
    
    const chatInput = document.getElementById('chatInput');
    if (!chatInput) return;
    
    const message = chatInput.value.trim();
    const fileInput = document.getElementById('fileUpload');
    const hasAttachment = fileInput && fileInput.files.length > 0;
    
    // Check if there's either a message or an attachment
    if (!message && !hasAttachment) {
        return;
    }
    
    // Set submitting flag to true
    isSubmitting = true;
    
    // Create form data
    const formData = new FormData();
    formData.append('collab_id', currentCollabId);
    formData.append('message_text', message);
    
    // Add attachment if selected
    if (hasAttachment) {
        console.log('Adding attachment to form data:', fileInput.files[0].name);
        formData.append('attachment', fileInput.files[0]);
    }
    
    // Clear input fields immediately to prevent double-clicking
    chatInput.value = '';
    
    // Clear attachment container
    const attachmentsContainer = document.getElementById('attachmentsContainer');
    if (attachmentsContainer) {
        attachmentsContainer.innerHTML = '';
    }
    
    console.log('Sending message to server...');
    
    // Send to server
    fetch('post_messages.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.success) {
            // Successfully sent message, now clear file input
            if (fileInput) fileInput.value = '';
            
            // Add message to chat
            const chatMessages = document.getElementById('chatMessages');
            if (chatMessages) {
                console.log('Adding new message to chat:', data.message);
                
                const messageEl = createMessageElement({
                    id: data.message.message_id,
                    user: data.message.username,
                    user_id: parseInt(data.message.user_id, 10),
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
            console.error('Error from server:', data.error);
            alert('Error sending message: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message: ' + error.message);
    })
    .finally(() => {
        // Reset submitting flag after a short delay (to prevent rapid clicks)
        setTimeout(() => {
            isSubmitting = false;
        }, 1000);
    });
}

// Handle file upload
function handleFileUpload() {
    const fileInput = document.getElementById('fileUpload');
    if (!fileInput || !fileInput.files.length) return;
    
    const file = fileInput.files[0];
    console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);
    
    // this is not popping out, not sure where, maybe because i'm blur
    // Check file size (10MB limit)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        alert('File is too large. Maximum size is 10MB.');
        fileInput.value = '';
        return;
    }
    
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

// Tab functionality
function openTab(evt, tabName) {
    // Hide all tabcontent elements
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Remove the "active" class from all tablinks
    var tablinks = document.getElementsByClassName("tablinks");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}