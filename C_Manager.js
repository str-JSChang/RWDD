// Global variables to store message data
let messageData = [];
let currentUser = {
    id: 0, // This would come from PHP session in the future
    username: "User", // This would come from PHP session in the future
    initials: "U"  // This would come from PHP session in the future
};

// Initialize the chat interface
document.addEventListener("DOMContentLoaded", function() {
    // Add tab click handlers
    document.querySelectorAll(".tablinks").forEach(function(button) {
        button.addEventListener("click", function(event) {
            const tabName = this.textContent === "Post" ? "Post" : "TaskManagement";
            openTab(event, tabName);
        });
    });
    
    // In the future, this would load messages from the database
    loadMessages();
    
    // Setup event listeners
    let chatInputEl = document.getElementById("chatInput");
    if (chatInputEl) {
        chatInputEl.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                sendMessage();
            }
        });
    }
});

// Tab Switching
window.openTab = function(evt, tabName) {
    // Get all elements with class="tabcontent" and hide them
    var tabcontent = document.getElementsByClassName("tabcontent");
    for (var i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    var tablinks = document.getElementsByClassName("tablinks");
    for (var i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

// Function to load messages from the server (placeholder for now)
function loadMessages() {
    // In the future, this would be an AJAX call to get messages from PHP
    // For now, we'll just add a welcome message
    const welcomeMessage = {
        id: 1,
        user_id: 0,
        username: "System",
        message: "Welcome to the collaboration workspace!",
        timestamp: new Date().toISOString(),
        attachments: []
    };
    
    messageData.push(welcomeMessage);
    renderMessages();
}

// Function to render all messages in the UI
function renderMessages() {
    const chatMessagesEl = document.getElementById("chatMessages");
    if (!chatMessagesEl) return;
    
    chatMessagesEl.innerHTML = ''; // Clear existing messages
    
    messageData.forEach(message => {
        // Create a message element
        const messageEl = createMessageElement(message);
        chatMessagesEl.appendChild(messageEl);
    });
    
    // Scroll to bottom
    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
}

// Function to create a message element
function createMessageElement(message) {
    const messageDiv = document.createElement("div");
    messageDiv.className = "message";
    messageDiv.dataset.messageId = message.id;
    
    // Determine if message is from current user
    const isCurrentUser = message.user_id === currentUser.id;
    if (isCurrentUser) {
        messageDiv.classList.add("own-message");
    }
    
    // Get user initials for avatar
    const userInitials = message.username.substring(0, 2).toUpperCase();
    
    // Create message HTML
    messageDiv.innerHTML = `
        <div class="user-avatar message-avatar">
            <i class="user-icon">${userInitials}</i>
        </div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-username">${message.username}</span>
                <span class="message-time">${formatTimestamp(message.timestamp)}</span>
            </div>
            <div class="message-text">${message.message}</div>
            ${renderAttachments(message.attachments)}
        </div>
    `;
    
    return messageDiv;
}

// Function to render attachments
function renderAttachments(attachments) {
    if (!attachments || attachments.length === 0) return '';
    
    let attachmentsHtml = '';
    attachments.forEach(attachment => {
        attachmentsHtml += `
            <div class="message-attachment">
                <div class="attachment-preview">${attachment.filename}</div>
                <div class="attachment-actions">
                    <button class="attachment-btn" onclick="downloadAttachment(${attachment.id})">Download</button>
                    <button class="attachment-btn" onclick="previewAttachment(${attachment.id})">Preview</button>
                </div>
            </div>
        `;
    });
    
    return attachmentsHtml;
}

// Format timestamp into readable format
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Send a new message
function sendMessage() {
    const chatInput = document.getElementById("chatInput");
    if (!chatInput) return;
    
    const message = chatInput.value.trim();
    
    if (message) {
        // Create message object
        const newMessage = {
            id: messageData.length + 1, // In real app, this would come from the server
            user_id: currentUser.id,
            username: currentUser.username,
            message: message,
            timestamp: new Date().toISOString(),
            attachments: [] // No attachments for text-only messages
        };
        
        // In a real app, this would be sent to the server via AJAX
        // For now, we'll just add it locally
        messageData.push(newMessage);
        renderMessages();
        
        // Clear input
        chatInput.value = "";
    }
}

// Handle file upload
function handleFileUpload() {
    const fileInput = document.getElementById("fileUpload");
    if (!fileInput) return;
    
    const file = fileInput.files[0];
    
    if (file) {
        // In a real app, you would upload the file to the server here
        // For now, we'll just simulate it
        
        // Create an attachment object
        const attachment = {
            id: Date.now(), // Simulate an ID
            filename: file.name,
            filesize: file.size,
            filetype: file.type
        };
        
        // Create a message with the attachment
        const newMessage = {
            id: messageData.length + 1,
            user_id: currentUser.id,
            username: currentUser.username,
            message: "Shared a file:",
            timestamp: new Date().toISOString(),
            attachments: [attachment]
        };
        
        // Add to message data and render
        messageData.push(newMessage);
        renderMessages();
        
        // Add to attachments container
        addToAttachmentsContainer(attachment);
        
        // Reset file input
        fileInput.value = "";
    }
}

// Add attachment to the attachments container
function addToAttachmentsContainer(attachment) {
    const attachmentsContainer = document.getElementById("attachmentsContainer");
    if (!attachmentsContainer) return;
    
    const attachmentItem = document.createElement("div");
    attachmentItem.className = "attachment-item";
    attachmentItem.dataset.attachmentId = attachment.id;
    
    attachmentItem.innerHTML = `
        <span class="attachment-name">${attachment.filename}</span>
        <button class="attachment-remove" onclick="removeAttachment(${attachment.id})">×</button>
    `;
    
    attachmentsContainer.appendChild(attachmentItem);
}

// Remove attachment (placeholder function)
function removeAttachment(attachmentId) {
    // In a real app, this would make an AJAX call to remove from the server
    const attachmentEl = document.querySelector(`.attachment-item[data-attachment-id="${attachmentId}"]`);
    if (attachmentEl) {
        attachmentEl.remove();
    }
}

// Download attachment (placeholder function)
function downloadAttachment(attachmentId) {
    // In a real app, this would initiate a download
    alert(`Downloading attachment ${attachmentId}`);
}

// Preview attachment (placeholder function)
function previewAttachment(attachmentId) {
    // In a real app, this would show a preview
    alert(`Previewing attachment ${attachmentId}`);
}