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
        url: '/auth/check_session',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      }).then(
        function(response) {
          console.log('AuthService: Session check response:', response.data);
          if (response.data.success && response.data.logged_in && response.data.user) {
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
     * @description Clears authentication-related cookies.
     */
    /**
 * @function logout
 * @description Clears authentication-related cookies and session data.
 */
logout: function() {
    console.log('AuthService: Clearing cookies and session data');
    
    // Clear all auth-related cookies
    var cookies = $cookies.getAll();
    Object.keys(cookies).forEach(function(key) {
        if (key === 'user_id' || key === 'username' || key === 'email' || key === 'csrf_token') {
            delete $cookies[key];
            console.log('AuthService: Cleared cookie:', key);
        }
    });
    
    // Also clear from document.cookie to ensure complete removal
    document.cookie = 'user_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    document.cookie = 'username=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    document.cookie = 'email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    document.cookie = 'csrf_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    
    console.log('AuthService: All auth cookies cleared');
}
  };
}]);