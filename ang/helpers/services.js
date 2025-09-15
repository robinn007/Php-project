/**
 * @file services.js
 * @description Defines services for authentication-related operations in the Student Management System.
 */

/**
 * @ngdoc service
 * @name AuthService
 * @description Provides methods for authentication-related operations.
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $http - Angular HTTP service
 * @param {Object} $q - Angular promise service
 */
angular.module('myApp').factory('AuthService', ['$cookies', '$http', '$q', function($cookies, $http, $q) {
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
     * @function getCurrentUserEmail
     * @description Returns the current user's email from cookies.
     * @returns {string} Email or empty string if not logged in
     */
    getCurrentUserEmail: function() {
      return $cookies.email || '';
    },
    /**
     * @function getUserId
     * @description Returns the current user's ID from cookies.
     * @returns {string} User ID or empty string if not logged in
     */
    getUserId: function() {
      return $cookies.user_id || '';
    },
    /**
     * @function syncSessionWithServer
     * @description Synchronizes local authentication state with server session.
     * @returns {Promise} Promise that resolves with user data or rejects if not logged in
     */
    syncSessionWithServer: function() {
      var deferred = $q.defer();
      console.log('AuthService: Syncing session with server');
      
      $http({
        method: 'GET',
        url: '/auth/check_auth', // Changed from /auth/check_session to /auth/check_auth
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      }).then(
        function(response) {
          console.log('AuthService: Session check response:', response.data);
          if (response.data.success && response.data.is_logged_in && response.data.user) {
            // Update cookies with server data
            $cookies.user_id = response.data.user.id.toString();
            $cookies.username = response.data.user.username;
            $cookies.email = response.data.user.email;
            console.log('AuthService: Cookies synced with server session');
            deferred.resolve(response.data.user);
          } else {
            console.log('AuthService: User not logged in on server');
            this.logout();
            deferred.reject('Not logged in');
          }
        }.bind(this),
        function(error) {
          console.error('AuthService: Session check failed:', error);
          deferred.reject(error);
        }
      );
      
      return deferred.promise;
    },
    /**
     * @function logout
     * @description Clears authentication-related cookies and session data.
     */
    logout: function() {
      console.log('AuthService: Clearing cookies and session data');
      
      // Explicitly clear known auth-related cookies
      var authCookies = ['user_id', 'username', 'email', 'csrf_token'];
      authCookies.forEach(function(key) {
        if ($cookies[key]) {
          $cookies[key] = null; // Set to null to clear in AngularJS 1.3.0
          console.log('AuthService: Cleared cookie:', key);
        }
      });
      
      // Also clear from document.cookie to ensure complete removal
      authCookies.forEach(function(key) {
        document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        console.log('AuthService: Cleared document.cookie:', key);
      });
      
      console.log('AuthService: All auth cookies cleared');
    }
  };
}]);