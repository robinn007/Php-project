<!-- <ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include> -->
<ng-include src="'/partials/flash-message'"></ng-include>

<div class="chat-app-three-panel" ng-controller="ChatController" data-debug="chat-controller">
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
            <div ng-repeat="item in filteredConversations track by (item.group_id || item.id)" 
                 ng-if="item && (item.email || item.group_id)"
                 class="chat-item" 
                 ng-class="{ 
                     'active': (isGroup(item) && isGroupSelected(item)) || (!isGroup(item) && isStudentSelected(item)),
                     'group-chat': isGroup(item)
                 }"
                 ng-click="isGroup(item) ? selectGroup(item) : selectStudent(item)">
                
                <!-- Group Chat Item -->
                <div ng-if="isGroup(item)" class="chat-avatar group-avatar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z"/>
                    </svg>
                    <div class="group-indicator"></div>
                </div>
                
                <!-- Direct Chat Item -->
                <div ng-if="!isGroup(item)" class="chat-avatar">
                    <span>{{ item.name ? item.name.charAt(0).toUpperCase() : '?' }}</span>
                    <div class="status-indicator" ng-class="getStatusClass(item)"></div>
                </div>
                
                <div class="chat-content">
                    <div class="chat-header">
                        <h4 class="chat-name">{{ isGroup(item) ? item.name : (item.name || 'Unknown') }}</h4>
                        <span class="chat-time" ng-show="getLastMessageTime(item)">
                            {{ getLastMessageTime(item) }}
                        </span>
                    </div>
                    <div class="chat-preview-row">
                        <p class="chat-preview">{{ getLastMessagePreview(item) }}</p>
                        <span class="chat-status-small" ng-if="!isGroup(item)" ng-class="getStatusClass(item)">
                            <div class="status-dot" ng-class="getStatusClass(item)"></div>
                        </span>
                        <span class="group-member-count" ng-if="isGroup(item)">
                            {{ item.member_count }} members
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="chat-main">
        <!-- Welcome State -->
        <div ng-show="!selectedStudent && !selectedGroup" class="welcome-screen">
            <div class="welcome-content">
                <svg class="welcome-icon" width="80" height="80" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20,2H4A2,2 0 0,0 2,4V22L6,18H20A2,2 0 0,0 22,16V4A2,2 0 0,0 20,2M6,9V7H18V9H6M14,11V13H6V11H14M16,15V17H6V15H16Z"/>
                </svg>
                <h2>Welcome to Chat</h2>
                <p>Select a conversation from the left or start a new chat from the contacts on the right</p>
            </div>
        </div>

        <!-- Chat Interface -->
        <div ng-show="selectedStudent || selectedGroup" class="chat-interface">
            <!-- Chat Header -->
            <div class="chat-header-bar">
                <div class="chat-partner-info">
                    <div class="partner-avatar" ng-class="{ 'group-avatar': isGroupChat() }">
                        <span ng-if="!isGroupChat()">{{ getCurrentChatName().charAt(0).toUpperCase() }}</span>
                        <svg ng-if="isGroupChat()" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z"/>
                        </svg>
                        <div class="status-indicator" ng-if="!isGroupChat()" ng-class="getCurrentChatStatusClass()"></div>
                    </div>
                    <div class="partner-details">
                        <h3>{{ getCurrentChatName() }}</h3>
                        <p class="partner-status" ng-class="getCurrentChatStatusClass()">
                            {{ getCurrentChatStatus() }}
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

            <div class="typing-indicator-bar" ng-show="getTypingIndicator() || getGroupTypingIndicator()">
    <div class="typing-indicator-content">
        <span class="typing-text">
            {{ chatType === 'group' ? getGroupTypingIndicator() : getTypingIndicator() }}
        </span>
        <div class="typing-dots">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>
