<!-- <ng-include src="'views/partials/header.php'" ng-init="showBreadcrumb=true; breadcrumbText='Ascending'"></ng-include>
<ng-include src="'views/partials/flash-message.php'"></ng-include>
<a href="/ci/ang/students/add" class="btn btn-add">Add New Student</a>
<ng-include src="'views/partials/table-students.php'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 100px; background-color: aqua;"></ng-include> -->

<!-- <ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include>
<ng-include src="'/partials/flash-message'"></ng-include>
<a href="/students/add" class="btn btn-add">Add New Student</a>
<ng-include src="'/partials/table-students'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 100px; background-color: aqua;"></ng-include> -->

<ng-include src="'/partials/header'" ng-init="showBreadcrumb=true"></ng-include>
<ng-include src="'/partials/flash-message'"></ng-include>
<div class="filters-container" style="margin-bottom: 20px;">
    <search-filter search-text="searchText" on-search="handleSearch(searchText)"></search-filter>
    <state-filter 
        selected-states="selectedStates" 
        on-state-change="handleStateChange(states)">
    </state-filter>
</div>
<a href="/students/add" class="btn btn-add">Add New Student</a>
<ng-include src="'/partials/table-students'" ng-init="tableTitle='Students'; showEdit=true; showDelete=true" style="margin-top: 20px;"></ng-include>

<!-- Chat Inbox -->
<div class="chat-inbox" ng-show="selectedStudentEmail">
    <h2>Chat with {{ selectedStudentEmail }}</h2>

    <!-- Messages Display -->
    <div class="messages" ng-show="messages.length">
        <div ng-repeat="message in messages" class="message" ng-class="{'sent': message.sender_email === senderEmail, 'received': message.sender_email !== senderEmail}">
            <p><strong>{{ message.sender_email }}</strong> ({{ message.created_at | date:'short' }}):</p>
            <p>{{ message.message }}</p>
        </div>
    </div>
    <div ng-show="!messages.length" class="no-messages">
        <p>No messages yet. Start a conversation!</p>
    </div>

    <!-- Message Input -->
    <form name="chatForm" ng-submit="sendMessage()" novalidate>
        <div class="form-group">
            <label for="newMessage">Message:</label>
            <textarea 
                id="newMessage" 
                name="newMessage" 
                ng-model="newMessage" 
                required
                class="form-control"
                placeholder="Type your message..."
            ></textarea>
        </div>
        <button type="submit" class="btn btn-primary" ng-disabled="!newMessage">Send</button>
    </form>
</div>