/**
 * @file StudentFormController.js
 * @description Controller for managing the student form (add/edit) with contenteditable address field and state dropdown.
 */
angular.module('myApp').controller('StudentFormController', ['$scope', '$routeParams', '$location', 'AjaxHelper', '$rootScope', '$sce', '$filter', function($scope, $routeParams, $location, AjaxHelper, $rootScope, $sce, $filter) {
  $scope.title = $routeParams.id ? 'Edit Student' : 'Add Student';
  $scope.student = {
    name: '',
    email: '',
    phone: '',
    address: '',
    state: 'Rajasthan' // Initialize state with default value
  };
  $scope.action = $routeParams.id ? 'edit' : 'add';
  $scope.flashMessage = '';
  $scope.flashType = '';
  $scope.emailSuggestion = '';

  // Define and sort states alphabetically
  $scope.states = [
    'Andaman and Nicobar Islands','Andhra Pradesh','Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chhattisgarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa','Gujarat', 'Haryana','Himachal Pradesh','Jammu and Kashmir','Jharkhand','Karnataka','Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh','Maharashtra','Manipur', 'Meghalaya','Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'
  ].sort(); // Sort alphabetically

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
            address: response.data.student.address || '',
            state: response.data.student.state || 'Rajasthan' // Set state, default to Rajasthan if not present
          };
          console.log('Loaded student data:', JSON.stringify($scope.student, null, 2));
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

    if (!$scope.student.name || !$scope.student.email || !$scope.student.state) {
      $scope.flashMessage = 'Please fill in all required fields.';
      $scope.flashType = 'error';
      console.error('Missing required fields:', {
        name: $scope.student.name,
        email: $scope.student.email,
        state: $scope.student.state
      });
      return;
    }

    // Prepare the data in a flat structure for CodeIgniter
    var studentData = {
      name: $scope.student.name,
      email: $filter('emailFilter')($scope.student.email, 'clean'),
      phone: $filter('phoneFilter')($scope.student.phone, 'clean'),
      address: $scope.cleanAddressContent($scope.student.address),
      state: $scope.student.state || 'Rajasthan'
    };

    var url = '/students/manage';
    var data = $scope.action === 'edit' ?
      { action: 'edit', id: $routeParams.id, ...studentData } :
      { action: 'add', ...studentData };

    console.log('Sending data to server:', JSON.stringify(data, null, 2));

    AjaxHelper.ajaxRequest('POST', url, data)
      .then(function(response) {
        console.log('Form submission response:', JSON.stringify(response, null, 2));
        $scope.flashMessage = response.flashMessage;
        $scope.flashType = response.flashType;
        if (response.data.success) {
          $rootScope.$broadcast('studentUpdated');
          $location.path('/students');
        }
      })
      .catch(function(error) {
        console.error('Error submitting form:', JSON.stringify(error, null, 2));
        $scope.flashMessage = error.flashMessage || 'Failed to submit form: ' + error.message;
        $scope.flashType = error.flashType || 'error';
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