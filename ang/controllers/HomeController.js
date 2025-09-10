/**
 * @file HomeController.js
 * @description Controller for the home page with static content and flash messages.
 */
angular.module('myApp').controller('HomeController', ['$scope', function($scope) {
  $scope.title = "Welcome to Student Management System";
  $scope.message = "Manage your student records efficiently.";
  $scope.flashMessage = '';
  $scope.flashType = '';
}]);