/**
 * @file DashboardController.js
 * @description Manages the dashboard view, including student counts and recent students.
 */
angular.module('myApp').controller('DashboardController', ['$scope', 'AuthService', '$location', '$sce', 'AjaxHelper', function($scope, AuthService, $location, $sce, AjaxHelper) {
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
    $location.path('/login');
    return;
  }

  // Fetch active students
  AjaxHelper.ajaxRequest('GET', '/ci/students/manage')
    .then(function(response) {
      console.log('getStudents response:', JSON.stringify(response.data, null, 2));
      $scope.flashMessage = response.flashMessage;
      $scope.flashType = response.flashType;
      if (response.data.success) {
        $scope.totalStudents = response.data.students ? response.data.students.length : 0;
        $scope.recentStudents = response.data.students
          ? response.data.students
              .sort(function(a, b) {
                return new Date(b.created_at) - new Date(a.created_at);
              })
              .slice(0, 5)
          : [];
      }
    })
    .catch(function(error) {
      console.error('Error loading students:', JSON.stringify(error, null, 2));
      $scope.flashMessage = error.flashMessage;
      $scope.flashType = error.flashType;
    });

  // Fetch deleted students
  AjaxHelper.ajaxRequest('GET', '/ci/students/deleted')
    .then(function(response) {
      console.log('getDeletedStudents response:', JSON.stringify(response.data, null, 2));
      $scope.flashMessage = response.flashMessage;
      $scope.flashType = response.flashType;
      if (response.data.success) {
        $scope.totalDeletedStudents = response.data.students ? response.data.students.length : 0;
      }
    })
    .catch(function(error) {
      console.error('Error loading deleted students:', JSON.stringify(error, null, 2));
      $scope.flashMessage = error.flashMessage;
      $scope.flashType = error.flashType;
    });

  /**
   * @function goToAddStudent
   * @description Navigates to the add student page.
   */
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