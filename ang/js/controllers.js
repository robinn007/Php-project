/**
  * @file controllers.js
 * @description Defines AngularJS controllers for the Student Management System.
 * Includes navigation, home, student management, and authentication controllers.
 */


/**
 * @ngdoc controller
 * @name NavController
 * @description Controller for managing navigation and user authentication state and logout.
 */
// NavController
app.controller('NavController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
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
    AuthService.logout().then(function() {
      $scope.isLoggedIn = false;
      $scope.currentUser = '';
      $location.path('/login'); // Updated to clean URL
    }, function(error) {
      console.error('Logout failed:', JSON.stringify(error, null, 2));
    });
  };
}]);

/**
 * @ngdoc controller
 * @name HomeController
 * @description Controller for the home page with static content and flash messages.
 */
app.controller('HomeController', ['$scope', function($scope) {
  $scope.title = "Welcome to Student Management System";
  $scope.message = "Manage your student records efficiently.";
  $scope.flashMessage = '';
  $scope.flashType = '';
}]);


/**
 * @name StudentController
 * @description Controller for managing student list view including fetching and deleting students.
 *  StudentService - Service for student-related API calls
 */
app.controller('StudentController', ['$scope', 'StudentService', function($scope, StudentService) {
  $scope.title = "Students Dashboard.....";
  $scope.students = [];
  $scope.flashMessage = 'Loading students...';
  $scope.flashType = 'info';

  console.log('StudentController initialized');

   /**
   * @function loadStudents
   * @description Fetches active students from the server and updates the UI.
   */
  // Function to fetch students
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
            // Remove deleted student from the list
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
  // Listen for student update events
  $scope.$on('studentUpdated', function() {
    $scope.loadStudents();
  });
}]);


// when i try to render edited data on UI  step - 5
/**
 * @name StudentFormController
 * @description Controller for managing the student form (add/edit).
 * @param {Object} $scope - Angular scope object
 * @param {Object} $routeParams - Angular route parameters
 * @param {Object} $location - Angular location service
 * @param {Object} StudentService - Service for student-related API calls
 * @param {Object} $rootScope - Angular root scope
 */


// StudentFormController
app.controller('StudentFormController', ['$scope', '$routeParams', '$location', 'StudentService', '$rootScope', function($scope, $routeParams, $location, StudentService, $rootScope) {
  $scope.title = $routeParams.id ? 'Edit Student' : 'Add Student';
  $scope.student = { name: '', email: '', phone: '', address: '' };
  $scope.action = $routeParams.id ? 'edit' : 'add';
  $scope.flashMessage = '';
  $scope.flashType = '';

  console.log('StudentFormController initialized. Action:', $scope.action, 'ID:', $routeParams.id);

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

  $scope.goToStudents = function() {
    console.log('Navigating to /students');
    $location.path('/students'); // Updated to clean URL
  };
}]);


/**
 * @name DeletedStudentsController
 * @description Manages the deleted students archive view, including restore and permanent delete.
 *  * @param {Object} $scope - Angular scope object
 * @param {Object} StudentService - Service for student-related API calls
 */