</div>

            <!-- Messages Area -->
            <div class="messages-container">
                <div ng-show="messages.length === 0" class="no-messages">
                    <p ng-if="isGroupChat()">No messages yet in {{ getCurrentChatName() }}. Start the conversation!</p>
                    <p ng-if="!isGroupChat()">No messages yet. Start a conversation with {{ getCurrentChatName() }}!</p>
                </div>
                
                <div ng-repeat="message in messages" class="message-wrapper">
                    <div class="message" ng-class="{ 
                        'sent': isMyMessage(message), 
                        'received': !isMyMessage(message),
                        'group-message': isGroupChat()
                    }">
                        <div class="message-content">
                            <p>{{ message.message }}</p>
                        </div>
                        <div class="message-info">
                            <span class="message-sender" ng-if="isGroupChat() || !isMyMessage(message)">{{ getSenderName(message) }}</span>
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

    <!-- Right Sidebar - All Students with Group Creation -->
    <div class="students-sidebar">
        <!-- Header with Group Creation Button -->
        <div class="sidebar-header">
            <div class="user-info">
                <h3>All Students</h3>
                <p class="user-status">{{ filteredAllStudents.length }} contacts</p>
            </div>
            <div class="sidebar-actions">
                <button class="action-btn create-group-btn" 
                        ng-click="openCreateGroupModal()" 
                        title="Create Group" 
                        data-debug="create-group-button"
                        ng-disabled="isLoading">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z"/>
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                    </svg>
                    Create Group
                </button>
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
                    <div class="student-location" ng-show="student.state">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                        </svg>
                        {{ student.state }}
                    </div>
                    <div class="student-status" ng-class="getStatusClass(student)">
                        <div class="status-dot" ng-class="getStatusClass(student)"></div>
                        {{ getStatusDisplay(student) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Group Modal - FIXED VERSION -->
    <div class="modal-overlay" ng-show="showCreateGroupModal" id="createGroupModal">
        <div class="modal-content create-group-modal" ng-click="$event.stopPropagation()">
            <!-- Flash Message Display -->
            <div class="flash-message" ng-show="flashMessage">
                <span class="{{ flashType }}">{{ flashMessage }}</span>
            </div>
            
            <div class="modal-header">
                <h3>Create New Group</h3>
                <button class="close-btn" ng-click="closeCreateGroupModal()" type="button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="groupName">Group Name</label>
                    <input type="text" 
                           id="groupName" 
                           ng-model="newGroupName" 
                           placeholder="Enter group name" 
                           class="form-input"
                           maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="groupDescription">Description (Optional)</label>
                    <textarea id="groupDescription" 
                              ng-model="newGroupDescription" 
                              placeholder="Enter group description" 
                              class="form-input"
                              rows="3"
                              maxlength="500"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Select Members</label>
                    <div class="member-selection">
                        <input type="text" 
                               ng-model="memberSearchQuery" 
                               placeholder="Search students" 
                               class="form-input member-search">
                        
                        <div class="member-list">
                            <div ng-repeat="student in allStudents | filter:memberSearchQuery" 
                                 class="member-item" 
                                 ng-class="{'selected': student.selected}"
                                 ng-click="toggleMemberSelection(student)">
                                <div class="member-checkbox">
                                    <svg ng-show="student.selected" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z"/>
                                    </svg>
                                </div>
                                <div class="student-avatar member-avatar">
                                    <span>{{ student.name ? student.name.charAt(0).toUpperCase() : '?' }}</span>
                                </div>
                                <div class="member-info">
                                    <div class="member-name">{{ student.name || 'Unknown' }}</div>
                                    <div class="member-email">{{ student.email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display selected members -->
                    <div class="selected-members" ng-show="selectedMembers.length > 0">
                        <h4>Selected Members ({{ selectedMembers.length }})</h4>
                        <div class="selected-member-list">
                            <div ng-repeat="email in selectedMembers" class="selected-member-chip">
                                <span>{{ email }}</span>
                                <button type="button" ng-click="toggleMember(email)">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-primary" 
                        ng-click="createGroup()" 
                        ng-disabled="isLoading || !newGroupName || selectedMembers.length === 0"
                        type="button">
                    <span ng-hide="isLoading">Create Group</span>
                    <span ng-show="isLoading">Creating...</span>
                </button>
                <button class="btn btn-secondary" 
                        ng-click="closeCreateGroupModal()"
                        type="button">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Chat App Layout */
.chat-app-three-panel {
    display: flex;
    height: 100vh;
    background: #f5f5f5;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Left Sidebar - Conversations */
.chat-conversations-sidebar {
    width: 350px;
    background: white;
    border-right: 1px solid #e1e5e9;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Main Chat Area */
.chat-main {
    flex: 1;
    background: white;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Right Sidebar - Students */
.students-sidebar {
    width: 300px;
    background: white;
    border-left: 1px solid #e1e5e9;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Sidebar Headers */
.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #e1e5e9;
    background: #fafafa;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar, .chat-avatar, .student-avatar, .partner-avatar, .member-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
    position: relative;
}

.user-info h3, .partner-details h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.user-status, .partner-status {
    margin: 2px 0 0 0;
    font-size: 12px;
    color: #666;
}

.sidebar-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: #f0f0f0;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.action-btn:hover {
    background: #e0e0e0;
    color: #333;
}

.create-group-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
}

.create-group-btn:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: scale(1.05);
}

/* Search Containers */
.search-container {
    padding: 16px;
    border-bottom: 1px solid #e1e5e9;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: #999;
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border: 2px solid #e1e5e9;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s ease;
}

.search-input:focus {
    border-color: #667eea;
}

/* Chat Lists */
.chat-list, .students-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0;
}

.chat-item, .student-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    gap: 12px;
    border-left: 3px solid transparent;
}

.chat-item:hover, .student-item:hover {
    background: #f8f9fa;
}

.chat-item.active {
    background: #e3f2fd;
    border-left-color: #667eea;
}

.chat-item.group-chat {
    border-left: 3px solid #667eea;
}

.chat-content, .student-info {
    flex: 1;
    min-width: 0;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.chat-name, .student-name {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-time {
    font-size: 11px;
    color: #999;
    flex-shrink: 0;
    margin-left: 8px;
}

.chat-preview-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-preview {
    font-size: 13px;
    color: #666;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.student-location {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #999;
    margin: 2px 0;
}

.student-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
}

/* Status Indicators */
.status-indicator, .status-dot {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-online {
    color: #28a745;
}

.status-offline {
    color: #6c757d;
}

.status-dot.status-online {
    background: #28a745;
}

.status-dot.status-offline {
    background: #6c757d;
}

/* Group Specific Styles */
.group-avatar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.group-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    border: 2px solid white;
}

.group-member-count {
    font-size: 11px;
    color: #666;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 10px;
    white-space: nowrap;
}

/* Loading and Empty States */
.loading-state, .empty-state, .no-messages {
    padding: 40px 20px;
    text-align: center;
    color: #666;
}

.loading-spinner {
    width: 24px;
    height: 24px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Welcome Screen */
.welcome-screen {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.welcome-content {
    text-align: center;
    max-width: 400px;
    padding: 40px;
}

.welcome-icon {
    color: #667eea;
    margin-bottom: 24px;
}

.welcome-content h2 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px 0;
}

.welcome-content p {
    color: #666;
    font-size: 16px;
    line-height: 1.5;
    margin: 0;
}

/* Chat Interface */
.chat-interface {
    display: flex;
    flex-direction: column;
    height: 100vh;
}

.chat-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e1e5e9;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.chat-partner-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.partner-details h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.partner-status {
    margin: 2px 0 0 0;
    font-size: 14px;
}

.partner-status.status-online {
    color: #28a745;
}

.partner-status.status-offline {
    color: #6c757d;
} 

.partner-status.group-status {
    color: #667eea;
}

.chat-actions {
    display: flex;
    gap: 8px;
}

/* Messages Container */
.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    background: #fafafa;
}

.message-wrapper {
    margin-bottom: 16px;
}

.message {
    max-width: 70%;
    word-wrap: break-word;
}

.message.sent {
    margin-left: auto;
}

.message.received {
    margin-right: auto;
}

.message-content {
    background: white;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message.sent .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.message-content p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
}

.message-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
    padding: 0 8px;
    font-size: 11px;
    color: #999;
}

.message.sent .message-info {
    flex-direction: row-reverse;
}

.message-sender {
    font-weight: 600;
    color: #667eea;
}

.message.group-message .message-sender {
    color: #667eea;
}

/* Message Input */
.message-input-container {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid #e1e5e9;
}

.message-form {
    width: 100%;
}

.input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    background: #f8f9fa;
    border-radius: 24px;
    padding: 8px;
}

.attachment-btn, .send-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.attachment-btn {
    background: none;
    color: #666;
}

.attachment-btn:hover {
    background: #e9ecef;
    color: #333;
}

.send-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.send-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: scale(1.05);
}

