/**
 * @file services.js
 * @description Defines services for API communication and authentication in the Student Management System.
 */

/**
 * @ngdoc service
 * @name StudentService
 * @description Provides methods for student-related API operations.
 * @param {Object} $http - Angular HTTP service
 */
app.factory('StudentService', ['$http', function($http) {
  var baseUrl = '/ci/students';

  return {
    /**
     * @function getStudents
     * @description Fetches the list of active students.
     * @returns {Promise} Resolves with student data or rejects with an error
     */
    getStudents: function() {
      console.log('Calling getStudents: ' + baseUrl + '/manage');
      return $http.get(baseUrl + '/manage');
    },
    /**
     * @function getStudent
     * @description Fetches a single student by ID.
     * @param {number} id - Student ID
     * @returns {Promise} Resolves with student data or rejects with an error
     */
    getStudent: function(id) {
      console.log('Calling getStudent: ' + baseUrl + '/get/' + id);
      return $http.get(baseUrl + '/get/' + id);
    },
    /**
     * @function addStudent
     * @description Adds a new student.
     * @param {Object} student - Student data to add
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    addStudent: function(student) {
      console.log('Calling addStudent: ' + baseUrl + '/manage');
      return $http.post(baseUrl + '/manage', { action: 'add', student: student });
    },
    /**
     * @function updateStudent
     * @description Updates an existing student.
     * @param {number} id - Student ID
     * @param {Object} student - Updated student data
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    updateStudent: function(id, student) {
      console.log('Calling updateStudent: ' + baseUrl + '/manage');
      return $http.post(baseUrl + '/manage', { action: 'edit', id: id, student: student });
    },
    /**
     * @function deleteStudent
     * @description Soft deletes a student.
     * @param {number} id - Student ID
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    deleteStudent: function(id) {
      console.log('Calling deleteStudent: ' + baseUrl + '/manage');
      return $http.post(baseUrl + '/manage', { action: 'delete', id: id });
    },
    /**
     * @function getDeletedStudents
     * @description Fetches the list of soft-deleted students.
     * @returns {Promise} Resolves with deleted student data or rejects with an error
     */
    getDeletedStudents: function() {
      console.log('Calling getDeletedStudents: ' + baseUrl + '/deleted');
      return $http.get(baseUrl + '/deleted');
    },
    /**
     * @function restoreStudent
     * @description Restores a soft-deleted student.
     * @param {number} id - Student ID
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    restoreStudent: function(id) {
      console.log('Calling restoreStudent: ' + baseUrl + '/restore/' + id);
      return $http.post(baseUrl + '/restore/' + id, { action: 'restore', id: id });
    },
    /**
     * @function permanentDelete
     * @description Permanently deletes a student.
     * @param {number} id - Student ID
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    permanentDelete: function(id) {
      console.log('Calling permanentDelete: ' + baseUrl + '/permanent_delete/' + id);
      return $http.post(baseUrl + '/permanent_delete/' + id, { action: 'permanent_delete', id: id });
    }
  };
}]);

/**
 * @ngdoc service
 * @name AuthService
 * @description Provides methods for authentication-related operations.
 * @param {Object} $http - Angular HTTP service
 * @param {Object} $cookies - Angular cookies service
 * @param {Object} $location - Angular location service
 */
app.factory('AuthService', ['$http', '$cookies', '$location', function($http, $cookies, $location) {
  var baseUrl = '/ci/auth';

  return {
    /**
     * @function login
     * @description Sends a login request to the server.
     * @param {Object} credentials - User credentials (email, password)
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    login: function(credentials) {
      return $http.post(baseUrl + '/login', credentials);
    },
    /**
     * @function signup
     * @description Sends a signup request to the server.
     * @param {Object} user - User data (username, email, password)
     * @returns {Promise} Resolves with response data or rejects with an error
     */
    signup: function(user) {
      return $http.post(baseUrl + '/signup', user);
    },
    /**
     * @function logout
     * @description Logs out the user and clears authentication cookies.
     * @returns {Promise} Resolves after logout or rejects with an error
     */
    logout: function() {
      return $http.get(baseUrl + '/logout').then(function() {
        // Clear authentication cookies
        delete $cookies.user_id;
        delete $cookies.username;
        delete $cookies.email;
      });
    },
    /**
     * @function isLoggedIn
     * @description Checks if the user is logged in by verifying the user_id cookie.
     * @returns {boolean} True if logged in, false otherwise
     */
    isLoggedIn: function() {
      var userId = $cookies.user_id;
      console.log('Checking isLoggedIn, user_id:', userId);
      return !!userId;
    },
    /**
     * @function getCurrentUser
     * @description Returns the current user's username from cookies.
     * @returns {string} Username or empty string if not logged in
     */
    getCurrentUser: function() {
      return $cookies.username || '';
    }
  };
}]);