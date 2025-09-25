

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
 * @param {Object} SocketService - Socket.IO service
 */
app.run(['$rootScope', '$cookies', '$location', 'AjaxHelper', 'AuthService', 'SocketService', function($rootScope, $cookies, $location, AjaxHelper, AuthService, SocketService) {
    console.log('App run block initialized');

    // Fetch CSRF token on app start
    function fetchCsrfToken() {
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
                var metaCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (metaCsrfToken) {
                    $cookies.csrf_token = metaCsrfToken;
                    console.log('Fallback CSRF token set from meta tag:', metaCsrfToken.substring(0, 10) + '...');
                }
            });
    }

    fetchCsrfToken();

    // Sync session only if not coming from logout
    if (AuthService.isLoggedIn() && $location.search().logout !== 'true') {
        console.log('App init: Syncing session with server');
        AuthService.syncSessionWithServer()
            .then(function(user) {
                console.log('App init: Session synced successfully', user);
                $rootScope.isLoggedIn = true;
                $rootScope.currentUser = user.username;
                $rootScope.$broadcast('userLoggedIn');
                // Emit user_login event to socket
                SocketService.emit('user_login', { email: user.email });
                
                if (['/login', '/signup'].includes(window.location.pathname)) {
                    $location.path('/students').search({});
                    $scope.$applyAsync();
                }
            })
            .catch(function(error) {
                console.log('App init: Session sync failed, user not logged in');
                AuthService.logout();
                $rootScope.isLoggedIn = false;
                $rootScope.currentUser = '';
                SocketService.emit('user_logout', { email: AuthService.getCurrentUserEmail() });
                
                if (['/students', '/dashboard', '/test-db'].includes(window.location.pathname)) {
                    $location.path('/login').search({ logout: 'true' });
                    $scope.$applyAsync();
                }
            });
    } else {
        AuthService.logout();
        $rootScope.isLoggedIn = false;
        $rootScope.currentUser = '';
        SocketService.emit('user_logout', { email: AuthService.getCurrentUserEmail() });
    }

    // Global authentication state tracking
    $rootScope.isLoggedIn = AuthService.isLoggedIn();
    $rootScope.currentUser = AuthService.getCurrentUser();

    // Update global state when auth changes
    $rootScope.$on('userLoggedIn', function() {
        $rootScope.isLoggedIn = true;
        $rootScope.currentUser = AuthService.getCurrentUser();
        console.log('Global auth state updated: logged in as', $rootScope.currentUser);
        SocketService.emit('user_login', { email: AuthService.getCurrentUserEmail() });
    });

    $rootScope.$on('userLoggedOut', function() {
        $rootScope.isLoggedIn = false;
        $rootScope.currentUser = '';
        console.log('Global auth state updated: logged out');
        SocketService.emit('user_logout', { email: AuthService.getCurrentUserEmail() });
        fetchCsrfToken(); // Refresh CSRF token after logout
    });

    $rootScope.$on('$routeChangeStart', function(event, next) {
        console.log('Route change to:', next?.originalPath);
        
        var isLoggedIn = AuthService.isLoggedIn();
        console.log('Route guard - isLoggedIn:', isLoggedIn, 'requireLogin:', next?.requireLogin);
        
        if (next?.requireLogin && !isLoggedIn) {
            console.log('Redirecting to login: User not authenticated');
            event.preventDefault();
            $location.path('/login').search({ logout: 'true' });
        }
        
        $rootScope.isLoggedIn = isLoggedIn;
        $rootScope.currentUser = AuthService.getCurrentUser();
    });
}]);