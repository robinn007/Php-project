/**
 * @file NavController.js
 * @description Controller for managing navigation and user authentication state and logout.
 */
angular.module('myApp').controller('NavController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
  // Initialize scope variables
  $scope.isLoggedIn = AuthService.isLoggedIn();
  $scope.currentUser = AuthService.getCurrentUser();
  $scope.currentPath = $location.path();

  console.log('NavController initialized. isLoggedIn:', $scope.isLoggedIn, 'currentUser:', $scope.currentUser, 'currentPath:', $scope.currentPath);

  /**
   * @event $routeChangeSuccess
   * @description Updates navigation state when the route changes.
   */
  $scope.$on('$routeChangeSuccess', function() {
    $scope.currentPath = $location.path();
    $scope.isLoggedIn = AuthService.isLoggedIn();
    $scope.currentUser = AuthService.getCurrentUser();
    console.log('Route changed. currentPath:', $scope.currentPath, 'isLoggedIn:', $scope.isLoggedIn);
  });

  /**
   * @function logout
   * @description Logs out the user, clears cookies, and redirects to login page.
   */
  $scope.logout = function() {
    console.log('Logging out user:', $scope.currentUser);
    AuthService.logout().then(function() {
      $scope.isLoggedIn = false;
      $scope.currentUser = '';
      $location.path('/login');
    }, function(error) {
      console.error('Logout failed:', JSON.stringify(error, null, 2));
    });
  };
}]);