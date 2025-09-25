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
    isLoggedIn: function() {
      var userId = $cookies.user_id;
      console.log('Checking isLoggedIn, user_id:', userId);
      return !!userId;
    },
    getCurrentUser: function() {
      return $cookies.username || '';
    },
    getCurrentUserEmail: function() {
      return $cookies.email || '';
    },
    getUserId: function() {
      return $cookies.user_id || '';
    },
    syncSessionWithServer: function() {
      var deferred = $q.defer();
      console.log('AuthService: Syncing session with server');
      
      $http({
        method: 'GET',
        url: '/auth/check_auth',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json'
        }
      }).then(
        function(response) {
          console.log('AuthService: Session check response:', response.data);
          if (response.data.success && response.data.is_logged_in && response.data.user) {
            $cookies.user_id = response.data.user.id.toString();
            $cookies.username = response.data.user.username;
            $cookies.email = response.data.user.email;
            console.log('AuthService: Cookies synced with server session:', {
              user_id: $cookies.user_id,
              username: $cookies.username,
              email: $cookies.email
            });
            deferred.resolve(response.data.user);
          } else {
            console.log('AuthService: User not logged in on server');
            this.clearUserCookies();
            deferred.reject('Not logged in');
          }
        }.bind(this),
        function(error) {
          console.error('AuthService: Session check failed:', error);
          this.clearUserCookies();
          deferred.reject(error);
        }.bind(this)
      );
      
      return deferred.promise;
    },
    clearUserCookies: function() {
      console.log('AuthService: Clearing user-related cookies');
      
      var userCookies = ['user_id', 'username', 'email'];
      
      userCookies.forEach(function(key) {
        if ($cookies[key]) {
          $cookies[key] = null;
          console.log('AuthService: Cleared AngularJS cookie:', key);
        }
        document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        console.log('AuthService: Cleared document.cookie:', key);
      });
    },
    logout: function() {
      console.log('AuthService: Clearing cookies and session data');
      
      var authCookies = ['user_id', 'username', 'email', 'csrf_token', 'ci_session'];
      
      authCookies.forEach(function(key) {
        if ($cookies[key]) {
          $cookies[key] = null;
          console.log('AuthService: Cleared AngularJS cookie:', key);
        }
      });
      
      authCookies.forEach(function(key) {
        document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        console.log('AuthService: Cleared document.cookie:', key);
      });
      
      document.cookie.split(';').forEach(function(cookie) {
        var name = cookie.split('=')[0].trim(); // FIXED: was "név" (Hungarian), now "name"
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        console.log('AuthService: Ensured cookie cleared:', name);
      });
      
      console.log('AuthService: All cookies cleared');
    }
  };
}]);