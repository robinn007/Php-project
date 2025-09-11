/**
 * @file AuthController.js
 * @description Controller for managing user authentication.
 */
angular.module('myApp').controller('AuthController', ['$scope', '$location', '$cookies', 'AjaxHelper', function($scope, $location, $cookies, AjaxHelper) {
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
        $scope.flashMessage = 'Please enter a valid email address.';
        $scope.flashType = 'error';
        return;
      }

      AjaxHelper.ajaxRequest('POST', '/ci/auth/signup', $scope.user)
        .then(function(response) {
          $scope.flashMessage = response.flashMessage;
          $scope.flashType = response.flashType;
          if (response.data.success) {
            $location.path('/login');
          }
        })
        .catch(function(error) {
          $scope.flashMessage = error.flashMessage;
          $scope.flashType = error.flashType;
        });
    } else {
      if (!$scope.user.email || !$scope.user.password) {
        $scope.flashMessage = 'Please fill in all required fields.';
        $scope.flashType = 'error';
        return;
      }
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test($scope.user.email)) {
        $scope.flashMessage = 'Please enter a valid email address.';
        $scope.flashType = 'error';
        return;
      }

      AjaxHelper.ajaxRequest('POST', '/ci/auth/login', $scope.user)
        .then(function(response) {
          $scope.flashMessage = response.flashMessage;
          $scope.flashType = response.flashType;
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
          }
        })
        .catch(function(error) {
          $scope.flashMessage = error.flashMessage;
          $scope.flashType = error.flashType;
        });
    }
  };
}]);


// angular.module('myApp').controller('AuthController', ['$scope', '$location', 'AjaxHelper', 'AuthService', function($scope, $location, AjaxHelper, AuthService) {
//     $scope.user = {};
//     $scope.flashMessage = '';
//     $scope.flashType = '';
//     $scope.isLoggedIn = AuthService.isLoggedIn();
//     $scope.currentUser = AuthService.getCurrentUser();

//     $scope.submitForm = function() {
//         var url = $scope.isSignup ? '/ci/auth/signup' : '/ci/auth/login';
//         AjaxHelper.ajaxRequest('POST', url, $scope.user).then(function(response) {
//             if (response.data.success) {
//                 $scope.flashMessage = $scope.isSignup ? 'Registration successful! Please log in.' : 'Login successful!';
//                 $scope.flashType = 'success';
//                 if ($scope.isSignup) {
//                     $scope.user = {}; // Clear form
//                     $location.path('/login');
//                 } else {
//                     // Set cookies for logged-in user
//                     $cookies['user_id'] = response.data.user_id;
//                     $cookies['username'] = response.data.username;
//                     $scope.isLoggedIn = true;
//                     $scope.currentUser = response.data.username;
//                     $location.path('/students/dashboard');
//                 }
//                 // Update CSRF token in DOM
//                 angular.element('meta[name="csrf-token"]').attr('content', response.data.csrf_token);
//             } else {
//                 $scope.flashMessage = response.data.message || ($scope.isSignup ? 'Registration failed' : 'Login failed');
//                 $scope.flashType = 'error';
//             }
//         }, function(error) {
//             $scope.flashMessage = 'Error: ' + (error.message || 'Unknown error');
//             $scope.flashType = 'error';
//         });
//     };
// }]);