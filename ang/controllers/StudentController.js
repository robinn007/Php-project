/**
 * @file StudentController.js
 * @description Controller for managing student list view including fetching and deleting students.
 */
angular.module('myApp').controller('StudentController', ['$scope', 'AjaxHelper', '$sce', '$filter', function($scope, AjaxHelper, $sce, $filter) {
  $scope.title = "Students Dashboard......";
  $scope.students = [];
  $scope.flashMessage = 'Loading students...';
  $scope.flashType = 'info';

  console.log('StudentController initialized');

  $scope.loadStudents = function() {
    $scope.flashMessage = 'Loading students...';
    $scope.flashType = 'info';
    AjaxHelper.ajaxRequest('GET', '/ci/students/manage')
      .then(function(response) {
        console.log('getStudents response:', response);
        $scope.flashMessage = response.flashMessage;
        $scope.flashType = response.flashType;
        if (response.data.success) {
          $scope.students = response.data.students || [];
        }
      })
      .catch(function(error) {
        console.error('Error loading students:', error);
        $scope.flashMessage = error.flashMessage;
        $scope.flashType = error.flashType;
      });
  };

  // Initial load of students
  $scope.loadStudents();

  /**
   * @function deleteStudent
   * @description Soft deletes a student after user confirmation.
   * @param {number} id - Student ID to delete
   */
  $scope.deleteStudent = function(id) {
    if (confirm('Are you sure you want to delete this student?')) {
      AjaxHelper.ajaxRequest('POST', '/ci/students/manage', { action: 'delete', id: id })
        .then(function(response) {
          if (response.data.success) {
            $scope.students = $scope.students.filter(function(student) {
              return student.id !== id;
            });
            $scope.flashMessage = response.flashMessage;
            $scope.flashType = response.flashType;
          } else {
            $scope.flashMessage = response.flashMessage;
            $scope.flashType = response.flashType;
          }
        })
        .catch(function(error) {
          $scope.flashMessage = error.flashMessage;
          $scope.flashType = error.flashType;
        });
    }
  };

  /**
   * @event studentUpdated
   * @description Listens for student update events to refresh the student list.
   */
  $scope.$on('studentUpdated', function() {
    $scope.loadStudents();
  });
}]);