.send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.message-input {
    flex: 1;
    border: none;
    background: none;
    resize: none;
    outline: none;
    font-size: 14px;
    font-family: inherit;
    max-height: 100px;
    padding: 8px 12px;
    line-height: 1.4;
}

.message-input::placeholder {
    color: #999;
}

/* FIXED MODAL STYLES - The main fixes are here */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

/* Show modal when ng-show is true */
.modal-overlay[ng-show="true"] {
    opacity: 1 !important;
    visibility: visible !important;
}

/* AngularJS ng-hide class override */
.modal-overlay:not(.ng-hide) {
    opacity: 1 !important;
    visibility: visible !important;
}

.modal-overlay.ng-hide {
    opacity: 0 !important;
    visibility: hidden !important;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    transform: translateY(-20px) scale(0.95);
    transition: all 0.3s ease;
}

.modal-overlay:not(.ng-hide) .modal-content {
    transform: translateY(0) scale(1);
}

.flash-message {
    padding: 12px 20px;
    margin: 0;
    border-radius: 12px 12px 0 0;
    text-align: center;
    font-weight: 500;
}

.flash-message .success {
    color: #155724;
    background-color: #d4edda;
    border-bottom: 1px solid #c3e6cb;
}

.flash-message .error {
    color: #721c24;
    background-color: #f8d7da;
    border-bottom: 1px solid #f5c6cb;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0 24px;
    border-bottom: 1px solid #eee;
    margin-bottom: 0;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: -8px -8px 0 0;
}

