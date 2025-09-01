app.controller('NavController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
  $scope.isLoggedIn = AuthService.isLoggedIn();
  $scope.currentUser = AuthService.getCurrentUser();
  $scope.currentPath = $location.path();

  $scope.$on('$routeChangeSuccess', function() {
    $scope.currentPath = $location.path();
    $scope.isLoggedIn = AuthService.isLoggedIn();
    $scope.currentUser = AuthService.getCurrentUser();
  });

  $scope.logout = function() {
    AuthService.logout().then(function() {
      $scope.isLoggedIn = false;
      $scope.currentUser = '';
      $location.path('/login');
      $scope.flashMessage = 'Logged out successfully.';
      $scope.flashType = 'success';
    }, function(error) {
      $scope.flashMessage = 'Logout failed: ' + (error.statusText || 'Unknown error');
      $scope.flashType = 'error';
    });
  };
}]);

app.controller('HomeController', ['$scope', function($scope) {
  $scope.title = "Welcome to Student Management System";
  $scope.message = "Manage your student records efficiently.";
  $scope.flashMessage = '';
  $scope.flashType = '';
}]);

app.controller('StudentController', ['$scope', 'StudentService', function($scope, StudentService) {
  $scope.title = "Students Dashboard";
  $scope.students = [];
  $scope.flashMessage = 'Loading students...';
  $scope.flashType = 'info';

  console.log('StudentController initialized');
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
}]);

app.controller('StudentFormController', ['$scope', '$routeParams', '$location', 'StudentService', function($scope, $routeParams, $location, StudentService) {
  $scope.title = $routeParams.id ? 'Edit Student' : 'Add Student 3';
  $scope.student = { name: '', email: '', phone: '', address: '' };
  $scope.action = $routeParams.id ? 'edit' : 'add';
  $scope.flashMessage = '';
  $scope.flashType = '';

  if ($routeParams.id) {
    StudentService.getStudent($routeParams.id).then(function(response) {
      if (response.data.success) {
        $scope.student = response.data.student;
      } else {
        $scope.flashMessage = response.data.message || 'Failed to load student.';
        $scope.flashType = 'error';
      }
    }, function(error) {
      $scope.flashMessage = 'Error loading student: ' + (error.statusText || 'Unknown error');
      $scope.flashType = 'error';
    });
  }

  $scope.submitForm = function() {
    if (!$scope.student.name || !$scope.student.email) {
      $scope.flashMessage = 'Please fill in all required fields.';
      $scope.flashType = 'error';
      return;
    }

    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test($scope.student.email)) {
      $scope.flashMessage = 'Please enter a valid email address 1.';
      $scope.flashType = 'error';
      return;
    }

    var promise = $scope.action === 'edit' ?
      StudentService.updateStudent($routeParams.id, $scope.student) :
      StudentService.addStudent($scope.student);

    promise.then(function(response) {
      if (response.data.success) {
        $scope.flashMessage = response.data.message || ($scope.action === 'edit' ? 'Student updated successfully.' : 'Student added successfully.');
        $scope.flashType = 'success';
        $location.path('/students');
      } else {
        $scope.flashMessage = response.data.message || 'Operation failed.';
        $scope.flashType = 'error';
      }
    }, function(error) {
      $scope.flashMessage = 'Error: ' + (error.statusText || 'Unknown error');
      $scope.flashType = 'error';
    });
  };
}]);

app.controller('DeletedStudentsController', ['$scope', 'StudentService', function($scope, StudentService) {
  $scope.title = "Deleted Students Archive";
  $scope.students = [];
  $scope.flashMessage = 'Loading deleted students...';
  $scope.flashType = 'info';

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

  $scope.restoreStudent = function(id) {
    if (confirm('Are you sure you want to restore this student?')) {
      StudentService.restoreStudent(id).then(function(response) {
        if (response.data.success) {
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

  $scope.permanentDelete = function(id) {
    if (confirm('Are you sure you want to permanently delete this student? This action cannot be undone!')) {
      StudentService.permanentDelete(id).then(function(response) {
        if (response.data.success) {
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

app.controller('TestDbController', ['$scope', '$http', function($scope, $http) {
  $scope.title = "Database Status";
  $scope.message = '';
  $scope.flashMessage = 'Loading database status...';
  $scope.flashType = 'info';

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
          $location.path('/login');
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
          // Use AngularJS 1.3.0 cookie syntax
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
          $location.path('/students');
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