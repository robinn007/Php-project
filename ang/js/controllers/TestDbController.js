/**
 * @file TestDbController.js
 * @description Displays the database connection status.
 */
angular.module('myApp').controller('TestDbController', ['$scope', '$http', function($scope, $http) {
  $scope.title = "Database Status";
  $scope.message = '';
  $scope.flashMessage = 'Loading database status...';
  $scope.flashType = 'info';

  // Fetch database status
  $http.get('/ci/students/test_db').then(function(response) {
    $scope.message = response.data.message;
    $scope.flashMessage = 'Database status loaded successfully.';
    $scope.flashType = 'success';
  }, function(error) {
    $scope.message = 'Error: ' + (error.statusText || 'Unknown error');
    $scope.flashMessage = 'Failed to load database status.';
    $scope.flashType = 'error';
  });
}]);