.close-btn:hover {
    background: #f0f0f0;
    color: #333;
}

.modal-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
}

.form-input::placeholder {
    color: #999;
}

/* Member Selection Styles */
.member-selection {
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
}

.member-search {
    margin-bottom: 16px;
}

.member-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px;
}

.member-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    gap: 12px;
    border: 2px solid transparent;
}

.member-item:hover {
    background: #f8f9fa;
}

.member-item.selected {
    background: #e3f2fd;
    border-color: #667eea;
}

.member-info {
    flex: 1;
    min-width: 0;
}

.member-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
    margin-bottom: 2px;
}

.member-email {
    font-size: 12px;
    color: #666;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.member-checkbox {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 2px solid #ddd;
    flex-shrink: 0;
}

.member-item.selected .member-checkbox {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.selected-members {
    margin-top: 16px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
}

.selected-members h4 {
    margin: 0 0 12px 0;
    color: #667eea;
    font-size: 14px;
    font-weight: 600;
}

.selected-member-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.selected-member-chip {
    background: #667eea;
    color: white;
    padding: 6px 12px;
    border-radius: 16px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: chipIn 0.2s ease;
}

@keyframes chipIn {
    from { transform: scale(0.8); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.selected-member-chip button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 2px;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s ease;
}

.selected-member-chip button:hover {
    background: rgba(255, 255, 255, 0.2);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 24px;
    border-top: 1px solid #eee;
    background: #fafafa;
    border-radius: 0 0 12px 12px;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    font-size: 14px;
    min-width: 120px;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Animation for new messages */
.message-wrapper {
    animation: messageIn 0.3s ease;
}

@keyframes messageIn {
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
@media (max-width: 768px) {
    .chat-app-three-panel {
        flex-direction: column;
        height: auto;
    }
    
    .chat-conversations-sidebar,
    .students-sidebar {
        width: 100%;
        height: 300px;
    }
    
    .chat-main {
        height: calc(100vh - 600px);
        min-height: 400px;
    }
    
    .message {
        max-width: 85%;
    }
    
    .modal-content {
        width: 95%;
        margin: 10px;
    }
}

/* Scrollbar Styling */
.chat-list::-webkit-scrollbar,
.students-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar,
.member-selection::-webkit-scrollbar {
    width: 6px;
}

.chat-list::-webkit-scrollbar-track,
.students-list::-webkit-scrollbar-track,
.messages-container::-webkit-scrollbar-track,
.member-selection::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-list::-webkit-scrollbar-thumb,
.students-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb,
.member-selection::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.chat-list::-webkit-scrollbar-thumb:hover,
.students-list::-webkit-scrollbar-thumb:hover,
.messages-container::-webkit-scrollbar-thumb:hover,
.member-selection::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Focus styles for accessibility */
.action-btn:focus,
.search-input:focus,
.form-input:focus,
.btn:focus {
    outline: 2px solid #667eea;
    outline-offset: 2px;
}

.chat-item:focus,
.student-item:focus,
.member-item:focus {
    outline: 2px solid #667eea;
    outline-offset: -2px;
}

.typing-indicator-bar {
    padding: 8px 20px;
    background-color: #f5f7fa;
    border-bottom: 1px solid #e2e8f0;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.typing-indicator-content {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
}

.typing-text {
    font-style: italic;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dots .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #64748b;
    animation: typingBounce 1.4s infinite;
}

.typing-dots .dot:nth-child(1) {
    animation-delay: 0s;
}

.typing-dots .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots .dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typingBounce {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.7;
    }
    30% {
        transform: translateY(-8px);
        opacity: 1;
    }
}

/* Optional: Adjust messages-container to account for typing indicator */
.chat-interface .messages-container {
    /* Add transition for smooth height adjustment */
    transition: height 0.3s ease;
}

</style>