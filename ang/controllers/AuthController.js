/**
 * @file AuthController.js
 * @description Controller for managing user authentication.
 */
angular.module('myApp').controller('AuthController', ['$scope', '$location', '$cookies', 'AjaxHelper', '$rootScope', 'AuthService', function($scope, $location, $cookies, AjaxHelper, $rootScope, AuthService) {
    $scope.user = { email: '', password: '', username: '', confirm_password: '' };
    $scope.flashMessage = '';
    $scope.flashType = '';
    $scope.isSignup = $location.path() === '/signup';

    console.log('AuthController initialized');

    // Check if user is already logged in
    function checkAuth() {
        console.log('Checking auth status');
        AjaxHelper.ajaxRequest('GET', '/auth/check_auth')
            .then(function(response) {
                console.log('checkAuth response:', response);
                if (response.data.is_logged_in) {
                    console.log('User is already logged in, redirecting to /students');
                    
                    // Set cookies with user data if available
                    if (response.data.user) {
                        $cookies.user_id = response.data.user.id.toString();
                        $cookies.username = response.data.user.username;
                        $cookies.email = response.data.user.email;
                        console.log('User cookies set from check_auth response');
                    }
                    
                    $location.path('/students');
                    $scope.$applyAsync(); // Ensure digest cycle runs for redirection
                }
            })
            .catch(function(error) {
                console.error('Error checking auth:', error);
                $scope.flashMessage = error.flashMessage || 'Error checking authentication status';
                $scope.flashType = error.flashType || 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
            });
    }

    $scope.submitForm = function() {
        if (!$scope.isSignup) {
            // If already logged in, prevent login attempt
            if (AuthService.isLoggedIn()) {
                $scope.flashMessage = 'You are already logged in.';
                $scope.flashType = 'info';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                $location.path('/students');
                return;
            }
        }
        if ($scope.isSignup) {
            if (!$scope.user.username || !$scope.user.email || !$scope.user.password || !$scope.user.confirm_password) {
                $scope.flashMessage = 'Please fill in all required fields.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }
            if ($scope.user.password !== $scope.user.confirm_password) {
                $scope.flashMessage = 'Passwords do not match.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }
            if ($scope.user.password.length < 6) {
                $scope.flashMessage = 'Password must be at least 6 characters long.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }

            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test($scope.user.email)) {
                $scope.flashMessage = 'Please enter a valid email address.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }

            AjaxHelper.ajaxRequest('POST', '/auth/signup', $scope.user)
                .then(function(response) {
                    $scope.flashMessage = response.flashMessage;
                    $scope.flashType = response.flashType;
                    $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                    if (response.data.success) {
                        $location.path('/login');
                        $scope.$applyAsync();
                    }
                })
                .catch(function(error) {
                    $scope.flashMessage = error.flashMessage;
                    $scope.flashType = error.flashType;
                    $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                });
        } else {
            if (!$scope.user.email || !$scope.user.password) {
                $scope.flashMessage = 'Please fill in all required fields.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test($scope.user.email)) {
                $scope.flashMessage = 'Please enter a valid email address.';
                $scope.flashType = 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                return;
            }

            AjaxHelper.ajaxRequest('POST', '/auth/login', $scope.user)
                .then(function(response) {
                    $scope.flashMessage = response.flashMessage;
                    $scope.flashType = response.flashType;
                    $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                    if (response.data.success) {
                        $cookies.user_id = response.data.user.id.toString();
                        $cookies.username = response.data.user.username;
                        $cookies.email = response.data.user.email;
                        console.log('Cookies set:', { 
                            user_id: $cookies.user_id, 
                            username: $cookies.username, 
                            email: $cookies.email 
                        });
                        $location.path('/students');
                        $scope.$applyAsync();
                    }
                })
                .catch(function(error) {
                    $scope.flashMessage = error.flashMessage;
                    $scope.flashType = error.flashType;
                    $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                });
        }
    };

    // Initial auth check
    checkAuth();
}]);