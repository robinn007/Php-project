/**
 * @file StudentFormController.js
 * @description Controller for managing the student form (add/edit) with contenteditable address field.
 */
angular.module('myApp').controller('StudentFormController', ['$scope', '$routeParams', '$location', 'StudentService', '$rootScope', '$sce', '$filter', function($scope, $routeParams, $location, StudentService, $rootScope, $sce, $filter) {
  $scope.title = $routeParams.id ? 'Edit Student' : 'Add Student';
  $scope.student = { name: '', email: '', phone: '', address: '' };
  $scope.action = $routeParams.id ? 'edit' : 'add';
  $scope.flashMessage = '';
  $scope.flashType = '';
  $scope.emailSuggestion = '';

  console.log('StudentFormController initialized. Action:', $scope.action, 'ID:', $routeParams.id);

  // Load student data for editing if ID is provided
  if ($routeParams.id) {
    console.log('Fetching student data for ID:', $routeParams.id);
    StudentService.getStudent($routeParams.id).then(function(response) {
      console.log('getStudent response:', JSON.stringify(response.data, null, 2));
      if (response.data.success && response.data.student) {
        $scope.student = {
          name: response.data.student.name || '',
          email: $filter('emailFilter')(response.data.student.email || '', 'clean'),
          phone: $filter('phoneFilter')(response.data.student.phone || '', 'clean'), // Clean phone
          address: response.data.student.address || ''
        };
        console.log('Loaded address field:', $scope.student.address);
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
   * @function cleanAddressContent
   * @description Cleans the address content from the contenteditable div
   * @param {string} content - Raw HTML content from contenteditable
   * @returns {string} Cleaned content
   */
  $scope.cleanAddressContent = function(content) {
    if (!content) return '';
    
    var cleaned = content.replace(/<p><br><\/p>/gi, '')
                        .replace(/<br\s*\/?>/gi, '\n')
                        .replace(/<div><br><\/div>/gi, '\n')
                        .replace(/<div>/gi, '\n')
                        .replace(/<\/div>/gi, '')
                        .trim();
    
    return cleaned;
  };

  /**
   * @function submitForm
   * @description Submits the student form for adding or editing a student.
   */
  $scope.submitForm = function() {
    console.log('Submitting form. Action:', $scope.action, 'Student:', JSON.stringify($scope.student, null, 2));
    
    if ($scope.studentForm.$invalid) {
      $scope.flashMessage = 'Please correct the errors in the form before submitting.';
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

    // Clean the email, phone, and address before submitting
    var studentData = angular.copy($scope.student);
    studentData.email = $filter('emailFilter')(studentData.email, 'clean');
    studentData.phone = $filter('phoneFilter')(studentData.phone, 'clean'); // Clean phone
    studentData.address = $scope.cleanAddressContent(studentData.address);

    var promise = $scope.action === 'edit' ?
      StudentService.updateStudent($routeParams.id, studentData) :
      StudentService.addStudent(studentData);

    promise.then(function(response) {
      console.log('Submit response:', JSON.stringify(response.data, null, 2));
      if (response.data.success) {
        $scope.flashMessage = response.data.message || ($scope.action === 'edit' ? 'Student updated successfully.' : 'Student added successfully.');
        $scope.flashType = 'success';
        $rootScope.$broadcast('studentUpdated');
        $location.path('/students');
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
    $location.path('/students');
  };
}]);