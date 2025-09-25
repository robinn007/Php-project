/**
 * @file ChatController.js
 * @description Controller for managing real-time chat functionality.
 */
angular.module('myApp').controller('ChatController', ['$scope', 'SocketService', 'AuthService', '$location', function($scope, SocketService, AuthService, $location) {
    $scope.messages = [];
    $scope.newMessage = '';
    $scope.receiverEmail = '';
    $scope.senderEmail = AuthService.getCurrentUserEmail();

    console.log('ChatController initialized for user:', $scope.senderEmail);

    // Redirect to login if not authenticated
    if (!AuthService.isLoggedIn()) {
        $location.path('/login').search({ logout: 'true' });
        return;
    }

    // Listen for incoming chat messages
    $scope.$on('chat_message', function(event, data) {
        console.log('ChatController: Received chat_message:', data);
        if (data.sender_email === $scope.receiverEmail || data.receiver_email === $scope.senderEmail) {
            $scope.messages.push({
                sender_email: data.sender_email,
                message: data.message,
                created_at: data.created_at
            });
            $scope.$apply();
        }
    });

    // Send a chat message
    $scope.sendMessage = function() {
        if (!$scope.newMessage || !$scope.receiverEmail) {
            console.log('ChatController: Missing message or receiver email');
            return;
        }

        var messageData = {
            sender_email: $scope.senderEmail,
            receiver_email: $scope.receiverEmail,
            message: $scope.newMessage
        };

        console.log('ChatController: Sending message:', messageData);
        SocketService.emit('chat_message', messageData, function(response) {
            console.log('ChatController: Message sent response:', response);
            $scope.newMessage = ''; // Clear input
            $scope.$apply();
        });
    };

    // Load initial messages (optional: fetch from backend if needed)
    function loadMessages() {
    AjaxHelper.ajaxRequest('GET', '/auth/get_messages', { receiver_email: $scope.receiverEmail })
        .then(function(response) {
            if (response.data.success) {
                $scope.messages = response.data.messages || [];
                console.log('ChatController: Loaded messages:', $scope.messages);
                $scope.$apply();
            } else {
                console.error('ChatController: Failed to load messages:', response.data.message);
            }
        })
        .catch(function(error) {
            console.error('ChatController: Error loading messages:', error);
        });
}
    loadMessages();
}]);