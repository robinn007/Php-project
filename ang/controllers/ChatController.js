/**
 * @file ChatController.js
 * @description Optimized controller for managing the chat interface with efficient message-based sorting.
 */
angular.module('myApp').controller('ChatController', ['$scope', '$rootScope', 'AjaxHelper', 'SocketService', 'AuthService', '$location', '$timeout', function($scope, $rootScope, AjaxHelper, SocketService, AuthService, $location, $timeout) {
    console.log('ChatController initialized');
    
    // Initialize scope variables
    $scope.students = [];
    $scope.selectedStudent = null;
    $scope.selectedStudentEmail = '';
    $scope.messages = [];
    $scope.newMessage = '';
    $scope.senderEmail = AuthService.getCurrentUserEmail();
    $scope.currentUser = AuthService.getCurrentUser();
    $scope.isLoading = true;
    $scope.studentsLoaded = false;
    $scope.searchQuery = '';
    $scope.filteredStudents = [];
    $scope.conversationData = {}; // Store last message data for each student
    
    // Check if user is logged in
    if (!AuthService.isLoggedIn()) {
        console.log('User not logged in, redirecting to login');
        $location.path('/login').search({ logout: 'true' });
        return;
    }

    // Update conversation data when a new message is received
    function updateConversationData(studentEmail, message, timestamp) {
        if (!$scope.conversationData[studentEmail]) {
            $scope.conversationData[studentEmail] = {};
        }
        
        $scope.conversationData[studentEmail].lastMessage = message;
        $scope.conversationData[studentEmail].lastMessageTime = new Date(timestamp);
        $scope.conversationData[studentEmail].timestamp = new Date(timestamp).getTime();
        
        console.log('Updated conversation data for', studentEmail, ':', $scope.conversationData[studentEmail]);
    }

    // Get last message preview for a student
    $scope.getLastMessagePreview = function(student) {
        if (!student || !student.email) {
            return 'No messages yet';
        }
        
        var data = $scope.conversationData[student.email];
        if (data && data.lastMessage) {
            var preview = data.lastMessage.length > 45 ? 
                data.lastMessage.substring(0, 45) + '...' : 
                data.lastMessage;
            return preview;
        }
        return 'No messages yet';
    };

    // Get last message time for a student
    $scope.getLastMessageTime = function(student) {
        if (!student || !student.email) {
            return '';
        }
        
        var data = $scope.conversationData[student.email];
        if (data && data.lastMessageTime) {
            var now = new Date();
            var messageTime = new Date(data.lastMessageTime);
            var diffMs = now - messageTime;
            var diffMins = Math.floor(diffMs / 60000);
            var diffHours = Math.floor(diffMs / 3600000);
            var diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';
            if (diffHours < 24) return diffHours + 'h ago';
            if (diffDays < 7) return diffDays + 'd ago';
            return messageTime.toLocaleDateString();
        }
        return '';
    };

    // Sort students by last message time (most recent first)
    function sortStudentsByLastMessage() {
        $scope.students.sort(function(a, b) {
            var aData = $scope.conversationData[a.email];
            var bData = $scope.conversationData[b.email];
            
            var aTime = aData && aData.timestamp ? aData.timestamp : 0;
            var bTime = bData && bData.timestamp ? bData.timestamp : 0;
            
            // Sort descending (newest first)
            return bTime - aTime;
        });
        
        console.log('Students sorted by last message time');
        filterStudents();
    }

    // Filter students based on search query
    function filterStudents() {
        if (!$scope.searchQuery) {
            $scope.filteredStudents = $scope.students.filter(function(student) {
                return student && student.email && student.name;
            });
        } else {
            $scope.filteredStudents = $scope.students.filter(function(student) {
                if (!student || !student.email || !student.name) {
                    return false;
                }
                return student.name.toLowerCase().includes($scope.searchQuery.toLowerCase()) ||
                       student.email.toLowerCase().includes($scope.searchQuery.toLowerCase());
            });
        }
    }

    // Expose filter function to scope
    $scope.filterStudents = function() {
        filterStudents();
    };

    // Load conversation summary to determine order
    function loadConversationSummary() {
        console.log('Loading conversation summary for message-based ordering');
        
        AjaxHelper.ajaxRequest('GET', '/auth/get_last_messages_summary')
            .then(function(response) {
                console.log('Conversation summary loaded:', response.data.conversations);
                
                var conversations = response.data.conversations || [];
                
                // Update conversation data for each conversation
                conversations.forEach(function(conv) {
                    updateConversationData(
                        conv.other_person_email,
                        conv.message,
                        conv.created_at
                    );
                });
                
                // Sort students by conversation activity
                sortStudentsByLastMessage();
                $scope.$applyAsync();
                
            })
            .catch(function(error) {
                console.error('Error loading conversation summary:', error);
                // If we can't load the summary, just filter the students without sorting
                filterStudents();
                $scope.$applyAsync();
            });
    }

    // Select a student from the sidebar
    $scope.selectStudent = function(student) {
        if (!student || !student.email || !student.name) {
            console.error('Invalid student object passed to selectStudent:', student);
            return;
        }
        
        $scope.selectedStudent = student;
        $scope.selectedStudentEmail = student.email;
        $scope.messages = [];
        console.log('Selected student:', student.name, student.email);
        loadMessages();
    };

    // Check if student is selected
    $scope.isStudentSelected = function(student) {
        if (!student || !student.email || !$scope.selectedStudent) {
            return false;
        }
        return $scope.selectedStudent.email === student.email;
    };

    // Get student status display
    $scope.getStatusDisplay = function(student) {
        if (!student || !student.hasOwnProperty('status')) {
            return 'Offline';
        }
        return student.status === 'online' ? 'Online' : 'Offline';
    };

    // Get student status class for styling
    $scope.getStatusClass = function(student) {
        if (!student || !student.hasOwnProperty('status')) {
            return 'status-offline';
        }
        return student.status === 'online' ? 'status-online' : 'status-offline';
    };

    // Check if message is from current user
    $scope.isMyMessage = function(message) {
        return message.sender_email === $scope.senderEmail;
    };

    // Get sender display name
    $scope.getSenderName = function(message) {
        if (message.sender_email === $scope.senderEmail) {
            return 'You';
        }
        var student = $scope.students.find(function(s) {
            return s.email === message.sender_email;
        });
        return student ? student.name : message.sender_email;
    };

    // Fetch all students for the sidebar with retry mechanism
    function loadStudents(retryCount = 0) {
        console.log('Fetching students for chat, attempt:', retryCount + 1);
        $scope.isLoading = true;
        
        AjaxHelper.ajaxRequest('GET', '/students')
            .then(function(response) {
                console.log('Students fetched:', response.data.students);
                $scope.students = response.data.students.filter(function(student) {
                    return student.email !== $scope.senderEmail; // Exclude current user
                });
                $scope.studentsLoaded = true;
                $scope.isLoading = false;
                
                // Load conversation summary for sorting after students are loaded
                if ($scope.students.length > 0) {
                    loadConversationSummary();
                } else {
                    $scope.filteredStudents = [];
                    $scope.$applyAsync();
                }
                
            })
            .catch(function(error) {
                console.error('Error fetching students (attempt ' + (retryCount + 1) + '):', error);
                
                // Retry up to 3 times with exponential backoff
                if (retryCount < 2 && error.status === 0) {
                    var delay = Math.pow(2, retryCount) * 1000; // 1s, 2s, 4s
                    console.log('Retrying in', delay, 'ms');
                    
                    $timeout(function() {
                        loadStudents(retryCount + 1);
                    }, delay);
                } else {
                    $scope.isLoading = false;
                    $scope.flashMessage = error.flashMessage || 'Failed to load students';
                    $scope.flashType = error.flashType || 'error';
                    $rootScope.$emit('flashMessage', { 
                        message: $scope.flashMessage, 
                        type: $scope.flashType 
                    });
                    $scope.$applyAsync();
                }
            });
    }

    // Load messages for the selected student
    function loadMessages() {
        if (!$scope.selectedStudentEmail) {
            $scope.messages = [];
            return;
        }
        
        console.log('Loading messages for:', $scope.selectedStudentEmail);
        AjaxHelper.ajaxRequest('GET', '/auth/get_messages', { receiver_email: $scope.selectedStudentEmail })
            .then(function(response) {
                console.log('Messages fetched:', response.data.messages);
                $scope.messages = response.data.messages || [];
                $scope.$applyAsync();
                
                // Update conversation data with the latest message if exists
                if ($scope.messages.length > 0) {
                    var lastMessage = $scope.messages[$scope.messages.length - 1];
                    updateConversationData(
                        $scope.selectedStudentEmail, 
                        lastMessage.message, 
                        lastMessage.created_at
                    );
                }
                
                // Scroll to bottom of messages
                $timeout(function() {
                    var messagesContainer = document.querySelector('.messages-container');
                    if (messagesContainer) {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                }, 100);
            })
            .catch(function(error) {
                console.error('Error fetching messages:', error);
                $scope.messages = [];
                $scope.flashMessage = error.flashMessage || 'Failed to load messages';
                $scope.flashType = error.flashType || 'error';
                $rootScope.$emit('flashMessage', { 
                    message: $scope.flashMessage, 
                    type: $scope.flashType 
                });
                $scope.$applyAsync();
            });
    }

    // Send a new message
    $scope.sendMessage = function() {
        if (!$scope.newMessage || !$scope.selectedStudentEmail) {
            $scope.flashMessage = 'Please enter a message and select a student';
            $scope.flashType = 'error';
            $rootScope.$emit('flashMessage', { 
                message: $scope.flashMessage, 
                type: $scope.flashType 
            });
            return;
        }
        
        console.log('Sending message to:', $scope.selectedStudentEmail);
        
        // Update conversation data immediately for better UX
        updateConversationData($scope.selectedStudentEmail, $scope.newMessage, new Date());
        sortStudentsByLastMessage();
        
        SocketService.emit('chat_message', {
            sender_email: $scope.senderEmail,
            receiver_email: $scope.selectedStudentEmail,
            message: $scope.newMessage
        });
        $scope.newMessage = ''; // Clear the input
        
        // Auto-resize textarea back to original size
        var textarea = document.querySelector('#newMessage');
        if (textarea) {
            textarea.style.height = 'auto';
        }
    };

    // Auto-resize textarea
    $scope.autoResize = function() {
        $timeout(function() {
            var textarea = document.querySelector('#newMessage');
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            }
        });
    };

    // Listen for incoming chat messages
    SocketService.on('chat_message', function(data) {
        console.log('Received chat message:', data);
        
        // Update conversation data for the sender
        var otherPersonEmail = data.sender_email === $scope.senderEmail ? 
            data.receiver_email : data.sender_email;
        
        updateConversationData(otherPersonEmail, data.message, data.created_at);
        
        // If this message is part of the current conversation, add it to messages
        if (data.sender_email === $scope.selectedStudentEmail || 
            data.receiver_email === $scope.selectedStudentEmail) {
            $scope.messages.push(data);
            
            // Scroll to bottom
            $timeout(function() {
                var messagesContainer = document.querySelector('.messages-container');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 50);
        }
        
        // Re-sort students to move the conversation to top
        sortStudentsByLastMessage();
        $scope.$applyAsync();
    });

    // Listen for status updates
    SocketService.on('status_update', function(data) {
        console.log('Received status update:', data);
        $timeout(function() {
            $scope.students.forEach(function(student) {
                if (student.email === data.email) {
                    student.status = data.status;
                }
            });
            filterStudents(); // Update filtered list
        });
    });

    // Handle Enter key in message input
    $scope.handleKeyPress = function(event) {
        if (event.keyCode === 13 && !event.shiftKey) {
            event.preventDefault();
            $scope.sendMessage();
        }
    };

    // Wait for authentication to be fully established before loading students
    function initializeWithDelay() {
        if (!AuthService.isLoggedIn()) {
            $timeout(initializeWithDelay, 100);
            return;
        }
        
        // Additional delay to ensure all services are ready
        $timeout(function() {
            loadStudents();
        }, 500);
    }

    // Initialize with proper timing
    initializeWithDelay();
}]);