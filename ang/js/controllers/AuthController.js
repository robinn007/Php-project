/**
 * @file AuthController.js
 * @description Controller for managing user authentication.
 */

angular.module('myApp').controller('AuthController', ['$scope', '$location', '$cookies', '$http', function($scope, $location, $cookies, $http) {
  $scope.user = { email: '', password: '', username: '', confirm_password: '' };
  $scope.flashMessage = '';
  $scope.flashType = '';
  $scope.isSignup = $location.path() === '/signup';

  console.log('AuthController initialized');

   /**
    * @function submitForm
    * @description Submits login or signup form with validation.
    */

  $scope.submitForm = function() {
    if ($scope.isSignup) {
      if (!$scope.user.username || !$scope.user.email || !$scope.user.password || !$scope.user.confirm_password) {
        $scope.flashMessage = 'Please fill in all required fields.';
        $scope.flashType = 'error';
        return;
      }
      if ($scope.user.password !== $scope.user.confirm_password) {
        $scope.flashMessage = 'Passwords do not match.';
        $scope.flashType = 'error';
        return;
      }
      if ($scope.user.password.length < 6) {
        $scope.flashMessage = 'Password must be at least 6 characters long.';
        $scope.flashType = 'error';
        return;
      }

      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test($scope.user.email)) {
        $scope.flashMessage = 'Please enter a valid email address 2.';
        $scope.flashType = 'error';
        return;
      }

      $http.post('/ci/auth/signup', $scope.user).then(function(response) {
        console.log('Signup response:', response.data);
        if (response.data.success) {
          $scope.flashMessage = response.data.message || 'Account created successfully! Please log in.';
          $scope.flashType = 'success';
          $location.path('/login'); // Updated to clean URL
        } else {
          $scope.flashMessage = response.data.message || 'Failed to create account.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        console.error('Signup error:', error);
        $scope.flashMessage = 'Error: ' + (error.data.message || error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    } else {
      if (!$scope.user.email || !$scope.user.password) {
        $scope.flashMessage = 'Please fill in all required fields.';
        $scope.flashType = 'error';
        return;
      }
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test($scope.user.email)) {
        $scope.flashMessage = 'Please enter a valid email address 3.';
        $scope.flashType = 'error';
        return;
      }

      $http.post('/ci/auth/login', $scope.user).then(function(response) {
        console.log('Login response:', response.data);
        if (response.data.success) {
          $cookies.user_id = response.data.user.id.toString();
          $cookies.username = response.data.user.username;
          $cookies.email = response.data.user.email;
          console.log('Cookies set:', { 
            user_id: $cookies.user_id, 
            username: $cookies.username, 
            email: $cookies.email 
          });
          $scope.flashMessage = 'Login successful!';
          $scope.flashType = 'success';
          $location.path('/students'); // Updated to clean URL
        } else {
          $scope.flashMessage = response.data.message || 'Invalid email or password.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        console.error('Login error:', error);
        $scope.flashMessage = 'Error: ' + (error.data.message || error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    }
  };
}]);