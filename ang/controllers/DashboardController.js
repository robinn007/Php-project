/**
 * @file DashboardController.js
 * @description Manages the dashboard view, including student counts and recent students.
 */
angular.module('myApp').controller('DashboardController', ['$scope', 'AuthService', '$location', '$sce', 'AjaxHelper', '$q', function($scope, AuthService, $location, $sce, AjaxHelper, $q) {
    $scope.title = 'Student Management Dashboard';
    $scope.totalStudents = 0;
    $scope.totalDeletedStudents = 0;
    $scope.recentStudents = [];
    $scope.flashMessage = 'Loading dashboard...';
    $scope.flashType = 'info';
    $scope.currentUser = AuthService.getCurrentUser();

    console.log('DashboardController initialized. User:', $scope.currentUser, 'Logged in:', AuthService.isLoggedIn());

    if (!AuthService.isLoggedIn()) {
        console.log('User not logged in, redirecting to /login');
        $scope.flashMessage = 'Please log in to view the dashboard.';
        $scope.flashType = 'error';
        $location.path('/login');
        return;
    }

    // Load all dashboard data
    function loadDashboardData() {
        $scope.flashMessage = 'Loading dashboard data...';
        $scope.flashType = 'info';

        // Create promises for both requests
        var studentsPromise = AjaxHelper.ajaxRequest('GET', '/students/manage');
        var deletedStudentsPromise = AjaxHelper.ajaxRequest('GET', '/students/deleted');

        // Wait for both requests to complete
        $q.all([studentsPromise, deletedStudentsPromise])
            .then(function(responses) {
                var studentsResponse = responses[0];
                var deletedStudentsResponse = responses[1];

                console.log('Active students response:', JSON.stringify(studentsResponse.data, null, 2));
                console.log('Deleted students response:', JSON.stringify(deletedStudentsResponse.data, null, 2));

                // Process active students
                if (studentsResponse.data.success) {
                    var allStudents = studentsResponse.data.students || [];
                    $scope.totalStudents = allStudents.length;
                    
                    // Sort by created_at if available, otherwise by ID (newest first)
                    $scope.recentStudents = allStudents
                        .sort(function(a, b) {
                            // Try to sort by created_at first
                            if (a.created_at && b.created_at) {
                                return new Date(b.created_at) - new Date(a.created_at);
                            }
                            // Fallback to ID-based sorting (higher ID = more recent)
                            return parseInt(b.id) - parseInt(a.id);
                        })
                        .slice(0, 5); // Get top 5
                }

                // Process deleted students
                if (deletedStudentsResponse.data.success) {
                    $scope.totalDeletedStudents = deletedStudentsResponse.data.students ? deletedStudentsResponse.data.students.length : 0;
                }

                // Set success message
                $scope.flashMessage = 'Dashboard loaded successfully';
                $scope.flashType = 'success';

                console.log('Dashboard data loaded - Active:', $scope.totalStudents, 'Deleted:', $scope.totalDeletedStudents, 'Recent:', $scope.recentStudents.length);
            })
            .catch(function(error) {
                console.error('Error loading dashboard data:', JSON.stringify(error, null, 2));
                $scope.flashMessage = error.flashMessage || 'Failed to load dashboard data';
                $scope.flashType = 'error';
            });
    }

    // Load dashboard data on initialization
    loadDashboardData();

    // Navigation functions
    $scope.goToAddStudent = function() {
        console.log('Navigating to /students/add');
        $location.path('/students/add');
    };

    $scope.goToEditStudent = function(id) {
        console.log('Navigating to /students/edit/' + id);
        $location.path('/students/edit/' + id);
    };

    $scope.goToStudents = function() {
        console.log('Navigating to /students');
        $location.path('/students');
    };

    $scope.goToDeletedStudents = function() {
        console.log('Navigating to /students/deleted');
        $location.path('/students/deleted');
    };
}]);