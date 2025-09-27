<!-- <ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include> -->
<ng-include src="'/partials/flash-message'"></ng-include>

<div class="chat-app" ng-controller="ChatController">
    <!-- Left Sidebar -->
    <div class="chat-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="user-profile">
                <div class="user-avatar">
                    <span>{{ currentUser.charAt(0).toUpperCase() }}</span>
                </div>
                <div class="user-info">
                    <h3>{{ currentUser }}</h3>
                    <p class="user-status">Active</p>
                </div>
            </div>
            <div class="sidebar-actions">
                <button class="action-btn" title="Settings">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M10,22C9.75,22 9.54,21.82 9.5,21.58L9.13,18.93C8.5,18.68 7.96,18.34 7.44,17.94L4.95,18.95C4.73,19.03 4.46,18.95 4.34,18.73L2.34,15.27C2.21,15.05 2.27,14.78 2.46,14.63L4.57,12.97L4.5,12L4.57,11.03L2.46,9.37C2.27,9.22 2.21,8.95 2.34,8.73L4.34,5.27C4.46,5.05 4.73,4.96 4.95,5.05L7.44,6.05C7.96,5.66 8.5,5.32 9.13,5.07L9.5,2.42C9.54,2.18 9.75,2 10,2H14C14.25,2 14.46,2.18 14.5,2.42L14.87,5.07C15.5,5.32 16.04,5.66 16.56,6.05L19.05,5.05C19.27,4.96 19.54,5.05 19.66,5.27L21.66,8.73C21.79,8.95 21.73,9.22 21.54,9.37L19.43,11.03L19.5,12L19.43,12.97L21.54,14.63C21.73,14.78 21.79,15.05 21.66,15.27L19.66,18.73C19.54,18.95 19.27,19.04 19.05,18.95L16.56,17.95C16.04,18.34 15.5,18.68 14.87,18.93L14.5,21.58C14.46,21.82 14.25,22 14,22H10M11.25,4L10.88,6.61C9.68,6.86 8.62,7.5 7.85,8.39L5.44,7.35L4.69,8.65L6.8,10.2C6.4,11.37 6.4,12.64 6.8,13.8L4.68,15.36L5.43,16.66L7.86,15.62C8.63,16.5 9.68,17.14 10.87,17.38L11.24,20H12.76L13.13,17.39C14.32,17.14 15.37,16.5 16.14,15.62L18.57,16.66L19.32,15.36L17.2,13.81C17.6,12.64 17.6,11.37 17.2,10.2L19.31,8.65L18.56,7.35L16.15,8.39C15.38,7.5 14.32,6.86 13.12,6.61L12.75,4H11.25Z"/>
                    </svg>
                </button>
                <button class="action-btn" title="New Chat">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                </svg>
                <input 
                    type="text" 
                    placeholder="Search Messages" 
                    ng-model="searchQuery" 
                    ng-change="filterStudents()"
                    class="search-input"
                >
            </div>
        </div>

        <!-- Chat List -->
        <div class="chat-list">
            <!-- Loading State -->
            <div ng-show="isLoading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading conversations...</p>
            </div>

            <!-- No Students -->
            <div ng-show="!isLoading && filteredStudents.length === 0" class="empty-state">
                <p ng-show="!searchQuery">No students available to chat with</p>
                <p ng-show="searchQuery">No results found for "{{ searchQuery }}"</p>
            </div>

            <!-- Students List -->
            <div ng-repeat="student in filteredStudents track by student.id" 
                 ng-if="student && student.email"
                 class="chat-item" 
                 ng-class="{ 'active': isStudentSelected(student) }"
                 ng-click="selectStudent(student)">
                <div class="chat-avatar">
                    <span>{{ student.name ? student.name.charAt(0).toUpperCase() : '?' }}</span>
                    <div class="status-indicator" ng-class="getStatusClass(student)"></div>
                </div>
                <div class="chat-content">
                    <div class="chat-header">
                        <h4 class="chat-name">{{ student.name || 'Unknown' }}</h4>
                        <span class="chat-time" ng-show="getLastMessageTime(student)">
                            {{ getLastMessageTime(student) }}
                        </span>
                    </div>
                    <div class="chat-preview-row">
                        <p class="chat-preview">{{ getLastMessagePreview(student) }}</p>
                        <span class="chat-status-small" ng-class="getStatusClass(student)">
                            <div class="status-dot" ng-class="getStatusClass(student)"></div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <!-- Welcome State -->
        <div ng-show="!selectedStudent" class="welcome-screen">
            <div class="welcome-content">
                <svg class="welcome-icon" width="80" height="80" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4A2,2 0 0,0 20,2M6,9V7H18V9H6M14,11V13H6V11H14M16,15V17H6V15H16Z"/>
                </svg>
                <h2>Welcome to Chat</h2>
                <p>Select a conversation from the sidebar to start messaging</p>
            </div>
        </div>

        <!-- Chat Interface -->
        <div ng-show="selectedStudent" class="chat-interface">
            <!-- Chat Header -->
            <div class="chat-header-bar" ng-show="selectedStudent">
                <div class="chat-partner-info">
                    <div class="partner-avatar">
                        <span>{{ selectedStudent.name ? selectedStudent.name.charAt(0).toUpperCase() : '?' }}</span>
                        <div class="status-indicator" ng-class="getStatusClass(selectedStudent)"></div>
                    </div>
                    <div class="partner-details">
                        <h3>{{ selectedStudent.name || 'Unknown User' }}</h3>
                        <p class="partner-status" ng-class="getStatusClass(selectedStudent)">
                            {{ getStatusDisplay(selectedStudent) }}
                        </p>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="action-btn" title="Call">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z"/>
                        </svg>
                    </button>
                    <button class="action-btn" title="Video Call">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17,10.5V7A1,1 0 0,0 16,6H4A1,1 0 0,0 3,7V17A1,1 0 0,0 4,18H16A1,1 0 0,0 17,17V13.5L21,17.5V6.5L17,10.5Z"/>
                        </svg>
                    </button>
                    <button class="action-btn" title="More Options">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-container">
                <div ng-show="messages.length === 0" class="no-messages">
                    <p>No messages yet. Start a conversation with {{ selectedStudent.name }}!</p>
                </div>
                
                <div ng-repeat="message in messages" class="message-wrapper">
                    <div class="message" ng-class="{ 'sent': isMyMessage(message), 'received': !isMyMessage(message) }">
                        <div class="message-content">
                            <p>{{ message.message }}</p>
                        </div>
                        <div class="message-info">
                            <span class="message-sender">{{ getSenderName(message) }}</span>
                            <span class="message-time">{{ message.created_at | date:'short' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input -->
            <div class="message-input-container">
                <form name="chatForm" ng-submit="sendMessage()" novalidate class="message-form">
                    <div class="input-wrapper">
                        <button type="button" class="attachment-btn" title="Attach File">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16.5,6V17.5A4,4 0 0,1 12.5,21.5A4,4 0 0,1 8.5,17.5V5A2.5,2.5 0 0,1 11,2.5A2.5,2.5 0 0,1 13.5,5V15.5A1,1 0 0,1 12.5,16.5A1,1 0 0,1 11.5,15.5V6H10V15.5A2.5,2.5 0 0,0 12.5,18A2.5,2.5 0 0,0 15,15.5V5A4,4 0 0,0 11,1A4,4 0 0,0 7,5V17.5A5.5,5.5 0 0,0 12.5,23A5.5,5.5 0 0,0 18,17.5V6H16.5Z"/>
                            </svg>
                        </button>
                        <textarea 
                            id="newMessage"
                            name="newMessage"
                            ng-model="newMessage"
                            ng-keydown="handleKeyPress($event)"
                            ng-change="autoResize()"
                            placeholder="Type a message..."
                            class="message-input"
                            rows="1"
                            required
                        ></textarea>
                        <button type="submit" class="send-btn" ng-disabled="!newMessage" title="Send Message">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.chat-app {
    display: flex;
    height: calc(100vh - 120px);
    min-height: 600px;
    background: #f0f2f5;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Sidebar Styles */
.chat-sidebar {
    width: 320px;
    background: #ffffff;
    border-right: 1px solid #e4e6ea;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 16px;
    border-bottom: 1px solid #e4e6ea;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.user-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1c1e21;
}

.user-status {
    margin: 0;
    font-size: 12px;
    color: #65676b;
}

.sidebar-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #f0f2f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #65676b;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #e4e6ea;
    color: #1c1e21;
}

.search-container {
    padding: 8px 16px;
    border-bottom: 1px solid #e4e6ea;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #65676b;
    z-index: 1;
}

.search-input {
    width: 100%;
    padding: 8px 12px 8px 40px;
    border: 1px solid #e4e6ea;
    border-radius: 20px;
    background: #f0f2f5;
    font-size: 14px;
    outline: none;
    transition: all 0.2s ease;
}

.search-input:focus {
    background: #ffffff;
    border-color: #1877f2;
}

.chat-list {
    flex: 1;
    overflow-y: auto;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #65676b;
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #f0f2f5;
    border-top: 3px solid #1877f2;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 12px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #65676b;
}

.chat-item {
    display: flex;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f0f2f5;
}

.chat-item:hover {
    background: #f0f2f5;
}

.chat-item.active {
    background: #e7f3ff;
    border-right: 3px solid #1877f2;
}

.chat-avatar {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 18px;
    margin-right: 12px;
}

.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #ffffff;
}

/* .status-online {
    background: #42b883;
}

.status-offline {
    background: #95a5a6;
} */

.chat-content {
    flex: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: between;
    margin-bottom: 4px;
}

.chat-name {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1c1e21;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.chat-time {
    font-size: 11px;
    color: #65676b;
    margin-left: 8px;
    white-space: nowrap;
}

.chat-preview-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.chat-status {
    font-size: 11px;
    font-weight: 500;
    padding: 2px 6px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.chat-status.status-online {
    color: #42b883;
    background: rgba(66, 184, 131, 0.1);
}

.chat-status.status-offline {
    color: #95a5a6;
    background: rgba(149, 165, 166, 0.1);
}

.chat-status-small {
    display: flex;
    align-items: center;
    margin-left: auto;
    flex-shrink: 0;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-dot.status-online {
    background: #42b883;
}

.status-dot.status-offline {
    background: #95a5a6;
}

.chat-preview {
    margin: 0;
    font-size: 12px;
    color: #65676b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    line-height: 1.3;
}

/* Main Chat Area */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

.welcome-screen {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.welcome-content {
    text-align: center;
    max-width: 400px;
    padding: 40px;
}

.welcome-icon {
    color: #65676b;
    margin-bottom: 20px;
}

.welcome-content h2 {
    margin: 0 0 12px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1c1e21;
}

.welcome-content p {
    margin: 0;
    font-size: 16px;
    color: #65676b;
    line-height: 1.5;
}

.chat-interface {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.chat-header-bar {
    padding: 16px 20px;
    border-bottom: 1px solid #e4e6ea;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}

.chat-partner-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.partner-avatar {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.partner-details h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1c1e21;
}

.partner-status {
    margin: 0;
    font-size: 12px;
    font-weight: 500;
}

.partner-status.status-online {
    color: #42b883;
}

.partner-status.status-offline {
    color: #95a5a6;
}

.chat-actions {
    display: flex;
    gap: 8px;
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
}

.no-messages {
    text-align: center;
    padding: 40px 20px;
    color: #65676b;
}

.message-wrapper {
    margin-bottom: 16px;
}

.message {
    max-width: 70%;
    margin-bottom: 4px;
}

.message.sent {
    margin-left: auto;
}

.message.received {
    margin-right: auto;
}

.message-content {
    padding: 12px 16px;
    border-radius: 18px;
    word-wrap: break-word;
    line-height: 1.4;
}

.message.sent .message-content {
    background: linear-gradient(135deg, #1877f2, #0d8bf2);
    color: white;
}

.message.received .message-content {
    background: #f0f2f5;
    color: #1c1e21;
}

.message-content p {
    margin: 0;
    font-size: 14px;
}

.message-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    font-size: 11px;
    color: #65676b;
}

.message.sent .message-info {
    justify-content: flex-end;
}

.message.received .message-info {
    justify-content: flex-start;
}

.message-input-container {
    padding: 16px 20px;
    border-top: 1px solid #e4e6ea;
    background: #ffffff;
}

.message-form {
    width: 100%;
}

.input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    background: #f0f2f5;
    border-radius: 20px;
    padding: 8px;
}

.attachment-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #65676b;
    transition: all 0.2s ease;
}

.attachment-btn:hover {
    background: rgba(0,0,0,0.05);
    color: #1c1e21;
}

.message-input {
    flex: 1;
    border: none;
    background: transparent;
    resize: none;
    outline: none;
    font-size: 14px;
    color: #1c1e21;
    line-height: 1.4;
    max-height: 100px;
    overflow-y: auto;
    padding: 8px 0;
}

.message-input::placeholder {
    color: #65676b;
}

.send-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #1877f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    transition: all 0.2s ease;
}

.send-btn:hover:not(:disabled) {
    background: #166fe5;
    transform: scale(1.05);
}

.send-btn:disabled {
    background: #e4e6ea;
    color: #bcc0c4;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chat-app {
        height: calc(100vh - 60px);
    }
    
    .chat-sidebar {
        width: 280px;
    }
    
    .message {
        max-width: 85%;
    }
    
    .chat-header-bar {
        padding: 12px 16px;
    }
    
    .messages-container {
        padding: 12px 16px;
    }
    
    .message-input-container {
        padding: 12px 16px;
    }
}

@media (max-width: 480px) {
    .chat-sidebar {
        width: 100%;
        position: absolute;
        z-index: 10;
        height: 100%;
    }
    
    .chat-main {
        width: 100%;
    }
    
    .welcome-content {
        padding: 20px;
    }
    
    .welcome-content h2 {
        font-size: 20px;
    }
}

/* Scrollbar Styling */
.chat-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar {
    width: 6px;
}

.chat-list::-webkit-scrollbar-track,
.messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.chat-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.chat-list::-webkit-scrollbar-thumb:hover,
.messages-container::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

/* Animation for new messages */
.message-wrapper {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>