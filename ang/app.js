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
 * @param {Object} AjaxHelper - AJAX helper service
 * @param {Object} AuthService - Authentication service
 */
app.run(['$rootScope', '$cookies', '$location', 'AjaxHelper', 'AuthService', function($rootScope, $cookies, $location, AjaxHelper, AuthService) {
    console.log('App run block initialized');

    // Fetch CSRF token on app start
    AjaxHelper.ajaxRequest('GET', '/auth/get_csrf')
        .then(function(response) {
            if (response.data.csrf_token) {
                $cookies.csrf_token = response.data.csrf_token;
                console.log('Initial CSRF token set:', $cookies.csrf_token?.substring(0, 10) + '...');
            } else {
                console.error('No CSRF token in response:', response.data);
            }
        })
        .catch(function(error) {
            console.error('Failed to fetch initial CSRF token:', error);
            // Fallback to meta tag CSRF token
            var metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (metaCsrfToken) {
                $cookies.csrf_token = metaCsrfToken;
                console.log('Fallback CSRF token set from meta tag:', metaCsrfToken.substring(0, 10) + '...');
            }
        });

    // Sync session with server on app start
if (AuthService.isLoggedIn() || window.location.pathname.includes('/students')) {
    console.log('App init: Syncing session with server');
    AuthService.syncSessionWithServer()
        .then(function(user) {
            console.log('App init: Session synced successfully', user);
            $rootScope.isLoggedIn = true;
            $rootScope.currentUser = user.username;
            $rootScope.$broadcast('userLoggedIn');
            
            // If already on login/signup page but logged in, redirect to students
            if (window.location.pathname === '/login' || window.location.pathname === '/signup') {
                $location.path('/students');
            }
        })
        .catch(function(error) {
            console.log('App init: Session sync failed, user not logged in');
            $rootScope.isLoggedIn = false;
            $rootScope.currentUser = '';
            
            // Only redirect if trying to access protected pages without auth
            if (window.location.pathname.includes('/students') || 
                window.location.pathname.includes('/dashboard') ||
                window.location.pathname.includes('/test-db')) {
                $location.path('/login');
            }
        });
}

    // Global authentication state tracking
    $rootScope.isLoggedIn = AuthService.isLoggedIn();
    $rootScope.currentUser = AuthService.getCurrentUser();

    // Update global state when auth changes
    $rootScope.$on('userLoggedIn', function() {
        $rootScope.isLoggedIn = true;
        $rootScope.currentUser = AuthService.getCurrentUser();
        console.log('Global auth state updated: logged in as', $rootScope.currentUser);
    });

    $rootScope.$on('userLoggedOut', function() {
        $rootScope.isLoggedIn = false;
        $rootScope.currentUser = '';
        console.log('Global auth state updated: logged out');
    });

    $rootScope.$on('$routeChangeStart', function(event, next) {
        console.log('Route change to:', next?.originalPath);
        
        // Check authentication state fresh each time
        var isLoggedIn = AuthService.isLoggedIn();
        console.log('Route guard - isLoggedIn:', isLoggedIn, 'requireLogin:', next?.requireLogin);
        
        if (next?.requireLogin && !isLoggedIn) {
            console.log('Redirecting to login: User not authenticated');
            event.preventDefault();
            $location.path('/login');
        }
        
        // Update global state
        $rootScope.isLoggedIn = isLoggedIn;
        $rootScope.currentUser = AuthService.getCurrentUser();
    });
}]);