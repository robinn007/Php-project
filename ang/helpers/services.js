/**
 * @file services.js
 * @description Defines services for authentication-related operations in the Student Management System.
 */

/**
 * @ngdoc service
 * @name AuthService
 * @description Provides methods for authentication-related operations.
 * @param {Object} $cookies - Angular cookies service
 */
angular.module('myApp').factory('AuthService', ['$cookies', function($cookies) {
  return {
    /**
     * @function isLoggedIn
     * @description Checks if the user is logged in by verifying the user_id cookie.
     * @returns {boolean} True if logged in, false otherwise
     */
    isLoggedIn: function() {
      var userId = $cookies.user_id;
      console.log('Checking isLoggedIn, user_id:', userId);
      return !!userId;
    },
    /**
     * @function getCurrentUser
     * @description Returns the current user's username from cookies.
     * @returns {string} Username or empty string if not logged in
     */
    getCurrentUser: function() {
      return $cookies.username || '';
    },
    /**
     * @function logout
     * @description Clears authentication-related cookies.
     */
    logout: function() {
      console.log('AuthService: Clearing cookies');
      delete $cookies['user_id'];
      delete $cookies['username'];
      console.log('AuthService: Cookies cleared');
    }
  };
}]);