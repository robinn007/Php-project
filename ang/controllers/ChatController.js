    /**
     * @file ChatController.js
     * @description Controller for managing the three-panel chat interface with group chat support.
     */
    angular.module("myApp").controller("ChatController", [
    "$scope",
    "$rootScope",
    "AjaxHelper",
    "SocketService",
    "AuthService",
    "$location",
    "$timeout",
    function (
        $scope,
        $rootScope,
        AjaxHelper,
        SocketService,
        AuthService,
        $location,
        $timeout
    ) {
        console.log("ChatController initialized with group chat support");

        // Initialize scope variables
        $scope.allStudents = [];
        $scope.conversationStudents = [];
        $scope.groups = [];
        $scope.selectedStudent = null;
        $scope.selectedGroup = null;
        $scope.selectedStudentEmail = "";
        $scope.messages = [];
        $scope.newMessage = "";
        $scope.senderEmail = AuthService.getCurrentUserEmail();
        $scope.currentUser = AuthService.getCurrentUser();
        $scope.isLoading = true;
        $scope.studentsLoaded = false;
        $scope.searchQuery = "";
        $scope.studentSearchQuery = "";
        $scope.filteredConversations = [];
        $scope.filteredAllStudents = [];
        $scope.conversationData = {};
        $scope.groupConversationData = {};
        $scope.chatType = "direct";
        $scope.showCreateGroupModal = false;
        $scope.newGroupName = "";
        $scope.newGroupDescription = "";
        $scope.selectedMembers = [];
        $scope.flashMessage = "";
        $scope.flashType = "";

        // Initialize allStudents with selected property
        function initializeStudents() {
        $scope.allStudents = $scope.allStudents.map(function (student) {
            student.selected = $scope.selectedMembers.includes(student.email);
            return student;
        });
        console.log(
            "initializeStudents: allStudents initialized with selected property",
            $scope.allStudents
        );
        }

        // Check if user is logged in with error handling
        try {
        if (!AuthService.isLoggedIn()) {
            console.log("User not logged in, redirecting to login");
            $location.path("/login").search({ logout: "true" });
            return;
        }
        } catch (error) {
        console.error("Error checking auth status:", error);
        $scope.flashMessage = "Authentication error occurred";
        $scope.flashType = "error";
        $rootScope.$emit("flashMessage", {
            message: $scope.flashMessage,
            type: $scope.flashType,
        });
        $location.path("/login").search({ logout: "true" });
        return;
        }

        // Update conversation data when a new message is received
        function updateConversationData(studentEmail, message, timestamp) {
        if (!$scope.conversationData[studentEmail]) {
            $scope.conversationData[studentEmail] = {};
        }

        $scope.conversationData[studentEmail].lastMessage = message;
        $scope.conversationData[studentEmail].lastMessageTime = new Date(
            timestamp
        );
        $scope.conversationData[studentEmail].timestamp = new Date(
            timestamp
        ).getTime();

        console.log(
            "Updated conversation data for",
            studentEmail,
            ":",
            $scope.conversationData[studentEmail]
        );
        }

        // Update group conversation data
        function updateGroupConversationData(
        groupId,
        message,
        timestamp,
        senderName
        ) {
        if (!$scope.groupConversationData[groupId]) {
            $scope.groupConversationData[groupId] = {};
        }

        $scope.groupConversationData[groupId].lastMessage = message;
        $scope.groupConversationData[groupId].lastMessageTime = new Date(
            timestamp
        );
        $scope.groupConversationData[groupId].timestamp = new Date(
            timestamp
        ).getTime();
        $scope.groupConversationData[groupId].senderName = senderName;

        console.log(
            "Updated group conversation data for",
            groupId,
            ":",
            $scope.groupConversationData[groupId]
        );
        }

        // Get last message preview for a student
        $scope.getLastMessagePreview = function (item) {
        if (!item) return "No messages yet";

        if (item.group_id) {
            var data = $scope.groupConversationData[item.group_id];
            if (data && data.lastMessage) {
            var preview =
                data.lastMessage.length > 45
                ? data.lastMessage.substring(0, 45) + "..."
                : data.lastMessage;
            var sender = data.senderName ? data.senderName + ": " : "";
            return sender + preview;
            }
            return item.description || "No messages yet";
        }

        if (!item.email) return "No messages yet";

        var data = $scope.conversationData[item.email];
        if (data && data.lastMessage) {
            var preview =
            data.lastMessage.length > 45
                ? data.lastMessage.substring(0, 45) + "..."
                : data.lastMessage;
            return preview;
        }
        return "No messages yet";
        };

        // Get last message time
        $scope.getLastMessageTime = function (item) {
        if (!item) return "";

        var data = item.group_id
            ? $scope.groupConversationData[item.group_id]
            : $scope.conversationData[item.email];

        if (data && data.lastMessageTime) {
            var now = new Date();
            var messageTime = new Date(data.lastMessageTime);
            var diffMs = now - messageTime;
            var diffMins = Math.floor(diffMs / 60000);
            var diffHours = Math.floor(diffMs / 3600000);
            var diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return "Just now";
            if (diffMins < 60) return diffMins + "m ago";
            if (diffHours < 24) return diffHours + "h ago";
            if (diffDays < 7) return diffDays + "d ago";
            return messageTime.toLocaleDateString();
        }
        return "";
        };

        // Sort conversation items by last message time
        function sortConversationsByLastMessage() {
        $scope.conversationStudents.sort(function (a, b) {
            var aData, bData, aTime, bTime;

            if (a.group_id) {
            aData = $scope.groupConversationData[a.group_id];
            aTime = aData && aData.timestamp ? aData.timestamp : 0;
            } else {
            aData = $scope.conversationData[a.email];
            aTime = aData && aData.timestamp ? aData.timestamp : 0;
            }

            if (b.group_id) {
            bData = $scope.groupConversationData[b.group_id];
            bTime = bData && bData.timestamp ? bData.timestamp : 0;
            } else {
            bData = $scope.conversationData[b.email];
            bTime = bData && bData.timestamp ? bData.timestamp : 0;
            }

            return bTime - aTime;
        });

        console.log("Conversations sorted by last message time");
        filterConversations();
        }

        // Filter conversations based on search query
        function filterConversations() {
        if (!$scope.searchQuery) {
            $scope.filteredConversations = $scope.conversationStudents.filter(
            function (item) {
                return item && ((item.email && item.name) || item.group_id);
            }
            );
        } else {
            $scope.filteredConversations = $scope.conversationStudents.filter(
            function (item) {
                if (!item) return false;

                if (item.group_id) {
                return (
                    item.name
                    .toLowerCase()
                    .includes($scope.searchQuery.toLowerCase()) ||
                    (item.description &&
                    item.description
                        .toLowerCase()
                        .includes($scope.searchQuery.toLowerCase()))
                );
                }

                if (!item.email || !item.name) return false;
                return (
                item.name
                    .toLowerCase()
                    .includes($scope.searchQuery.toLowerCase()) ||
                item.email
                    .toLowerCase()
                    .includes($scope.searchQuery.toLowerCase())
                );
            }
            );
        }
        }

        // Filter all students for right sidebar
        function filterAllStudents() {
        if (!$scope.studentSearchQuery) {
            $scope.filteredAllStudents = $scope.allStudents.filter(function (
            student
            ) {
            return student && student.email && student.name;
            });
        } else {
            $scope.filteredAllStudents = $scope.allStudents.filter(function (
            student
            ) {
            if (!student || !student.email || !student.name) {
                return false;
            }
            return (
                student.name
                .toLowerCase()
                .includes($scope.studentSearchQuery.toLowerCase()) ||
                student.email
                .toLowerCase()
                .includes($scope.studentSearchQuery.toLowerCase()) ||
                (student.location &&
                student.location
                    .toLowerCase()
                    .includes($scope.studentSearchQuery.toLowerCase()))
            );
            });
        }
        }

        $scope.filterConversations = function () {
        filterConversations();
        };

        $scope.filterAllStudents = function () {
        filterAllStudents();
        };

        function loadConversationSummary() {
        console.log("Loading conversation summary for message-based ordering");

        AjaxHelper.ajaxRequest("GET", "/auth/get_last_messages_summary")
            .then(function (response) {
            console.log("Conversation summary loaded:", response.data);

            var conversations = response.data.conversations || [];
            var conversationItems = [];

            conversations.forEach(function (conv) {
                if (conv.conversation_type === "group") {
                var groupItem = {
                    group_id: conv.group_id,
                    name: conv.group_name,
                    description: conv.group_name,
                    member_count: conv.member_count,
                    type: "group",
                };

                updateGroupConversationData(
                    conv.group_id,
                    conv.message,
                    conv.created_at,
                    conv.sender_name
                );

                conversationItems.push(groupItem);
                } else {
                var studentItem = $scope.allStudents.find(function (student) {
                    return student.email === conv.other_person_email;
                });

                if (studentItem) {
                    updateConversationData(
                    conv.other_person_email,
                    conv.message,
                    conv.created_at
                    );
                    conversationItems.push(studentItem);
                }
                }
            });

            $scope.conversationStudents = conversationItems;
            console.log(
                "Found",
                $scope.conversationStudents.length,
                "total conversations"
            );

            sortConversationsByLastMessage();
            $scope.$applyAsync();
            })
            .catch(function (error) {
            console.error("Error loading conversation summary:", error);
            $scope.conversationStudents = [];
            filterConversations();
            $scope.$applyAsync();
            });
        }

        $scope.selectStudent = function (student) {
        if (!student || !student.email || !student.name) {
            console.error(
            "Invalid student object passed to selectStudent:",
            student
            );
            return;
        }

        $scope.selectedStudent = student;
        $scope.selectedGroup = null;
        $scope.selectedStudentEmail = student.email;
        $scope.messages = [];
        $scope.chatType = "direct";
        console.log("Selected student:", student.name, student.email);
        loadMessages();
        };

        $scope.selectGroup = function (group) {
        if (!group || !group.group_id) {
            console.error("Invalid group object passed to selectGroup:", group);
            return;
        }

        $scope.selectedGroup = group;
        $scope.selectedStudent = null;
        $scope.selectedStudentEmail = "";
        $scope.messages = [];
        $scope.chatType = "group";
        console.log("Selected group:", group.name, group.group_id);
        loadGroupMessages();
        };

        $scope.isStudentSelected = function (student) {
        if (!student || !student.email || !$scope.selectedStudent) {
            return false;
        }
        return $scope.selectedStudent.email === student.email;
        };

        $scope.isGroupSelected = function (group) {
        if (!group || !group.group_id || !$scope.selectedGroup) {
            return false;
        }
        return $scope.selectedGroup.group_id === group.group_id;
        };

        $scope.getStatusDisplay = function (student) {
        if (!student || !student.hasOwnProperty("status")) {
            return "Offline";
        }
        return student.status === "online" ? "Online" : "Offline";
        };

        $scope.getStatusClass = function (student) {
        if (!student || !student.hasOwnProperty("status")) {
            return "status-offline";
        }
        return student.status === "online" ? "status-online" : "status-offline";
        };

        $scope.isMyMessage = function (message) {
        return message.sender_email === $scope.senderEmail;
        };

        $scope.getSenderName = function (message) {
        if (message.sender_email === $scope.senderEmail) {
            return "You";
        }

        if ($scope.chatType === "group" && message.sender_name) {
            return message.sender_name;
        }

        var student = $scope.allStudents.find(function (s) {
            return s.email === message.sender_email;
        });
        return student ? student.name : message.sender_email;
        };

        function loadStudents(retryCount = 0) {
        console.log("Fetching students for chat, attempt:", retryCount + 1);
        $scope.isLoading = true;

        AjaxHelper.ajaxRequest("GET", "/students")
            .then(function (response) {
            console.log("Students fetched:", response.data.students);
            $scope.allStudents = response.data.students.filter(function (
                student
            ) {
                return student.email !== $scope.senderEmail;
            });
            $scope.studentsLoaded = true;
            $scope.isLoading = false;

            filterAllStudents();

            if ($scope.allStudents.length > 0) {
                loadConversationSummary();
            } else {
                $scope.filteredConversations = [];
                $scope.conversationStudents = [];
            }

            $scope.$applyAsync();
            })
            .catch(function (error) {
            console.error(
                "Error fetching students (attempt " + (retryCount + 1) + "):",
                error
            );

            if (retryCount < 2 && error.status === 0) {
                var delay = Math.pow(2, retryCount) * 10000;
                console.log("Retrying in", delay, "ms");

                $timeout(function () {
                loadStudents(retryCount + 1);
                }, delay);
            } else {
                $scope.isLoading = false;
                $scope.flashMessage =
                error.flashMessage || "Failed to load students";
                $scope.flashType = error.flashType || "error";
                $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
                });
                $scope.$applyAsync();
            }
            });
        }

        function loadMessages() {
        if (!$scope.selectedStudentEmail) {
            $scope.messages = [];
            return;
        }

        console.log("Loading messages for:", $scope.selectedStudentEmail);
        AjaxHelper.ajaxRequest("GET", "/auth/get_messages", {
            receiver_email: $scope.selectedStudentEmail,
        })
            .then(function (response) {
            console.log("Messages fetched:", response.data.messages);
            $scope.messages = response.data.messages || [];
            $scope.$applyAsync();

            if ($scope.messages.length > 0) {
                var lastMessage = $scope.messages[$scope.messages.length - 1];
                updateConversationData(
                $scope.selectedStudentEmail,
                lastMessage.message,
                lastMessage.created_at
                );
            }

            scrollToBottom();
            })
            .catch(function (error) {
            console.error("Error fetching messages:", error);
            $scope.messages = [];
            $scope.flashMessage = error.flashMessage || "Failed to load messages";
            $scope.flashType = error.flashType || "error";
            $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
            });
            $scope.$applyAsync();
            });
        }

        function loadGroupMessages() {
        if (!$scope.selectedGroup || !$scope.selectedGroup.group_id) {
            $scope.messages = [];
            return;
        }

        console.log("Loading group messages for:", $scope.selectedGroup.group_id);
        AjaxHelper.ajaxRequest("GET", "/auth/get_messages", {
            group_id: $scope.selectedGroup.group_id,
        })
            .then(function (response) {
            console.log("Group messages fetched:", response.data.messages);
            $scope.messages = response.data.messages || [];
            $scope.$applyAsync();

            if ($scope.messages.length > 0) {
                var lastMessage = $scope.messages[$scope.messages.length - 1];
                updateGroupConversationData(
                $scope.selectedGroup.group_id,
                lastMessage.message,
                lastMessage.created_at,
                lastMessage.sender_name
                );
            }

            scrollToBottom();
            })
            .catch(function (error) {
            console.error("Error fetching group messages:", error);
            $scope.messages = [];
            $scope.flashMessage =
                error.flashMessage || "Failed to load group messages";
            $scope.flashType = error.flashType || "error";
            $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
            });
            $scope.$applyAsync();
            });
        }

        function scrollToBottom() {
        $timeout(function () {
            var messagesContainer = document.querySelector(".messages-container");
            if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }, 100);
        }

        $scope.sendMessage = function () {
        if (
            !$scope.newMessage ||
            (!$scope.selectedStudentEmail && !$scope.selectedGroup)
        ) {
            $scope.flashMessage =
            "Please enter a message and select a conversation";
            $scope.flashType = "error";
            $rootScope.$emit("flashMessage", {
            message: $scope.flashMessage,
            type: $scope.flashType,
            });
            return;
        }

        if ($scope.chatType === "group") {
            console.log("Sending group message to:", $scope.selectedGroup.group_id);

            updateGroupConversationData(
            $scope.selectedGroup.group_id,
            $scope.newMessage,
            new Date(),
            "You"
            );
            sortConversationsByLastMessage();

            SocketService.emit("group_message", {
            sender_email: $scope.senderEmail,
            group_id: $scope.selectedGroup.group_id,
            message: $scope.newMessage,
            });
        } else {
            console.log("Sending message to:", $scope.selectedStudentEmail);

            updateConversationData(
            $scope.selectedStudentEmail,
            $scope.newMessage,
            new Date()
            );

            var studentInConversations = $scope.conversationStudents.find(function (
            s
            ) {
            return s.email === $scope.selectedStudentEmail;
            });

            if (!studentInConversations && $scope.selectedStudent) {
            $scope.conversationStudents.unshift($scope.selectedStudent);
            console.log(
                "Added new student to conversations:",
                $scope.selectedStudent.name
            );
            }

            sortConversationsByLastMessage();

            SocketService.emit("chat_message", {
            sender_email: $scope.senderEmail,
            receiver_email: $scope.selectedStudentEmail,
            message: $scope.newMessage,
            });
        }

        $scope.newMessage = "";
        var textarea = document.querySelector("#newMessage");
        if (textarea) {
            textarea.style.height = "auto";
        }
        };

        $scope.autoResize = function () {
        $timeout(function () {
            var textarea = document.querySelector("#newMessage");
            if (textarea) {
            textarea.style.height = "auto";
            textarea.style.height = textarea.scrollHeight + "px";
            }
        });
        };

        $scope.debugOpenModal = function () {
        console.log("debugOpenModal called from console or button");
        $scope.openCreateGroupModal();
        };

        $scope.openCreateGroupModal = function() {
        console.log('openCreateGroupModal called');
        
        $scope.showCreateGroupModal = true;
        $scope.newGroupName = '';
        $scope.newGroupDescription = '';
        $scope.selectedMembers = [];
        $scope.flashMessage = '';
        $scope.flashType = '';
        
        // Initialize students with selected property
        $scope.allStudents = $scope.allStudents.map(function(student) {
            student.selected = false;
            return student;
        });
        
        // Force Angular to update the view
        $scope.$apply();
        console.log('Modal should now be visible, showCreateGroupModal =', $scope.showCreateGroupModal);
    };

        $scope.closeCreateGroupModal = function() {
        console.log('closeCreateGroupModal called');
        
        $scope.showCreateGroupModal = false;
        $scope.newGroupName = '';
        $scope.newGroupDescription = '';
        $scope.selectedMembers = [];
        $scope.flashMessage = '';
        $scope.flashType = '';
        
        // Reset all student selections
        if ($scope.allStudents) {
            $scope.allStudents.forEach(function(student) {
                student.selected = false;
            });
        }
        
        // Force Angular to update the view
        if (!$scope.$$phase) {
            $scope.$apply();
        }
        console.log('Modal should now be hidden, showCreateGroupModal =', $scope.showCreateGroupModal);
    };

        // Also update the toggleMember function to be simpler
    $scope.toggleMember = function(email) {
        console.log('toggleMember called for email:', email);
        
        var index = $scope.selectedMembers.indexOf(email);
        if (index === -1) {
            $scope.selectedMembers.push(email);
        } else {
            $scope.selectedMembers.splice(index, 1);
        }
        
        // Update the selected property for visual feedback
        $scope.allStudents.forEach(function(student) {
            if (student.email === email) {
                student.selected = !student.selected;
            }
        });
        
        console.log('Current selectedMembers:', $scope.selectedMembers);
    };

        $scope.toggleMemberSelection = function (student) {
        console.log("toggleMemberSelection called for student:", student.email);
        var index = $scope.selectedMembers.indexOf(student.email);
        if (index > -1) {
            $scope.selectedMembers.splice(index, 1);
        } else {
            $scope.selectedMembers.push(student.email);
        }
        $scope.$applyAsync();
        };

        $scope.isMemberSelected = function (student) {
        return $scope.selectedMembers.indexOf(student.email) > -1;
        };

        $scope.createGroup = function () {
        console.log("createGroup called at:", new Date().toISOString());
        console.log("createGroup: Form values before validation:", {
            newGroupName: $scope.newGroupName,
            newGroupDescription: $scope.newGroupDescription,
            selectedMembers: $scope.selectedMembers,
        });

        // Clear previous flash messages
        $scope.flashMessage = "";
        $scope.flashType = "";

        // Client-side validation
        var trimmedName = ($scope.newGroupName || "").trim();
        var trimmedDescription = ($scope.newGroupDescription || "").trim();

        if (!trimmedName) {
            console.log("createGroup: Group name is empty");
            $scope.flashMessage = "Group name is required";
            $scope.flashType = "error";
            return;
        }

        if (trimmedName.length < 3) {
            console.log("createGroup: Group name too short");
            $scope.flashMessage = "Group name must be at least 3 characters long";
            $scope.flashType = "error";
            return;
        }

        if (!$scope.selectedMembers || $scope.selectedMembers.length === 0) {
            console.log("createGroup: No members selected");
            $scope.flashMessage = "At least one member must be selected";
            $scope.flashType = "error";
            return;
        }

        // Prepare data object
        var groupData = {
            name: trimmedName,
            description: trimmedDescription,
            members: $scope.selectedMembers,
        };

        console.log(
            "createGroup: Sending validated group data:",
            JSON.stringify(groupData, null, 2)
        );

        // Set loading state
        $scope.isLoading = true;

        // Make AJAX request
        AjaxHelper.ajaxRequest("POST", "/auth/create_group", groupData)
            .then(function (response) {
            console.log("createGroup: Success response received:", response);

            if (response.data && response.data.success === true) {
                console.log(
                "createGroup: Group created successfully with ID:",
                response.data.group_id
                );

                // Add group to local groups array
                if (!$scope.groups) {
                $scope.groups = [];
                }
                $scope.groups.push({
                group_id: response.data.group_id,
                name: trimmedName,
                description: trimmedDescription,
                members: $scope.selectedMembers,
                member_count: $scope.selectedMembers.length + 1, // Include creator
                });

                // Show success message
                $scope.flashMessage =
                response.data.flashMessage || "Group created successfully";
                $scope.flashType = response.data.flashType || "success";
                $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
                });

                // Close modal after success
                $timeout(function () {
                $scope.closeCreateGroupModal();

                // Reset students selection state
                initializeStudents();

                // Reload conversation summary to include new group
                loadConversationSummary();
                }, 1500); // Give time for user to see success message
            } else {
                console.error(
                "createGroup: Server returned failure:",
                response.data
                );
                $scope.flashMessage =
                response.data.message ||
                response.data.flashMessage ||
                "Failed to create group";
                $scope.flashType = response.data.flashType || "error";
                $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
                });
            }

            $scope.isLoading = false;
            $scope.$applyAsync();
            })
            .catch(function (error) {
            console.error("createGroup: Error occurred:", error);

            $scope.isLoading = false;
            $scope.flashMessage =
                error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : "Failed to create group. Please ensure all member emails are valid and try again.";
            $scope.flashType = "error";
            $rootScope.$emit("flashMessage", {
                message: $scope.flashMessage,
                type: $scope.flashType,
            });
            $scope.$applyAsync();
            });
        };

        SocketService.on("chat_message", function (data) {
        console.log("Received chat message:", data);

        var otherPersonEmail =
            data.sender_email === $scope.senderEmail
            ? data.receiver_email
            : data.sender_email;

        updateConversationData(otherPersonEmail, data.message, data.created_at);

        if (data.receiver_email === $scope.senderEmail) {
            var studentInConversations = $scope.conversationStudents.find(function (
            s
            ) {
            return s.email === otherPersonEmail;
            });

            if (!studentInConversations) {
            var studentFromAll = $scope.allStudents.find(function (s) {
                return s.email === otherPersonEmail;
            });
            if (studentFromAll) {
                $scope.conversationStudents.unshift(studentFromAll);
                console.log(
                "Added new student to conversations from incoming message:",
                studentFromAll.name
                );
            }
            }
        }

        if (
            data.sender_email === $scope.selectedStudentEmail ||
            data.receiver_email === $scope.selectedStudentEmail
        ) {
            $scope.messages.push(data);
            scrollToBottom();
        }

        sortConversationsByLastMessage();
        $scope.$applyAsync();
        });

        SocketService.on("group_message", function (data) {
        console.log("Received group message:", data);

        updateGroupConversationData(
            data.group_id,
            data.message,
            data.created_at,
            data.sender_name
        );

        if (
            $scope.selectedGroup &&
            data.group_id == $scope.selectedGroup.group_id
        ) {
            $scope.messages.push(data);
            scrollToBottom();
        }

        sortConversationsByLastMessage();
        $scope.$applyAsync();
        });

        SocketService.on("group_created", function (data) {
        console.log("Received group_created event:", data);
        if (
            $scope.senderEmail === data.created_by ||
            $scope.selectedMembers.includes($scope.senderEmail)
        ) {
            var newGroup = {
            group_id: data.group_id,
            name: data.name,
            description: data.description,
            member_count: data.member_count,
            type: "group",
            };
            $scope.conversationStudents.unshift(newGroup);
            sortConversationsByLastMessage();
            $scope.$applyAsync();
        }
        });

        SocketService.on("status_update", function (data) {
        console.log("Received status update:", data);
        $timeout(function () {
            $scope.allStudents.forEach(function (student) {
            if (student.email === data.email) {
                student.status = data.status;
            }
            });

            $scope.conversationStudents.forEach(function (student) {
            if (student.email === data.email) {
                student.status = data.status;
            }
            });

            filterConversations();
            filterAllStudents();
        });
        });

        $scope.handleKeyPress = function (event) {
        if (event.keyCode === 13 && !event.shiftKey) {
            event.preventDefault();
            $scope.sendMessage();
        }
        };

        $scope.isGroupChat = function () {
        return $scope.chatType === "group" && $scope.selectedGroup;
        };

        $scope.getCurrentChatName = function () {
        if ($scope.chatType === "group" && $scope.selectedGroup) {
            return $scope.selectedGroup.name;
        } else if ($scope.selectedStudent) {
            return $scope.selectedStudent.name;
        }
        return "";
        };

        $scope.getCurrentChatStatus = function () {
        if ($scope.chatType === "group" && $scope.selectedGroup) {
            return $scope.selectedGroup.member_count + " members";
        } else if ($scope.selectedStudent) {
            return $scope.getStatusDisplay($scope.selectedStudent);
        }
        return "";
        };

        $scope.getCurrentChatStatusClass = function () {
        if ($scope.chatType === "group") {
            return "group-status";
        } else if ($scope.selectedStudent) {
            return $scope.getStatusClass($scope.selectedStudent);
        }
        return "";
        };

        $scope.isGroup = function (item) {
        return item && item.group_id;
        };

        function initializeWithDelay() {
        console.log("Attempting to initialize ChatController");
        if (!AuthService.isLoggedIn()) {
            console.log("AuthService not ready, retrying...");
            $timeout(initializeWithDelay, 100);
            return;
        }

        $timeout(function () {
            console.log("Initializing with students load");
            loadStudents();
        }, 500);
        }

        window.debugOpenModal = function () {
        $scope.debugOpenModal();
        };

        // Add event listener for ESC key to close modal
        document.addEventListener("keydown", function (event) {
        if (event.keyCode === 27 && $scope.showCreateGroupModal) {
            // ESC key
            $scope.closeCreateGroupModal();
        }
        });

        // Prevent modal from closing when clicking inside the modal content
        $scope.preventModalClose = function (event) {
        event.stopPropagation();
        };

        initializeWithDelay();
    },
    ]);
