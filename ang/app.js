/**
 * @file app.js
 * @description Main AngularJS application module for the Student Management System.
 * Initializes the application and enforces authentication on route changes.
 */

var app = angular.module('myApp', ['ngRoute', 'ngCookies', 'ngSanitize']);

/**
 * @description Initializes the application and enforces authentication on route changes.
 * @param {Object} $rootScope - Angular root scope
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $location - Angular location service
 */
app.run(['$rootScope', '$cookies', '$location', function($rootScope, $cookies, $location) {
  console.log('AngularJS app initialized - DEBUG MODE');

  /**
   * @event $routeChangeStart
   * @description Listens for route changes to manage authentication for protected routes.
   * Redirects to /login if authentication is required and user is not logged in.
   */
  $rootScope.$on('$routeChangeStart', function(event, next, current) {
    console.log('Route change to:', next.$$route ? next.$$route.originalPath : 'unknown');
    
    // Check if the route requires authentication and user is not logged in
    if (next.$$route && next.$$route.requireAuth && !$cookies.user_id) {
      console.log('Authentication required, redirecting to /login');
      event.preventDefault();
      $location.path('/login');
    }
  });
}]);