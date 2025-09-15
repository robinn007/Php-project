/**
 * @file NavController.js
 * @description Controller for managing navigation and user authentication state and logout.
 */
angular.module('myApp').controller('NavController', ['$scope', '$location', '$rootScope', 'AuthService', 'AjaxHelper', '$cookies', function($scope, $location, $rootScope, AuthService, AjaxHelper, $cookies) {
    
    // Initialize authentication state
    function updateAuthState() {
        $scope.isLoggedIn = AuthService.isLoggedIn();
        $scope.currentUser = AuthService.getCurrentUser();
        $scope.currentPath = $location.path();
        console.log('NavController - Auth state updated:', {
            isLoggedIn: $scope.isLoggedIn,
            currentUser: $scope.currentUser,
            currentPath: $scope.currentPath,
            cookies: {
                user_id: document.cookie.indexOf('user_id') !== -1,
                username: document.cookie.indexOf('username') !== -1
            }
        });
    }

    // Initial state
    updateAuthState();

    console.log('NavController initialized. isLoggedIn:', $scope.isLoggedIn, 'currentUser:', $scope.currentUser, 'currentPath:', $scope.currentPath);

    // Watch for route changes
    $scope.$on('$routeChangeSuccess', function() {
        updateAuthState();
        console.log('NavController - Route changed:', $scope.currentPath, 'isLoggedIn:', $scope.isLoggedIn);
    });

    // Watch for login events
    $rootScope.$on('userLoggedIn', function() {
        console.log('NavController - Received userLoggedIn event');
        updateAuthState();
    });

    // Watch for logout events
    $rootScope.$on('userLoggedOut', function() {
        console.log('NavController - Received userLoggedOut event');
        updateAuthState();
    });

    // Watch for cookie changes
    $scope.$watch(function() {
        return AuthService.isLoggedIn();
    }, function(newValue, oldValue) {
        if (newValue !== oldValue) {
            console.log('NavController - Login state changed from', oldValue, 'to', newValue);
            updateAuthState();
        }
    });

    $scope.logout = function() {
        console.log('Logging out user:', $scope.currentUser);
        
        // Prepare data with CSRF token
        var csrfTokenName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'ci_csrf_token';
        var csrfToken = $cookies.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var data = {};
        data[csrfTokenName] = csrfToken;

        console.log('NavController - Sending logout request with CSRF token:', csrfToken?.substring(0, 10) + '...');

        AjaxHelper.ajaxRequest('POST', '/auth/logout', data)
            .then(function(response) {
                console.log('Logout successful:', response.data);
                
                // Clear authentication data
                AuthService.logout();
                updateAuthState();
                
                // Broadcast logout event
                $rootScope.$broadcast('userLoggedOut');
                
                // Redirect to login page
                $location.path('/login');
                $scope.$applyAsync();
            })
            .catch(function(error) {
                console.error('Logout failed:', JSON.stringify(error, null, 2));
                
                // Even if the server request fails, clear local authentication data
                AuthService.logout();
                updateAuthState();
                
                // Broadcast logout event
                $rootScope.$broadcast('userLoggedOut');
                
                // Redirect to login page regardless of server response
                $location.path('/login');
                $scope.$applyAsync();
            });
    };
}]);