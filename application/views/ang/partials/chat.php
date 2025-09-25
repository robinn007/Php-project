<div class="chat-container">
    <h1>Chat</h1>
    
    <!-- Flash Message -->
    <div flash-message ng-show="flashMessage" class="flash-message flash-{{ flashType }}">
        {{ flashMessage }}
    </div>

    <!-- Receiver Selection -->
    <div class="form-group">
        <label for="receiverEmail">Send to:</label>
        <input 
            type="email" 
            id="receiverEmail" 
            name="receiverEmail" 
            ng-model="receiverEmail" 
            placeholder="Enter recipient email"
            class="form-control"
        >
    </div>

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
        <button type="submit" class="btn btn-primary" ng-disabled="!newMessage || !receiverEmail">Send</button>
    </form>
</div>

<style>
.chat-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}
.messages {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 20px;
}
.message {
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 5px;
}
.message.sent {
    background-color: #e1f5fe;
    margin-left: 20%;
}
.message.received {
    background-color: #f5f5f5;
    margin-right: 20%;
}
.form-group {
    margin-bottom: 15px;
}
.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-primary {
    background-color: #007bff;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}
.no-messages {
    text-align: center;
    color: #666;
}
</style>