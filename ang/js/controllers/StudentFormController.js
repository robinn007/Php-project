/**
 * @file StudentFormController.js
 * @description Controller for managing the student form (add/edit).
 */


// StudentFormController
angular.module('myApp').controller('StudentFormController', ['$scope', '$routeParams', '$location', 'StudentService', '$rootScope', function($scope, $routeParams, $location, StudentService, $rootScope) {
  $scope.title = $routeParams.id ? 'Edit Student' : 'Add Student';
  $scope.student = { name: '', email: '', phone: '', address: '' };
  $scope.action = $routeParams.id ? 'edit' : 'add';
  $scope.flashMessage = '';
  $scope.flashType = '';

  console.log('StudentFormController initialized. Action:', $scope.action, 'ID:', $routeParams.id);

  // Load student data for editing if ID is provided
  if ($routeParams.id) {
    console.log('Fetching student data for ID:', $routeParams.id);
    StudentService.getStudent($routeParams.id).then(function(response) {
      console.log('getStudent response:', JSON.stringify(response.data, null, 2));
      if (response.data.success && response.data.student) {
        $scope.student = {
          name: response.data.student.name || '',
          email: response.data.student.email || '',
          phone: response.data.student.phone || '',
          address: response.data.student.address || ''
        };
        $scope.flashMessage = 'Student data loaded successfully.';
        $scope.flashType = 'success';
      } else {
        $scope.flashMessage = response.data.message || 'Failed to load student data: No student found.';
        $scope.flashType = 'error';
        console.error('Failed to load student data for ID:', $routeParams.id, 'Message:', response.data.message);
      }
    }, function(error) {
      console.error('Error loading student for ID:', $routeParams.id, JSON.stringify(error, null, 2));
      $scope.flashMessage = 'Error loading student: ' + (error.statusText || 'Network or server error');
      $scope.flashType = 'error';
    });
  }

   /**
   * @function submitForm
   * @description Submits the student form for adding or editing a student.
   */
  $scope.submitForm = function() {
    console.log('Submitting form. Action:', $scope.action, 'Student:', $scope.student);
    
    if ($scope.studentForm.$invalid) {
      $scope.flashMessage = 'Please correct the phone number in the form before submitting.';
      $scope.flashType = 'error';
      
      angular.forEach($scope.studentForm, function(field, name) {
        if (name[0] !== '$') {
          field.$setTouched();
        }
      });
      return;
    }

    if (!$scope.student.name || !$scope.student.email) {
      $scope.flashMessage = 'Please fill in all required fields.';
      $scope.flashType = 'error';
      return;
    }

    var promise = $scope.action === 'edit' ?
      StudentService.updateStudent($routeParams.id, $scope.student) :
      StudentService.addStudent($scope.student);

    promise.then(function(response) {
      console.log('Submit response:', JSON.stringify(response.data, null, 2));
      if (response.data.success) {
        $scope.flashMessage = response.data.message || ($scope.action === 'edit' ? 'Student updated successfully.' : 'Student added successfully.');
        $scope.flashType = 'success';
        $rootScope.$broadcast('studentUpdated');
        $location.path('/students'); // Updated to clean URL
      } else {
        $scope.flashMessage = response.data.message || 'Operation failed: Unknown error.';
        $scope.flashType = 'error';
        console.error('Submit failed:', response.data.message);
      }
    }, function(error) {
      console.error('Submit error:', JSON.stringify(error, null, 2));
      $scope.flashMessage = 'Error: ' + (error.statusText || 'Network or server error');
      $scope.flashType = 'error';
    });
  };


  /**
   * @function goToStudents
   * @description Navigates to the students list page.
   */
  $scope.goToStudents = function() {
    console.log('Navigating to /students');
    $location.path('/students'); // Updated to clean URL
  };
}]);