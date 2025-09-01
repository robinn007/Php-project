var app = angular.module('myApp', ['ngRoute', 'ngCookies']);

app.run(['$http', '$rootScope', '$cookies', function($http, $rootScope, $cookies) {
  console.log('AngularJS app initialized - DEBUG MODE');
  
  // Simplified CSRF fetching - just for the token
  $rootScope.fetchCsrfToken = function() {
    return $http.get('/ci/auth/get_csrf').then(function(response) {
      console.log('CSRF response:', response.data);
      if (response.data.csrf_token_name && response.data.csrf_token) {
        $cookies.csrf_token_name = response.data.csrf_token_name;
        $cookies.csrf_token = response.data.csrf_token;
      } else {
        console.warn('No CSRF token received - continuing anyway');
      }
    }, function(error) {
      console.error('Failed to fetch CSRF token:', error);
      // Don't fail the app if CSRF fails
    });
  };
  
  // Try to fetch CSRF token but don't block the app
  $rootScope.fetchCsrfToken();
}]);

app.config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
  $locationProvider.html5Mode(false).hashPrefix('');

  $routeProvider
    .when('/', {
      templateUrl: 'views/home.html',
      controller: 'HomeController'
    })
    .when('/students', {
      templateUrl: 'views/students.html',
      controller: 'StudentController',
      resolve: {
        auth: ['AuthService', '$location', function(AuthService, $location) {
          console.log('Resolving auth for /students');
          if (!AuthService.isLoggedIn()) {
            console.log('Not logged in, redirecting to /login');
            $location.path('/login');
            return false;
          }
          return true;
        }]
      }
    })
    .when('/students/add', {
      templateUrl: 'views/student-form.html',
      controller: 'StudentFormController',
      resolve: {
        auth: ['AuthService', '$location', function(AuthService, $location) {
          if (!AuthService.isLoggedIn()) {
            $location.path('/login');
            return false;
          }
          return true;
        }]
      }
    })
    .when('/students/edit/:id', {
      templateUrl: 'views/student-form.html',
      controller: 'StudentFormController',
      resolve: {
        auth: ['AuthService', '$location', function(AuthService, $location) {
          if (!AuthService.isLoggedIn()) {
            $location.path('/login');
            return false;
          }
          return true;
        }]
      }
    })
    .when('/deleted-students', {
      templateUrl: 'views/deleted-students.html',
      controller: 'DeletedStudentsController',
      resolve: {
        auth: ['AuthService', '$location', function(AuthService, $location) {
          if (!AuthService.isLoggedIn()) {
            $location.path('/login');
            return false;
          }
          return true;
        }]
      }
    })
    .when('/login', {
      templateUrl: 'views/login.html',
      controller: 'AuthController'
    })
    .when('/signup', {
      templateUrl: 'views/signup.html',
      controller: 'AuthController'
    })
    .when('/logout', {
      controller: 'AuthController',
      template: '',
      resolve: {
        logout: ['AuthService', '$location', function(AuthService, $location) {
          AuthService.logout();
          $location.path('/login');
          return true;
        }]
      }
    })
    .when('/test-db', {
      templateUrl: 'views/test-db.html',
      controller: 'TestDbController'
    })
    .otherwise({
      redirectTo: '/'
    });

  // Simplified HTTP interceptor for debugging
  $httpProvider.interceptors.push(['$q', '$rootScope', '$cookies', function($q, $rootScope, $cookies) {
    return {
      request: function(config) {
        console.log('HTTP Request:', config.method, config.url);
        
        // Only add CSRF for POST requests and if we have the token
        if (config.method === 'POST' && $cookies.csrf_token_name && $cookies.csrf_token) {
          config.data = config.data || {};
          config.data[$cookies.csrf_token_name] = $cookies.csrf_token;
          console.log('Added CSRF token to request');
        }
        
        return config;
      },
      response: function(response) {
        console.log('HTTP Response:', response.status, response.config.url);
        
        // Update CSRF token if provided
        if (response.data && response.data.csrf_token) {
          $cookies.csrf_token = response.data.csrf_token;
          console.log('Updated CSRF token from response');
        }
        
        return response;
      },
      responseError: function(rejection) {
        console.error('HTTP Error:', rejection.status, rejection.config.url, rejection.data);
        
        // Update CSRF token if provided in error response
        if (rejection.data && rejection.data.csrf_token) {
          $cookies.csrf_token = rejection.data.csrf_token;
          console.log('Updated CSRF token from error response');
        }
        
        // Set flash message for errors (except auth redirects)
        if (rejection.status !== 401 && rejection.status !== 403) {
          $rootScope.flashMessage = rejection.data && rejection.data.message ? 
            rejection.data.message : 
            'Request failed: ' + (rejection.statusText || 'Server error');
          $rootScope.flashType = 'error';
        }
        
        return $q.reject(rejection);
      }
    };
  }]);
}]);

app.filter('capitalize', function() {
  return function(input) {
    if (!input) return '';
    return input.charAt(0).toUpperCase() + input.slice(1);
  };
});