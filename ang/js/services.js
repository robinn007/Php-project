app.factory('StudentService', ['$http', function($http) {
  var baseUrl = '/ci/students/manage';

  return {
    getStudents: function() {
      return $http.get(baseUrl);
    },
    getStudent: function(id) {
      return $http.get(baseUrl + '/edit/' + id);
    },
    addStudent: function(student) {
      return $http.post(baseUrl + '/add', { action: 'add', student: student });
    },
    updateStudent: function(id, student) {
      return $http.post(baseUrl + '/edit/' + id, { action: 'edit', id: id, student: student });
    },
    deleteStudent: function(id) {
      return $http.post(baseUrl + '/delete/' + id, { action: 'delete', id: id });
    },
    getDeletedStudents: function() {
      return $http.get('/ci/students/deleted');
    },
    restoreStudent: function(id) {
      return $http.post('/ci/students/restore/' + id, { action: 'restore', id: id });
    },
    permanentDelete: function(id) {
      return $http.post('/ci/students/permanent_delete/' + id, { action: 'permanent_delete', id: id });
    }
  };
}]);

app.factory('AuthService', ['$http', '$cookies', '$location', function($http, $cookies, $location) {
  var baseUrl = '/ci/auth';

  return {
    login: function(credentials) {
      return $http.post(baseUrl + '/login', credentials);
    },
    signup: function(user) {
      return $http.post(baseUrl + '/signup', user);
    },
    logout: function() {
      return $http.get(baseUrl + '/logout').then(function() {
        // Remove cookies using AngularJS 1.3.0 syntax
        delete $cookies.user_id;
        delete $cookies.username;
        delete $cookies.email;
      });
    },
    isLoggedIn: function() {
      var userId = $cookies.user_id;
      console.log('Checking isLoggedIn, user_id:', userId);
      return !!userId;
    },
    getCurrentUser: function() {
      return $cookies.username || '';
    }
  };
}]);