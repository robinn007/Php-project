/**
 * @description Main AngularJS application module for the Student Management System.
 * Initializes the application and handles CSRF token management and authentication checks.
 */

var app = angular.module('myApp', ['ngRoute', 'ngCookies']);

/**
 * @description Initializes the application, sets up CSRF token fetching, and enforces authentication on route changes.
 * @param {Object} $http - HTTP service used for API calls
 * @param {Object} $rootScope - Angular root scope
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $location - Angular location service
 */
app.run(['$http', '$rootScope', '$cookies', '$location', function($http, $rootScope, $cookies, $location) {
    console.log('AngularJS app initialized - DEBUG MODE');

    /**
     * @function fetchCsrfToken
     * @description Fetches CSRF token from the server and stores it in cookies.
     * @returns {Promise} Resolves with CSRF token data or logs an error on failure
     */
    $rootScope.fetchCsrfToken = function() {
        return $http.get('/ci/auth/get_csrf').then(function(response) {
            console.log('CSRF response:', response.data);
            if (response.data.csrf_token_name && response.data.csrf_token) {
                // Store CSRF token and name in cookies
                $cookies.csrf_token_name = response.data.csrf_token_name;
                $cookies.csrf_token = response.data.csrf_token;
                console.log('CSRF token stored successfully');
            } else {
                console.warn('No CSRF token received - continuing anyway');
            }
        }, function(error) {
            console.error('Failed to fetch CSRF token:', error);
        });
    };
    
    // Fetch CSRF token on app start
    $rootScope.fetchCsrfToken();

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