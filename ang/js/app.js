/**
 * @description Main AngularJS application module for the Student Management System.
 * Configures routes, CSRF token handling, and authentication checks.
 */

var app = angular.module('myApp', ['ngRoute', 'ngCookies']);

/**
 * @description Initializes the application, sets up CSRF token fetching, and enforces authentication on route changes.
 * As the $http - HTTP service used for API calls
 * As the $rootScope - Angular root scope
 * As the $cookies - Angular cookies service
 * As the $location - Angular location service
 */

app.run(['$http', '$rootScope', '$cookies', '$location', function($http, $rootScope, $cookies, $location) {
  console.log('AngularJS app initialized - DEBUG MODE');

  // Fetches CSRF token from the server and stores it in cookies.
  //@returns {Promise} Resolves with CSRF token data or logs an error on failure

  $rootScope.fetchCsrfToken = function() {
    return $http.get('/ci/auth/get_csrf').then(function(response) {
      console.log('CSRF response:', response.data);
      if (response.data.csrf_token_name && response.data.csrf_token) {
         // Store CSRF token and name in cookies
        $cookies.csrf_token_name = response.data.csrf_token_name;
        $cookies.csrf_token = response.data.csrf_token;
      } else {
        console.warn('No CSRF token received - continuing anyway');
      }
    }, function(error) {
      console.error('Failed to fetch CSRF token:', error);
    });
  };
  
  // Fetch CSRF token on app start
  $rootScope.fetchCsrfToken();

  // Listen for route changes to manage authentication for protected routes
  // Redirects to /login if authentication is required and user is not logged in.

  $rootScope.$on('$routeChangeStart', function(event, next, current) {
    console.log('Route change to:', next.$$route ? next.$$route.originalPath : 'unknown');
    if (next.$$route && next.$$route.requireAuth && !$cookies.user_id) {
      console.log('Authentication required, redirecting to /login');
      event.preventDefault();
      $location.path('/login');
    }
  });
}]);

/**
 * @ngdoc config
 * @name configBlock
 * @description Configures routes and HTTP interceptors for the application.
 * @param {Object} $routeProvider - Angular route provider
 * @param {Object} $httpProvider - Angular HTTP provider
 */


app.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
    // Define application routes
  $routeProvider
    .when('/login', {
      templateUrl: 'views/login.html',
      controller: 'AuthController'
    })
    .when('/signup', {
      templateUrl: 'views/signup.html',
      controller: 'AuthController'
    })
    .when('/dashboard', {
      templateUrl: 'views/dashboard.html',
      controller: 'DashboardController',
      requireAuth: true   // Requires user to be logged in
    })
    .when('/students', {
      templateUrl: 'views/students.html',
      controller: 'StudentController',
      requireAuth: true  // Requires user to be logged in
    })
    .when('/students/add', {
      templateUrl: 'views/student-form.html',
      controller: 'StudentFormController',
      requireAuth: true  // Requires user to be logged in
    })
    .when('/students/edit/:id', {
      templateUrl: 'views/student-form.html',
      controller: 'StudentFormController',
      requireAuth: true  // Requires user to be logged in
    })
    .when('/students/deleted', {
      templateUrl: 'views/deleted-students.html',
      controller: 'DeletedStudentsController',
      requireAuth: true  // Requires user to be logged in
    })
    .when('/test-db', {
      templateUrl: 'views/test-db.html',
      controller: 'TestDbController',
      requireAuth: true  // Requires user to be logged in
    })
    .otherwise({
      redirectTo: function() {
        return $cookies.user_id ? '/dashboard' : '/login';
      }
    });

    /**
  * @description HTTP interceptor to attach CSRF token to requests and update it from responses.
   * @param {Object} $cookies - Angular cookies service
   * @returns {Object} Interceptor object with request and response handlers
     */

   // HTTP Interceptor for CSRF token
  $httpProvider.interceptors.push(function($cookies) {
    return {
      request: function(config) {
          // Attach CSRF token to request headers
        var token = $cookies.csrf_token || '';
        if (token) {
          config.headers['X-CSRF-Token'] = token;
        }
        return config;
      },
      response: function(response) {
           // Update CSRF token from response if provided
        if (response.data.csrf_token) {
          $cookies.csrf_token = response.data.csrf_token;
        }
        return response;
      }
    };
  });
}]);