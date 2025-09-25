/**
 * @file SocketService.js
 * @description Service for managing Socket.IO connection for real-time status and chat.
 */
angular.module('myApp').factory('SocketService', ['$rootScope', 'AuthService', function($rootScope, AuthService) {
    var socket = null;

    // Initialize Socket.IO connection
    function connect() {
        if (!socket) {
            socket = io('http://localhost:3000', {
                reconnection: true,
                reconnectionAttempts: 5,
                reconnectionDelay: 1000
            });
            console.log('SocketService: Attempting connection to http://localhost:3000');

            // Handle connection
            socket.on('connect', function() {
                console.log('SocketService: Socket connected, ID:', socket.id);
                // Emit user_login event if user is logged in
                if (AuthService.isLoggedIn()) {
                    var email = AuthService.getCurrentUserEmail();
                    console.log('SocketService: Emitting user_login for', email);
                    socket.emit('user_login', { email: email });
                }
            });

            // Handle connection errors
            socket.on('connect_error', function(error) {
                console.error('SocketService: Connection error:', error);
            });

            // Handle disconnection
            socket.on('disconnect', function() {
                console.log('SocketService: Socket disconnected');
                // Emit user_logout if user was logged in
                if (AuthService.isLoggedIn()) {
                    var email = AuthService.getCurrentUserEmail();
                    console.log('SocketService: Emitting user_logout for', email);
                    socket.emit('user_logout', { email: email });
                }
            });

            // Listen for status updates
            socket.on('status_update', function(data) {
                console.log('SocketService: Received status_update:', data);
                $rootScope.$apply(function() {
                    $rootScope.$broadcast('status_update', data);
                });
            });

            // Listen for chat messages
            socket.on('chat_message', function(data) {
                console.log('SocketService: Received chat_message:', data);
                $rootScope.$apply(function() {
                    $rootScope.$broadcast('chat_message', data);
                });
            });
        }
        return socket;
    }

    // Initialize connection on service load
    connect();

    return {
        on: function(eventName, callback) {
            socket.on(eventName, function() {
                var args = arguments;
                $rootScope.$apply(function() {
                    callback.apply(socket, args);
                });
            });
        },
        emit: function(eventName, data, callback) {
            socket.emit(eventName, data, function() {
                var args = arguments;
                $rootScope.$apply(function() {
                    if (callback) {
                        callback.apply(socket, args);
                    }
                });
            });
        },
        getSocket: function() {
            return socket;
        },
        reconnect: function() {
            console.log('SocketService: Reconnecting socket');
            socket = null;
            connect();
        }
    };
}]);