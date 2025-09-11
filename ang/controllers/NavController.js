/**
 * @file NavController.js
 * @description Controller for managing navigation and user authentication state and logout.
 */
angular.module('myApp').controller('NavController', ['$scope', '$location', 'AuthService', 'AjaxHelper', function($scope, $location, AuthService, AjaxHelper) {
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
    AjaxHelper.ajaxRequest('GET', '/ci/auth/logout')
      .then(function(response) {
        console.log('Logout successful:', response.data);
        AuthService.logout(); // Call synchronous logout method
        $scope.isLoggedIn = false;
        $scope.currentUser = '';
        $location.path('/login');
      })
      .catch(function(error) {
        console.error('Logout failed:', JSON.stringify(error, null, 2));
        AuthService.logout(); // Clear cookies even on error
        $scope.isLoggedIn = false;
        $scope.currentUser = '';
        $location.path('/login');
      });
  };
}]);