app.controller('DeletedStudentsController', ['$scope', 'StudentService', function($scope, StudentService) {
  $scope.title = "Deleted Students Archive";
  $scope.students = [];
  $scope.flashMessage = 'Loading deleted students...';
  $scope.flashType = 'info';

  // Fetch deleted students
  StudentService.getDeletedStudents().then(function(response) {
    if (response.data.success) {
      $scope.students = response.data.students;
      if ($scope.students.length === 0) {
        $scope.flashMessage = 'No deleted students found.';
        $scope.flashType = 'info';
      } else {
        $scope.flashMessage = 'Loaded ' + $scope.students.length + ' deleted students.';
        $scope.flashType = 'success';
      }
    } else {
      $scope.flashMessage = response.data.message || 'Failed to load deleted students.';
      $scope.flashType = 'error';
    }
  }, function(error) {
    $scope.flashMessage = 'Error loading deleted students: ' + (error.statusText || 'Unknown error');
    $scope.flashType = 'error';
  });

   /**
   * @function restoreStudent
   * @description Restores a soft-deleted student after confirmation.
   * @param {number} id - Student ID to restore
   */
  $scope.restoreStudent = function(id) {
    if (confirm('Are you sure you want to restore this student?')) {
      StudentService.restoreStudent(id).then(function(response) {
        if (response.data.success) {
             // Remove restored student from the list
          $scope.students = $scope.students.filter(function(student) {
            return student.id !== id;
          });
          $scope.flashMessage = response.data.message || 'Student restored successfully.';
          $scope.flashType = 'success';
        } else {
          $scope.flashMessage = response.data.message || 'Failed to restore student.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        $scope.flashMessage = 'Error restoring student: ' + (error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    }
  };

   /**
   * @function permanentDelete
   * @description Permanently deletes a student after confirmation.
   * @param {number} id - Student ID to permanently delete
   */
  $scope.permanentDelete = function(id) {
    if (confirm('Are you sure you want to permanently delete this student? This action cannot be undone!')) {
      StudentService.permanentDelete(id).then(function(response) {
        if (response.data.success) {
          // Remove deleted student from the list
          $scope.students = $scope.students.filter(function(student) {
            return student.id !== id;
          });
          $scope.flashMessage = response.data.message || 'Student permanently deleted successfully.';
          $scope.flashType = 'success';
        } else {
          $scope.flashMessage = response.data.message || 'Failed to delete student permanently.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        $scope.flashMessage = 'Error deleting student permanently: ' + (error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    }
  };
}]);
/**
 * @name TestDbController
 * @description Displays the database connection status.
 * @param {Object} $scope - Angular scope object
 * @param {Object} $http - Angular HTTP service
 */
app.controller('TestDbController', ['$scope', '$http', function($scope, $http) {
  $scope.title = "Database Status";
  $scope.message = '';
  $scope.flashMessage = 'Loading database status...';
  $scope.flashType = 'info';

   // Fetch database status
  $http.get('/ci/students/test_db').then(function(response) {
    $scope.message = response.data.message;
    $scope.flashMessage = 'Database status loaded successfully.';
    $scope.flashType = 'success';
  }, function(error) {
    $scope.message = 'Error: ' + (error.statusText || 'Unknown error');
    $scope.flashMessage = 'Failed to load database status.';
    $scope.flashType = 'error';
  });
}]);
/**
 * @name AuthController
 * @description Controller for managing user authentication.
 * @param {Object} $scope - Angular scope object
 * @param {Object} $location - Angular location service
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $http - Angular HTTP service
 */

// AuthController
app.controller('AuthController', ['$scope', '$location', '$cookies', '$http', function($scope, $location, $cookies, $http) {
  $scope.user = { email: '', password: '', username: '', confirm_password: '' };
  $scope.flashMessage = '';
  $scope.flashType = '';
  $scope.isSignup = $location.path() === '/signup';

  console.log('AuthController initialized');

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
        $scope.flashMessage = 'Please enter a valid email address 2.';
        $scope.flashType = 'error';
        return;
      }

      $http.post('/ci/auth/signup', $scope.user).then(function(response) {
        console.log('Signup response:', response.data);
        if (response.data.success) {
          $scope.flashMessage = response.data.message || 'Account created successfully! Please log in.';
          $scope.flashType = 'success';
          $location.path('/login'); // Updated to clean URL
        } else {
          $scope.flashMessage = response.data.message || 'Failed to create account.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        console.error('Signup error:', error);
        $scope.flashMessage = 'Error: ' + (error.data.message || error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    } else {
      if (!$scope.user.email || !$scope.user.password) {
        $scope.flashMessage = 'Please fill in all required fields.';
        $scope.flashType = 'error';
        return;
      }
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test($scope.user.email)) {
        $scope.flashMessage = 'Please enter a valid email address 3.';
        $scope.flashType = 'error';
        return;
      }

      $http.post('/ci/auth/login', $scope.user).then(function(response) {
        console.log('Login response:', response.data);
        if (response.data.success) {
          $cookies.user_id = response.data.user.id.toString();
          $cookies.username = response.data.user.username;
          $cookies.email = response.data.user.email;
          console.log('Cookies set:', { 
            user_id: $cookies.user_id, 
            username: $cookies.username, 
            email: $cookies.email 
          });
          $scope.flashMessage = 'Login successful!';
          $scope.flashType = 'success';
          $location.path('/students'); // Updated to clean URL
        } else {
          $scope.flashMessage = response.data.message || 'Invalid email or password.';
          $scope.flashType = 'error';
        }
      }, function(error) {
        console.error('Login error:', error);
        $scope.flashMessage = 'Error: ' + (error.data.message || error.statusText || 'Unknown error');
        $scope.flashType = 'error';
      });
    }
  };
}]);