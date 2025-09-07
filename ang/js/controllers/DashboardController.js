/**
 * @file DashboardController.js
 * @description Manages the dashboard view, including student counts and recent students.
 */
angular.module('myApp').controller('DashboardController', ['$scope', 'StudentService', 'AuthService', '$location', '$sce', function($scope, StudentService, AuthService, $location, $sce) {
  $scope.title = 'Student Management Dashboard';
  $scope.totalStudents = 0;
  $scope.totalDeletedStudents = 0;
  $scope.recentStudents = [];
  $scope.flashMessage = 'Loading dashboard...';
  $scope.flashType = 'info';
  $scope.currentUser = AuthService.getCurrentUser();

  console.log('DashboardController initialized. User:', $scope.currentUser, 'Logged in:', AuthService.isLoggedIn());

    // Redirect to login if user is not authenticated
  if (!AuthService.isLoggedIn()) {
    console.log('User not logged in, redirecting to /login');
    $scope.flashMessage = 'Please log in to view the dashboard.';
    $scope.flashType = 'error';
    $location.path('/login'); // Updated to clean URL
    return;
  }

    // Fetch active students
  StudentService.getStudents().then(function(response) {
    console.log('getStudents response:', JSON.stringify(response.data, null, 2));
    if (response.data.success) {
      $scope.totalStudents = response.data.students ? response.data.students.length : 0;
      $scope.recentStudents = response.data.students
        ? response.data.students
            .sort(function(a, b) {
              return new Date(b.created_at) - new Date(a.created_at);
            })
            .slice(0, 5)
        : [];
      $scope.flashMessage = 'Dashboard loaded successfully.';
      $scope.flashType = 'success';
    } else {
      $scope.flashMessage = response.data.message || 'Failed to load students.';
      $scope.flashType = 'error';
      console.error('Failed to load students:', response.data.message);
    }
  }, function(error) {
    console.error('Error loading students:', JSON.stringify(error, null, 2));
    $scope.flashMessage = 'Error loading students: ' + (error.statusText || 'Network or server error');
    $scope.flashType = 'error';
  });

    // Fetch deleted students
  StudentService.getDeletedStudents().then(function(response) {
    console.log('getDeletedStudents response:', JSON.stringify(response.data, null, 2));
    if (response.data.success) {
      $scope.totalDeletedStudents = response.data.students ? response.data.students.length : 0;
    } else {
      $scope.flashMessage = response.data.message || 'Failed to load deleted students.';
      $scope.flashType = 'error';
      console.error('Failed to load deleted students:', response.data.message);
    }
  }, function(error) {
    console.error('Error loading deleted students:', JSON.stringify(error, null, 2));
    $scope.flashMessage = 'Error loading deleted students: ' + (error.statusText || 'Network or server error');
    $scope.flashType = 'error';
  });

   /**
    * @function goToAddStudent
    * @description Navigates to the add student page.
    */

  $scope.goToAddStudent = function() {
    console.log('Navigating to /students/add');
    $location.path('/students/add'); // Updated to clean URL
  };

  $scope.goToEditStudent = function(id) {
    console.log('Navigating to /students/edit/' + id);
    $location.path('/students/edit/' + id); // Already clean URL
  };

  $scope.goToStudents = function() {
    console.log('Navigating to /students');
    $location.path('/students'); // Updated to clean URL
  };

  $scope.goToDeletedStudents = function() {
    console.log('Navigating to /students/deleted');
    $location.path('/students/deleted'); // Updated to clean URL
  };
}]);