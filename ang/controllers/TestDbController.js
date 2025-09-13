/**
 * @file TestDbController.js
 * @description Displays the database connection status.
 */
angular.module('myApp').controller('TestDbController', ['$scope', 'AjaxHelper', function($scope, AjaxHelper) {
    $scope.title = "Database Status";
    $scope.message = '';
    $scope.flashMessage = 'Loading database status...';
    $scope.flashType = 'info';

    AjaxHelper.ajaxRequest('GET', '/students/test_db')
        .then(function(response) {
            $scope.message = response.data.message;
            $scope.flashMessage = response.flashMessage;
            $scope.flashType = response.flashType;
        })
        .catch(function(error) {
            $scope.message = error.message;
            $scope.flashMessage = error.flashMessage;
            $scope.flashType = error.flashType;
        });
}]);