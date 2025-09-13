/**
 * @file StudentFormController.js
 * @description Controller for managing the student form (add/edit) with contenteditable address field.
 */
angular.module('myApp').controller('StudentFormController', ['$scope', '$routeParams', '$location', 'AjaxHelper', '$rootScope', '$sce', '$filter', function($scope, $routeParams, $location, AjaxHelper, $rootScope, $sce, $filter) {
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
    AjaxHelper.ajaxRequest('GET', '/students/get/' + $routeParams.id)
      .then(function(response) {
        console.log('getStudent response:', JSON.stringify(response.data, null, 2));
        $scope.flashMessage = response.flashMessage;
        $scope.flashType = response.flashType;
        if (response.data.success && response.data.student) {
          $scope.student = {
            name: response.data.student.name || '',
            email: $filter('emailFilter')(response.data.student.email || '', 'clean'),
            phone: $filter('phoneFilter')(response.data.student.phone || '', 'clean'),
            address: response.data.student.address || ''
          };
          console.log('Loaded address field:', $scope.student.address);
        }
      })
      .catch(function(error) {
        console.error('Error loading student for ID:', $routeParams.id, JSON.stringify(error, null, 2));
        $scope.flashMessage = error.flashMessage;
        $scope.flashType = error.flashType;
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
    studentData.phone = $filter('phoneFilter')(studentData.phone, 'clean');
    studentData.address = $scope.cleanAddressContent(studentData.address);

    var url = '/students/manage';
    var data = $scope.action === 'edit' ?
      { action: 'edit', id: $routeParams.id, student: studentData } :
      { action: 'add', student: studentData };

    AjaxHelper.ajaxRequest('POST', url, data)
      .then(function(response) {
        $scope.flashMessage = response.flashMessage;
        $scope.flashType = response.flashType;
        if (response.data.success) {
          $rootScope.$broadcast('studentUpdated');
          $location.path('/students');
        }
      })
      .catch(function(error) {
        $scope.flashMessage = error.flashMessage;
        $scope.flashType = error.flashType;
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