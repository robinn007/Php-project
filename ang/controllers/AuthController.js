/**
 * @file AuthController.js
 * @description Controller for managing user authentication.
 */
angular.module('myApp').controller('AuthController', ['$scope', '$location', '$cookies', 'AjaxHelper', '$rootScope', 'AuthService', '$q', 'SocketService', function($scope, $location, $cookies, AjaxHelper, $rootScope, AuthService, $q, SocketService) {
    $scope.user = { email: '', password: '', username: '', confirm_password: '' };
    $scope.flashMessage = '';
    $scope.flashType = '';
    $scope.isSignup = $location.path() === '/signup';

    console.log('AuthController initialized for path:', $location.path());

    function checkAuth() {
        // Don't check auth if user is trying to signup or if coming from logout
        if ($scope.isSignup || $location.search().logout === 'true') {
            console.log('Skipping auth check - signup page or logout flag detected');
            if ($location.search().logout === 'true') {
                AuthService.logout();
                SocketService.emit('user_logout', { email: $cookies.email || '' });
            }
            return;
        }

        console.log('Checking auth status');
        AjaxHelper.ajaxRequest('GET', '/auth/check_auth')
            .then(function(response) {
                console.log('checkAuth response:', response);
                if (response.data.is_logged_in) {
                    console.log('User is already logged in, redirecting to /students');
                    $cookies.user_id = response.data.user?.id.toString() || null;
                    $cookies.username = response.data.user?.username || null;
                    $cookies.email = response.data.user?.email || null;
                    console.log('User cookies set from check_auth response:', {
                        user_id: $cookies.user_id,
                        username: $cookies.username,
                        email: $cookies.email
                    });
                    // Emit user_login only if needed (handled in app.js)
                    $location.path('/students').search({});
                    $scope.$applyAsync();
                } else {
                    // Only redirect to login if we're not already on login page
                    if ($location.path() !== '/login') {
                        console.log('User not logged in, redirecting to login');
                        AuthService.logout();
                        SocketService.emit('user_logout', { email: $cookies.email || '' });
                        $location.path('/login').search({ logout: 'true' });
                        $scope.$applyAsync();
                    }
                }
            })
            .catch(function(error) {
                console.error('Error checking auth:', error);
                $scope.flashMessage = error.flashMessage || 'Error checking authentication status';
                $scope.flashType = error.flashType || 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                
                if ($location.path() !== '/login' && $location.path() !== '/signup') {
                    AuthService.logout();
                    SocketService.emit('user_logout', { email: $cookies.email || '' });
                    $location.path('/login').search({ logout: 'true' });
                    $scope.$applyAsync();
                }
            });
    }

    $scope.submitForm = function() {
        console.log('submitForm called, isSignup:', $scope.isSignup);
        
        if ($scope.isSignup) {
            handleSignup();
        } else {
            handleLogin();
        }
    };

    function handleLogin() {
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

        // Clear user-related cookies
        AuthService.clearUserCookies();
        
        // Simplified - get dummy CSRF token and proceed
        fetchCsrfToken().then(function(csrfToken) {
            if (csrfToken) {
                $cookies.csrf_token = csrfToken;
                console.log('CSRF token obtained:', csrfToken.substring(0, 10) + '...');
                performLogin();
            } else {
                console.log('No CSRF token received, proceeding anyway');
                performLogin(); // Proceed without CSRF since it's disabled
            }
        }).catch(function(error) {
            console.log('CSRF token failed, proceeding without it:', error);
            performLogin(); // Proceed without CSRF since it's disabled
        });
    }

    function fetchCsrfToken(attempt = 1, maxAttempts = 2) {
        var deferred = $q.defer();
        AjaxHelper.ajaxRequest('GET', '/auth/get_csrf')
            .then(function(response) {
                if (response.data.csrf_token) {
                    deferred.resolve(response.data.csrf_token);
                } else {
                    deferred.resolve('dummy_token'); // Fallback dummy token
                }
            })
            .catch(function(error) {
                console.log('CSRF fetch failed, using dummy token');
                deferred.resolve('dummy_token'); // Always resolve with dummy token
            });
        return deferred.promise;
    }

    function handleSignup() {
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

        performSignup();
    }

    function performLogin() {
        console.log('Performing login with user:', $scope.user.email);
        
        var postData = {
            email: $scope.user.email,
            password: $scope.user.password
        };

        if ($cookies.csrf_token && $cookies.csrf_token !== 'dummy_token') {
            var csrfTokenName = 'ci_csrf_token';
            postData[csrfTokenName] = $cookies.csrf_token;
        }

        AjaxHelper.ajaxRequest('POST', '/auth/login', postData)
            .then(function(response) {
                console.log('Login response:', response);
                $scope.flashMessage = response.flashMessage;
                $scope.flashType = response.flashType;
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                
                if (response.data.success) {
                    $cookies.user_id = response.data.user.id.toString();
                    $cookies.username = response.data.user.username;
                    $cookies.email = response.data.user.email;
                    console.log('Login successful, cookies set:', { 
                        user_id: $cookies.user_id, 
                        username: $cookies.username, 
                        email: $cookies.email 
                    });
                    
                    // Emit user_login is handled in app.js
                    $rootScope.$broadcast('userLoggedIn');
                    
                    $location.path('/students').search({});
                    $scope.$applyAsync();
                }
            })
            .catch(function(error) {
                console.error('Login error details:', error);
                $scope.flashMessage = error.flashMessage || 'Login failed: ' + (error.status === 500 ? 'Server error' : 'Network error');
                $scope.flashType = error.flashType || 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
            });
    }

    function performSignup() {
        console.log('Performing signup');
        
        var postData = {
            username: $scope.user.username,
            email: $scope.user.email,
            password: $scope.user.password,
            confirm_password: $scope.user.confirm_password
        };

        if ($cookies.csrf_token && $cookies.csrf_token !== 'dummy_token') {
            var csrfTokenName = 'ci_csrf_token';
            postData[csrfTokenName] = $cookies.csrf_token;
        }

        AjaxHelper.ajaxRequest('POST', '/auth/signup', postData)
            .then(function(response) {
                console.log('Signup response:', response);
                $scope.flashMessage = response.flashMessage;
                $scope.flashType = response.flashType;
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
                
                if (response.data.success) {
                    $location.path('/login').search({ logout: 'true' });
                    $scope.$applyAsync();
                }
            })
            .catch(function(error) {
                console.error('Signup error details:', error);
                $scope.flashMessage = error.flashMessage || 'Error during signup';
                $scope.flashType = error.flashType || 'error';
                $rootScope.$emit('flashMessage', { message: $scope.flashMessage, type: $scope.flashType });
            });
    }

    // Only run checkAuth for login page, not signup
    if (!$scope.isSignup) {
        checkAuth();
    }
}]);

