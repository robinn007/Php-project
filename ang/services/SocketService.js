/**
 * @file SocketService.js
 * @description Service for Socket.IO real-time status updates.
 */
angular.module('myApp').factory('SocketService', ['$rootScope', function($rootScope) {
    var socket = io('http://localhost:3000'); // Connect to Node.js server

    console.log('SocketService initialized');

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
        }
    };
}]);