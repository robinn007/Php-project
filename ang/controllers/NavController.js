/**
 * @file NavController.js
 * @description Controller for managing navigation and user authentication state and logout.
 */
angular.module('myApp').controller('NavController', ['$scope', '$location', 'AuthService', 'AjaxHelper', function($scope, $location, AuthService, AjaxHelper) {
    $scope.isLoggedIn = AuthService.isLoggedIn();
    $scope.currentUser = AuthService.getCurrentUser();
    $scope.currentPath = $location.path();

    console.log('NavController initialized. isLoggedIn:', $scope.isLoggedIn, 'currentUser:', $scope.currentUser, 'currentPath:', $scope.currentPath);

    $scope.$on('$routeChangeSuccess', function() {
        $scope.currentPath = $location.path();
        $scope.isLoggedIn = AuthService.isLoggedIn();
        $scope.currentUser = AuthService.getCurrentUser();
        console.log('Route changed. currentPath:', $scope.currentPath, 'isLoggedIn:', $scope.isLoggedIn);
    });

    $scope.logout = function() {
        console.log('Logging out user:', $scope.currentUser);
        AjaxHelper.ajaxRequest('GET', '/auth/logout')
            .then(function(response) {
                console.log('Logout successful:', response.data);
                AuthService.logout();
                $scope.isLoggedIn = false;
                $scope.currentUser = '';
                $location.path('/login');
            })
            .catch(function(error) {
                console.error('Logout failed:', JSON.stringify(error, null, 2));
                AuthService.logout();
                $scope.isLoggedIn = false;
                $scope.currentUser = '';
                $location.path('/login');
            });
    };
}]);