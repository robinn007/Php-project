angular.module('myApp').controller('StudentController', ['$scope', 'AjaxHelper', '$sce', '$filter', '$location', 'SocketService', 'AuthService', '$timeout', function($scope, AjaxHelper, $sce, $filter, $location, SocketService, AuthService, $timeout) {
    $scope.title = "Students Dashboard";
    $scope.students = [];
    $scope.flashMessage = 'Loading students...';
    $scope.flashType = 'info';
    $scope.searchText = '';
    $scope.selectedStates = [];
    $scope.selectedStudentEmail = ''; // For chat recipient
    $scope.messages = []; // Chat messages
    $scope.newMessage = ''; // New message input
    $scope.senderEmail = AuthService.getCurrentUserEmail(); // Current user's email
    var lastRequestPromise = null;

    $scope.states = [
        'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh',
        'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana',
        'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
        'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry',
        'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand',
        'West Bengal'
    ].sort();

    console.log('StudentController initialized for user:', $scope.senderEmail);

    // Redirect to login if not authenticated
    if (!AuthService.isLoggedIn()) {
        $location.path('/login').search({ logout: 'true' });
        return;
    }

    function initFilters() {
        var searchParams = $location.search();
        $scope.searchText = searchParams.search || '';
        if (searchParams.states) {
            try {
                $scope.selectedStates = JSON.parse(searchParams.states);
                if (!Array.isArray($scope.selectedStates)) {
                    $scope.selectedStates = [$scope.selectedStates];
                }
            } catch (e) {
                console.error('Error parsing states from URL:', e);
                $scope.selectedStates = angular.copy($scope.states);
            }
        } else {
            $scope.selectedStates = angular.copy($scope.states);
        }
        console.log('Initialized filters:', { search: $scope.searchText, states: $scope.selectedStates.length + ' states' });
    }

    $scope.loadStudents = function(searchText, selectedStates) {
        if (lastRequestPromise) {
            console.log('Cancelling previous loadStudents request');
        }

        $scope.flashMessage = 'Loading students...';
        $scope.flashType = 'info';
        var params = {};
        if (searchText) {
            params.search = searchText;
        }
        if (selectedStates && selectedStates.length > 0 && selectedStates.length < $scope.states.length) {
            params.states = JSON.stringify(selectedStates);
        }
        console.log('Loading students with params:', params);

        lastRequestPromise = AjaxHelper.ajaxRequest('GET', '/students/manage', params)
            .then(function(response) {
                console.log('getStudents response:', response);
                $scope.flashMessage = response.flashMessage;
                $scope.flashType = response.flashType;
                if (response.data.success) {
                    $scope.students = response.data.students || [];
                    $scope.students.forEach(function(student) {
                        student.statusDisplay = student.status === 'online' ? 'Online' : 'Offline';
                        console.log('Student status for ' + student.email + ': ' + student.statusDisplay);
                    });
                    // No $scope.$apply() needed; $http triggers digest
                } else {
                    console.error('Failed to load students:', response.data.message);
                }
                lastRequestPromise = null;
            })
            .catch(function(error) {
                if (error.message === 'cancelled') {
                    console.log('Request cancelled');
                    return;
                }
                console.error('Error loading students:', error);
                $scope.flashMessage = error.flashMessage || 'Failed to load students';
                $scope.flashType = error.flashType || 'error';
                // No $scope.$apply() needed; $http triggers digest
                lastRequestPromise = null;
            });
    };

    // Listen for status updates from socket
    $scope.$on('status_update', function(event, data) {
        console.log('StudentController: Received status_update for', data.email, 'status:', data.status);
        $timeout(function() { // Use $timeout to avoid $digest in progress
            $scope.students.forEach(function(student) {
                if (student.email === data.email) {
                    student.status = data.status;
                    student.statusDisplay = data.status === 'online' ? 'Online' : 'Offline';
                }
            });
        });
    });

    // Listen for incoming chat messages
    $scope.$on('chat_message', function(event, data) {
        console.log('StudentController: Received chat_message:', data);
        $timeout(function() { // Use $timeout to avoid $digest in progress
            if (data.sender_email === $scope.selectedStudentEmail || data.receiver_email === $scope.senderEmail) {
                $scope.messages.push({
                    sender_email: data.sender_email,
                    message: data.message,
                    created_at: data.created_at
                });
            }
        });
    });

    // Select a student to chat with
    $scope.selectStudentForChat = function(email) {
        $scope.selectedStudentEmail = email;
        console.log('StudentController: Selected student for chat:', email);
        $scope.messages = []; // Clear previous messages
        loadMessages(); // Load messages for the selected student
    };

    // Send a chat message
    $scope.sendMessage = function() {
        if (!$scope.newMessage || !$scope.selectedStudentEmail) {
            console.log('StudentController: Missing message or selected student email');
            $scope.flashMessage = 'Please select a student and enter a message.';
            $scope.flashType = 'error';
            return;
        }

        var messageData = {
            sender_email: $scope.senderEmail,
            receiver_email: $scope.selectedStudentEmail,
            message: $scope.newMessage
        };

        console.log('StudentController: Sending message:', messageData);
        SocketService.emit('chat_message', messageData, function(response) {
            console.log('StudentController: Message sent response:', response);
            $timeout(function() { // Use $timeout to safely update scope
                $scope.newMessage = ''; // Clear input
            });
        });
    };

    // Load messages for the selected student
    function loadMessages() {
        if (!$scope.selectedStudentEmail) {
            console.log('StudentController: No student selected for loading messages');
            return;
        }

        AjaxHelper.ajaxRequest('GET', '/auth/get_messages', { receiver_email: $scope.selectedStudentEmail })
            .then(function(response) {
                if (response.data.success) {
                    $scope.messages = response.data.messages || [];
                    console.log('StudentController: Loaded messages:', $scope.messages);
                    // No $scope.$apply() needed; $http triggers digest
                } else {
                    console.error('StudentController: Failed to load messages:', response.data.message);
                    $scope.flashMessage = response.data.message || 'Failed to load messages';
                    $scope.flashType = 'error';
                }
            })
            .catch(function(error) {
                console.error('StudentController: Error loading messages:', error);
                $scope.flashMessage = 'Error loading messages';
                $scope.flashType = 'error';
                // No $scope.$apply() needed; $http triggers digest
            });
    }

    function updateUrlParams() {
        var params = {};
        if ($scope.searchText) {
            params.search = $scope.searchText;
        }
        if ($scope.selectedStates && $scope.selectedStates.length > 0 && $scope.selectedStates.length < $scope.states.length) {
            params.states = JSON.stringify($scope.selectedStates);
        }
        console.log('Updating URL with params:', params);
        $location.search(params);
    }

    $scope.handleSearch = function(searchText) {
        $scope.searchText = searchText || '';
        console.log('Search triggered with:', $scope.searchText);
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    $scope.handleStateChange = function(states) {
        $scope.selectedStates = states || [];
        console.log('State filter changed to:', $scope.selectedStates.length + ' states');
        updateUrlParams();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    };

    $scope.$on('$locationChangeSuccess', function() {
        initFilters();
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });

    initFilters();
    $scope.loadStudents($scope.searchText, $scope.selectedStates);

    $scope.deleteStudent = function(id) {
        if (confirm('Are you sure you want to delete this student?')) {
            AjaxHelper.ajaxRequest('POST', '/students/manage', { action: 'delete', id: id })
                .then(function(response) {
                    if (response.data.success) {
                        $scope.students = $scope.students.filter(function(student) {
                            return student.id !== id;
                        });
                        $scope.flashMessage = response.flashMessage;
                        $scope.flashType = response.flashType;
                        // Clear chat if the deleted student was selected
                        if ($scope.selectedStudentEmail === $scope.students.find(s => s.id === id)?.email) {
                            $scope.selectedStudentEmail = '';
                            $scope.messages = [];
                        }
                    } else {
                        $scope.flashMessage = response.flashMessage;
                        $scope.flashType = response.flashType;
                    }
                    // No $scope.$apply() needed; $http triggers digest
                })
                .catch(function(error) {
                    $scope.flashMessage = error.flashMessage || 'Failed to delete student';
                    $scope.flashType = error.flashType || 'error';
                    // No $scope.$apply() needed; $http triggers digest
                });
        }
    };

    $scope.$on('studentUpdated', function() {
        $scope.loadStudents($scope.searchText, $scope.selectedStates);
    });
}]);