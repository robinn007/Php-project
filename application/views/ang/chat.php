<!-- <ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include> -->
<ng-include src="'/partials/flash-message'"></ng-include>

<div class="chat-app-three-panel" ng-controller="ChatController">
    <!-- Left Sidebar - Conversations Only -->
    <div class="chat-conversations-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="user-profile">
                <div class="user-avatar">
                    <span>{{ currentUser.charAt(0).toUpperCase() }}</span>
                </div>
                <div class="user-info">
                    <h3>{{ currentUser }}</h3>
                    <p class="user-status">Conversations</p>
                </div>
            </div>
            <div class="sidebar-actions">
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
                    placeholder="Search Conversations" 
                    ng-model="searchQuery" 
                    ng-change="filterConversations()"
                    class="search-input"
                >
            </div>
        </div>

        <!-- Conversations List -->
        <div class="chat-list">
            <!-- Loading State -->
            <div ng-show="isLoading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading conversations...</p>
            </div>

            <!-- No Conversations -->
            <div ng-show="!isLoading && filteredConversations.length === 0" class="empty-state">
                <p ng-show="!searchQuery">No conversations yet</p>
                <p ng-show="searchQuery">No results found for "{{ searchQuery }}"</p>
                <small>Start a chat from the contacts panel on the right</small>
            </div>

            <!-- Conversations List -->
            <div ng-repeat="student in filteredConversations track by student.id" 
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
                <p>Select a conversation from the left or start a new chat from the contacts on the right</p>
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

    <!-- Right Sidebar - All Students -->
    <div class="students-sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="user-info">
                <h3>All Students</h3>
                <p class="user-status">{{ filteredAllStudents.length }} contacts</p>
            </div>
            <div class="sidebar-actions">
                <button class="action-btn" title="Refresh">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.65,6.35C16.2,4.9 14.21,4 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20C15.73,20 18.84,17.45 19.73,14H17.65C16.83,16.33 14.61,18 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6C13.66,6 15.14,6.69 16.22,7.78L13,11H20V4L17.65,6.35Z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search Bar for Students -->
        <div class="search-container">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"/>
                </svg>
                <input 
                    type="text" 
                    placeholder="Search Students" 
                    ng-model="studentSearchQuery" 
                    ng-change="filterAllStudents()"
                    class="search-input"
                >
            </div>
        </div>

        <!-- Students List -->
        <div class="students-list">
            <!-- Loading State -->
            <div ng-show="isLoading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading students...</p>
            </div>

            <!-- No Students -->
            <div ng-show="!isLoading && filteredAllStudents.length === 0" class="empty-state">
                <p ng-show="!studentSearchQuery">No students available</p>
                <p ng-show="studentSearchQuery">No results found for "{{ studentSearchQuery }}"</p>
            </div>

            <!-- Students List -->
            <div ng-repeat="student in filteredAllStudents track by student.id" 
                 ng-if="student && student.email"
                 class="student-item" 
                 ng-click="selectStudent(student)">
                <div class="student-avatar">
                    <span>{{ student.name ? student.name.charAt(0).toUpperCase() : '?' }}</span>
                    <div class="status-indicator" ng-class="getStatusClass(student)"></div>
                </div>
                <div class="student-info">
                    <div class="student-name">{{ student.name || 'Unknown' }}</div>
                    <div class="student-location" ng-show="student.location">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                        </svg>
                        {{ student.location }}
                    </div>
                    <div class="student-status" ng-class="getStatusClass(student)">
                        <div class="status-dot" ng-class="getStatusClass(student)"></div>
                        {{ getStatusDisplay(student) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.chat-app-three-panel {
    display: flex;
    height: calc(100vh - 120px);
    min-height: 600px;
    background: #f0f2f5;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Left Sidebar - Conversations */
.chat-conversations-sidebar {
    width: 300px;
    background: #ffffff;
    border-right: 1px solid #e4e6ea;
    display: flex;
    flex-direction: column;
}

/* Right Sidebar - All Students */
.students-sidebar {
    width: 280px;
    background: #ffffff;
    border-left: 1px solid #e4e6ea;
    display: flex;
    flex-direction: column;
}

/* Common Sidebar Styles */
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

.chat-list,
.students-list {
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

.empty-state small {
    display: block;
    margin-top: 8px;
    font-size: 11px;
    color: #8a8d91;
}

/* Chat Item Styles */
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
    flex-shrink: 0;
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

.status-indicator.status-online {
    background: #42b883;
}

.status-indicator.status-offline {
    background: #95a5a6;
}

.chat-content {
    flex: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
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

/* Student Item Styles */
.student-item {
    display: flex;
    padding: 12px 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f0f2f5;
}

.student-item:hover {
    background: #f0f2f5;
}

.student-avatar {
    position: relative;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
    margin-right: 12px;
    flex-shrink: 0;
}

.student-info {
    flex: 1;
    min-width: 0;
}

.student-name {
    font-size: 14px;
    font-weight: 600;
    color: #1c1e21;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.student-location {
    font-size: 11px;
    color: #65676b;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.student-status {
    font-size: 11px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.student-status.status-online {
    color: #42b883;
}

.student-status.status-offline {
    color: #95a5a6;
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
    animation: fadeInUp 0.3s ease-out;
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

/* Responsive Design */
@media (max-width: 1200px) {
    .students-sidebar {
        width: 260px;
    }
    
    .chat-conversations-sidebar {
        width: 280px;
    }
}

@media (max-width: 992px) {
    .chat-app-three-panel {
        height: calc(100vh - 60px);
    }
    
    .students-sidebar {
        width: 240px;
    }
    
    .chat-conversations-sidebar {
        width: 260px;
    }
    
    .message {
        max-width: 80%;
    }
}

@media (max-width: 768px) {
    .students-sidebar {
        display: none;
    }
    
    .chat-conversations-sidebar {
        width: 300px;
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

@media (max-width: 640px) {
    .chat-conversations-sidebar {
        width: 100%;
        position: absolute;
        z-index: 10;
        height: 100%;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .chat-conversations-sidebar.show {
        transform: translateX(0);
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
.students-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar {
    width: 6px;
}

.chat-list::-webkit-scrollbar-track,
.students-list::-webkit-scrollbar-track,
.messages-container::-webkit-scrollbar-track {
    background: transparent;
}

.chat-list::-webkit-scrollbar-thumb,
.students-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.2);
    border-radius: 3px;
}

.chat-list::-webkit-scrollbar-thumb:hover,
.students-list::-webkit-scrollbar-thumb:hover,
.messages-container::-webkit-scrollbar-thumb:hover {
    background: rgba(0,0,0,0.3);
}

/* Focus states for accessibility */
.search-input:focus,
.message-input:focus {
    outline: 2px solid #1877f2;
    outline-offset: 1px;
}

.action-btn:focus,
.send-btn:focus,
.attachment-btn:focus {
    outline: 2px solid #1877f2;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .chat-item:hover,
    .student-item:hover {
        background: #000000;
        color: #ffffff;
    }
    
    .status-dot.status-online {
        background: #00ff00;
    }
    
    .status-dot.status-offline {
        background: #ff0000;
    }
}

</style>