/**
 * @file StudentController.js
 * @description Controller for managing student list view including fetching and deleting students.
 */
angular.module('myApp').controller('StudentController', ['$scope', 'StudentService', '$sce', '$filter', function($scope, StudentService, $sce, $filter) {
  $scope.title = "Students Dashboard......";
  $scope.students = [];
  $scope.flashMessage = 'Loading students...';
  $scope.flashType = 'info';
  
  console.log('StudentController initialized');

  /**
   * @function getFormattedAddress
   * @description Safely renders HTML formatted address content (truncated to 40 chars)
   * @param {string} address - Raw address content from contenteditable
   * @returns {string} Trusted HTML content
   */
  $scope.getFormattedAddress = function(address) {
    if (!address) return 'N/A';
    
    // Use the addressFilter to get properly formatted and truncated content
    var addressFilter = $filter('addressFilter');
    var formatted = addressFilter(address, 'shortWithFormatting');
    
    // Trust the HTML content for rendering
    return $sce.trustAsHtml(formatted);
  };

  $scope.loadStudents = function() {
    $scope.flashMessage = 'Loading students...';
    $scope.flashType = 'info';
    StudentService.getStudents().then(function(response) {
      console.log('getStudents response:', response);
      if (response.data.success) {
        $scope.students = response.data.students || [];
        $scope.flashMessage = 'Loaded ' + $scope.students.length + ' active students.';
        $scope.flashType = 'success';
      } else {
        $scope.flashMessage = response.data.message || 'Failed to load students.';
        $scope.flashType = 'error';
      }
    }, function(error) {
      console.error('Error loading students:', error);
      $scope.flashMessage = 'Error loading students: ' + (error.statusText || 'Unknown error');
      $scope.flashType = 'error';
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
      StudentService.deleteStudent(id).then(function(response) {
        if (response.data.success) {
          $scope.students = $scope.students.filter(function(student) {
            return student.id !== id;
          });
          $scope.flashMessage = response.data.message || 'Student deleted successfully.';
          $scope.flashType = 'success';
        } else {
          $scope.flashMessage = response.data.message || 'Failed to delete student.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        $scope.flashMessage = 'Error deleting student: ' + (error.statusText || 'Unknown error');
        $scope.flashType = 'error';
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